<?php
namespace Service;

use Dao\Dao;
use Entity\PhpUnitItem;
use Entity\PmdItem;
use Entity\FileStats;
use Exception\NoPmdDataException;


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
        $pmdResult = $this->parsePmdReport();
        $phpunitResult = $this->parsePhpUnitReport();

        foreach ($pmdResult as $key => $value) {
            if (isset($phpunitResult[$key])) {
                //let's go to merge result
                $value->stats = array_merge($value->stats, $phpunitResult[$key]->stats);
            }
            array_push($data, $value);
            //save in database
            $this->dao->save($value->name, $value);


            //Delete the row added in the origin array
            unset($phpunitResult[$key]);
            array_values($phpunitResult);
        }

        // add files for the only code coverage violation
        foreach ($phpunitResult as $value) {
            array_push($data, $value);

            //save in database
            $this->dao->save($value->name, $value);
        }
        return count($data);
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
                $item = new PmdItem($file, $this->categories);
                $this->monolog->addDebug("Save object in Merge : " . $item->getClassName());
                $className = $item->getClassName();
                $result['' . $className] = new FileStats(
                    $item->getClassName(),
                    $item->getNamespace(),
                    $item->getStats(),
                    $item->getTypeName(),
                    $item->getBundleName()
                );
            }
        }

        return $result;
    }

    /**
     * Init the array from the file content (XML format)
     *
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
            $fileNodes = $nodes->children();
            if (isset($fileNodes)) {
                foreach ($fileNodes as $file) {
                    $item = new PhpUnitItem($file, $this->categories);
                    $this->monolog->addDebug("Save object in Merge : " . $item->getClassName());
                    $className = $item->getClassName();
                    $results['' . $className] = new FileStats(
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
     * @param  $filename Name and path of the file we want to extract the bundle
     *
     * @return String Bundle associated to the filename
     */
    private function getBundle($filename)
    {
        $aPmd = 0;
        $bPmd = 0;

        return ($aPmd < $bPmd) ? 1 : -1;
    }

    /**
     * Get the Type name from the class name
     *
     * @param  $className Name of the class
     *
     * @return String Type associated to the class name
     */
    private function getType($className)
    {
        $type = '';
        $isFound = false;
        $categories = $this->categories;
        foreach ($categories as $key => $value) {
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
