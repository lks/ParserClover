<?php
namespace Service;

use Dao\Dao;
use Entity\PhpUnitItem;
use Entity\PmdItem;
use Entity\FileStats;
use Exception\NoPmdDataException;


class ParserService implements IParserService
{
    protected $logger;
    protected $finder;
    protected $categories;
    protected $dao;

    public function __construct($logger, $finder, $categories, Dao $dao)
    {
        $this->logger = $logger;
        $this->finder = $finder;
        $this->categories = $categories;
        $this->dao = $dao;
    }

    /**
     * Get the report for the metrics given in the params array and count the high priority number by
     * desired metric.
     * The aim is to extract the high priority violation to have an overview of the refactoring task.
     *
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
                //we exclude the Test in the result
                if ('Test' != $item->getTypeName()) {
                    $this->logger->addDebug("Save object in Merge : " . $item->getClassName());
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
        }

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
            $fileNodes = $nodes->children();
            if (isset($fileNodes)) {
                foreach ($fileNodes as $file) {
                    $item = new PhpUnitItem($file, $this->categories);
                    if ('Test' != $item->getTypeName()) {
                        $this->logger->addDebug("Save object in Merge : " . $item->getClassName());
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
        }
        return $results;
    }

    /**
     * Init the array from the file content (XML format)
     *
     * @param $filepath
     * @return Array of the xml structure file
     */
    private function fileXmlToArray($filepath)
    {
        $xml = null;
        if (file_exists($filepath)) {
            $xml = simplexml_load_file($filepath);
        } else {
            return "error";

        }
        return $xml;
    }
}
