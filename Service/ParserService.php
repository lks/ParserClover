<?php
namespace Service;

use Entity\FileMetric;
use Entity\PmdMetric;
use Entity\FileStats;
use Exception\NoPmdDataException;


class ParserService
{
    protected $monolog;
    protected $finder;
    protected $categories;

    public function __construct($monolog, $finder, $categories)
    {
        $this->monolog = $monolog;
        $this->finder = $finder;
        $this->categories = $categories;
    }

    /**
     * Create a metric for each child. It's recursive method to explore all xml node
     *
     * @param child     Xml Node: Xml node to explore
     * @param categories  Categories to search in the namespace of the classes
     *
     * @return true     if all are ok.
     */
    public function createMetric($child, $categories)
    {
        $this->monolog->addDebug("Begin the treatement...");
        if (count($child->children()) > 0) {
            foreach ($child->children() as $newChild) {
                if ('package' == $newChild->getName()) {
                    $results = $this->createMetric($newChild, $categories);
                } else if ('file' == $newChild->getName()) {

                    if ($newChild->class['name'] != "") {
                        $this->monolog->addDebug(
                            sprintf("Create file metric '%s' ", $newChild->class['name']));
                        $class = $newChild->class;
                        $metrics = $newChild->metrics;
                        $fileMetric = new FileMetric($class, $metrics);
                        $isFound = false;

                        foreach ($categories as $category) {
                            if (preg_match("#" . $category . "$#", $newChild->class['name'])) {
                                $fileMetric->type = $category;
                                $isFound = true;
                                break;
                            }
                        }
                        if (!$isFound) {
                            $fileMetric->type = "Other";
                        }

                        $fileMetric = $this->setBundle($fileMetric);

                        $theDocument = $this->couchDbClient->findDocument($fileMetric->name);
                        if ($theDocument != null && $theDocument->status != 404) {
                            $this->couchDbClient->putDocument((array)$fileMetric, $fileMetric->name, $theDocument->body['_rev']);
                        } else {
                            $this->couchDbClient->postDocument((array)$fileMetric);
                        }
                    }
                }
            }
        }
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

            //Delete the row added in the origin array
            unset($phpunitResult[$key]);
            array_values($phpunitResult);
        }

        foreach ($phpunitResult as $value) {
            array_push($data, $value);
            $nbViolationsPhpUnit++;
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
                    //TODO get category value
                    $type = "";
                    $priority = 0;
                    $namespace = '';
                    $name = '';
                    $isToBeFixed = false;
                    $violation = array();
                    $nbExecutable = 0;
                    $nbExecuted = 0;
                    //determine if we have to log this file
                    foreach ($file->children() as $node) {

                        if ('class' == $node->getName()) {
                            foreach ($node->attributes() as $key => $value) {
                                if ($key == 'name') {
                                    $name = '' . $value;
                                    $type = $this->getType($name);
                                }
                            }
                        } else if ('totals' == $node->getName()) {

                            foreach ($node->children() as $result) {
                                if ('lines' == $result->getName()) {
                                    foreach ($result->attributes() as $key => $value) {
                                        if ($key == 'executable') {
                                            $nbExecutable = $value;
                                        } elseif ($key == 'executed') {
                                            $nbExecuted = $value;
                                        }
                                    }
                                }
                            }

                        }

                    } //end of the node of a file
                    if (isset($nbExecutable) && $nbExecutable > 0) {
                        $average = $nbExecuted / $nbExecutable;
                        //TODO filter by type
                        if ($average < $this->categories[$type]) {
                            $violation['phpunit'] = $average;
                            $isToBeFixed = true;

                        }
                    } else {
                        $violation['phpunit'] = 0;
                        $isToBeFixed = true;
                    }
                    if ($isToBeFixed) {
                        $results[$name] = new FileStats($name, $namespace, $violation, $type, "");
                    }
                } //end of the file parcours
            }
        }
        return $results;
    }


    /**
     * Parse the report of Pmd and insert it in the database
     *
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
                $namespace = '';
                $name = '';
                foreach ($file->children() as $violation) {
                    foreach ($violation->attributes() as $a => $b) {
                        //echo $a,'="',$b,"\"<br />";

                        if ($a == "class" && $name == '') {
                            $name = '' . $b;
                            $type = $this->getType($name);
                        } elseif ($a == "package" && !isset($namespace)) {
                            $namespace = $b;
                        } elseif ($a == "priority" && $b >= 1) {

                            $priority++;
                        }
                    }
                }
                $violation = array();
                $violation['pmd'] = $priority;
                $result[$name] = new FileStats($name, $namespace, $violation, $type, "");
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
        if (preg_match("#[/]{1}[A-Za-z]{1,100}Bundle#",
            $filename,
            $bundle,
            PREG_OFFSET_CAPTURE)
        ) {
            return $bundle[0][0];
        }
        return null;
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

    /**
     * Sort function of the the result table.
     * The constraints are the following:
     *     - Have the pmd value and the phpunit value present,
     *     - Have a pmd value superior, if equals, the phpunit have to be inferior,
     *     - If juste one value is completed, Pmd take the advantage in DESC sort.
     *
     * @param  FileStats $a
     * @param  FileStats $b
     * @return 0, if equals. 1, if $a is superior, -1 else.
     */
    public function sortMergeReport($a, $b)
    {
        $aPmd = 0;
        $aPhpUnit = 1;
        $bPmd = 0;
        $bPhpUnit = 1;
        $aViolations = $a->getViolations();
        $bViolations = $b->getViolations();

        if (isset($aViolations['pmd'])) {
            $aPmd = $aViolations['pmd'];
        }
        if (isset($aViolations['phpunit'])) {
            $aPhpUnit = $aViolations['phpunit'];
        }
        if (isset($bViolations['pmd'])) {
            $bPmd = $bViolations['pmd'];
        }
        if (isset($bViolations['phpunit'])) {
            $bPhpUnit = $bViolations['phpunit'];
        }
        if ($aPmd == $bPmd) {
            return 0;
        }
        return ($aPmd < $bPmd) ? 1 : -1;
    }

}
