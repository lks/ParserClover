<?php
namespace Service;

use Dao\Dao;
use Doctrine\CouchDB\HTTP\HTTPException;
use Entity\FileMetric;
use Entity\PmdMetric;
use Entity\FileStats;
use Exception\NoPmdDataException;
use Utility\PhpUnitItem;


class ParserService
{
    protected $monolog;
    protected $finder;
    protected $categories;
    protected $dao;

    public function __construct($monolog, $finder, $categories, Dao $dao)
    {
        $this->monolog = $monolog;
        $this->finder = $finder;
        $this->categories = $categories;
        $this->dao = $dao;
    }

    /**
     * Get the report for the metrics given in the params array and count the high priority number by
     * desired metric.
     * The aim is to extract the high priority violation to have an overview of the refactoring task.
     *
     * @internal param array $params Contained the metric to analyse, null if we want to analyse all metrics
     * @return array of FileStats object
     */
    public function mergeReport()
    {
        $data = array();
        $nbViolationsPmd = 0;
        $nbViolationsPhpUnit = 0;
        $pmdResult = $this->parsePmdReport();
        $phpunitResult = $this->parsePhpUnitReport();

        foreach ($pmdResult as $key => $value) {
            $nbViolationsPmd += $value->getViolations()['pmd'];
            if (isset($phpunitResult[$key])) {
                //let's go to merge result
                $value->setViolations(array_merge($value->getViolations(), $phpunitResult[$key]->getViolations()));
                $nbViolationsPhpUnit++;
            }
            array_push($data, $value);
            //save in database
            $this->dao->save($value->getName(), $value);


            //Delete the row added in the origin array
            unset($phpunitResult[$key]);
            array_values($phpunitResult);
        }

        // add files for the only code coverage violation
        foreach ($phpunitResult as $value) {
            array_push($data, $value);
            $nbViolationsPhpUnit++;

            //save in database
            $this->dao->save($value->getName(), $value);
        }

        $result = array();
        $result ['total'] = count($data);
        $result ['nbViolationsPmd'] = $nbViolationsPmd;
        $result ['nbViolationsPhpUnit'] = $nbViolationsPhpUnit;
        usort($data, array($this, 'sortMergeReport'));
        $result ['data'] = $data;

        return $result;
    }

    /**
     * Parse the report of Clover and insert it in the database
     *
     * @throws \Exception\NoPmdDataException
     * @return List of vialation inserted in the database
     */
    public function parsePhpUnitReport()
    {
        $results = array();

        //get all *.php.xml file and parse each file to get the file stats
        $iterator = $this->finder->files()
            ->name('*.php.xml')
            ->in(__DIR__ . '/../build/phpunit-coverage');

        foreach ($iterator as $file) {
            $nodes = $this->fileXmlToArray($file->getRealpath());
            // place the pointer on the right node
            if ($nodes == null) {
                //TODO Implement this exception
                throw new NoPmdDataException();
            }
            //all files
            $filenodes = $nodes->children();
            if (isset($filenodes)) {
                foreach ($filenodes as $file) {
                    $item = new PhpUnitItem($filenodes);
                    $results[$name] = new FileStats(
                        $item->getClassName(),
                        $item->getNamespace(),
                        $item->getStats(),
                        $item->getTypeName(),
                        $item->getBundleName()
                    );
                }
            }
        }
        return $results;
    }


    /**
     * Parse the report of Pmd and insert it in the database
     *
     * @throws \Exception\NoPmdDataException
     * @return List of vialation inserted in the database
     */
    public function parsePmdReport()
    {
        $result = array();
        $nodes = $this->fileXmlToArray('../build/phpmd/pmd.xml');

        // place the pointer on the right node
        if ($nodes == null) {
            //TODO Implement this exception
            throw new NoPmdDataException();
        }

        $fileNodes = $nodes->children();

        if ($fileNodes != null) {
            //for each file, I will populate an PmdMetric object used to ease the insert on DB
            foreach ($fileNodes as $file) {
                //TODO get category value
                $type = "";
                //TOD get bundle value
                //$bundle = $this->getBundle($file['name']);
                $priority = 0;
                $namespace = $bundle = null;
                $name = '';
                foreach ($file->children() as $violation) {
                    foreach ($violation->attributes() as $a => $b) {
                        if ($a == "class" && $name == '') {
                            $name = '' . $b;
                            $type = $this->getType($name);
                        } elseif ($a == "package" && !isset($namespace)) {
                            $namespace = $b;
                            $bundle = $this->getBundle($b);
                        } elseif ($a == "priority" && $b >= 1) {
                            $priority++;
                        }
                    }
                }
                $violation = array();
                $violation['pmd'] = $priority;
                $result[$name] = new FileStats($name, $namespace, $violation, $type, $bundle);
            }
        }

        return $result;
    }

    /**
     * Init the array from the file content (XML format)
     *
     * @param $filepath
     * @return List of vialation by typology.
     */
    private function fileXmlToArray($filepath)
    {
        $xml = null;

        $this->monolog->addDebug("Begin the loading...");
        if (file_exists($filepath)) {
            $xml = simplexml_load_file($filepath);
        } else {
            return "error";

        }
        return $xml;
    }

    private function setBundle($object)
    {
        $namespace = $object->namespace;
        if (preg_match("#[\\]{1}[A-Za-z]{1,100}Bundle#",
            $namespace,
            $bundle,
            PREG_OFFSET_CAPTURE)
        ) {
            $object->bundle = $bundle[0][0];
        }
        return $object;
    }

    /**
     * Get the Bundle name from the filename of a class
     *
     * @param  $filename String name and path of the file we want to extract the bundle
     *
     * @return String Bundle associated to the filename
     */
    private function getBundle($filename)
    {
        if (preg_match("#[\\]{1}[A-Za-z]{1,100}Bundle#",
            $filename,
            $bundle,
            PREG_OFFSET_CAPTURE)
        ) {
            return $bundle[0][0];
        }
        return null;
    }

    /**
     * Sort function of the the result table.
     * The constraints are the following:
     *     - Have the pmd value and the phpunit value present,
     *     - Have a pmd value superior, if equals, the phpunit have to be inferior,
     *     - If juste one value is completed, Pmd take the advantage in DESC sort.
     *
     * @param  FileStats $a
     * @param  FileStats $b
     * @return int 0, if equals. 1, if $a is superior, -1 else.
     */
    public function sortMergeReport($a, $b)
    {
        $aPmd = 0;
        $bPmd = 0;

        return ($aPmd < $bPmd) ? 1 : -1;
    }

    /**
     * Get the Type name from the class name
     *
     * @param  $className String name of the class
     *
     * @internal param $categories
     * @return String Type associated to the class name
     */
    private function getType($className)
    {
        $type = '';
        $isFound = false;
        foreach ($this->categories as $key => $value) {
            if (preg_match("#" . $key . "$#", $className)) {
                $type = $key;
                $isFound = true;
                break;
            }
        }
        if (!$isFound) {
            $type = "Other";
        }
        return $type;
    }

}
