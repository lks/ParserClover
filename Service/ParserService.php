<?php
namespace Service;

use Entity\FileMetric;
use Entity\PmdMetric;
use Exception\NoPmdDataException;


class ParserService implements IParserService
{
	protected $monolog;
	protected $categories;

	public function __construct($categories, $monolog) {
		$this->categories = $categories;
		$this->monolog = $monolog;
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
    if(count($child->children()) > 0) {
      foreach($child->children() as $newChild)
      {
        if('package' == $newChild->getName()) {
          $results = $this->createMetric($newChild, $categories);
        } else if ('file' == $newChild->getName()) {

          if($newChild->class['name'] != "") {
            $this->monolog->addDebug(
                sprintf("Create file metric '%s' ", $newChild->class['name']));
            $class = $newChild->class;
            $metrics = $newChild->metrics;
            $fileMetric = new FileMetric($class, $metrics);
            $isFound = false;

            foreach ($categories as $category) {
              if(preg_match("#".$category."$#", $newChild->class['name'])) {
                $fileMetric->type = $category;
                $isFound = true;
                break;
              }
            }
            if(!$isFound) {
              $fileMetric->type = "Other";
            }

            $fileMetric = $this->setBundle($fileMetric);

            $theDocument = $this->couchDbClient->findDocument($fileMetric->name);
            if ($theDocument != null && $theDocument->status != 404) {
              $this->couchDbClient->putDocument((array) $fileMetric, $fileMetric->name, $theDocument->body['_rev']);
            } else {
              $this->couchDbClient->postDocument((array) $fileMetric);
            }
          }
        }
      }
    }
  }

  /**
   * Parse the report of Clover and insert it in the database
   *
   * @return List of vialation inserted in the database
   */
	public function parseCloverReport()
	{
		return null;
	}


  /**
   * Parse the report of Pmd and insert it in the database
   *
   * @return List of vialation inserted in the database
   */
	public function parsePmdReport($category)
	{
    $result = array();
		$nodes = $this->fileXmlToArray('../build/phppmd/pmd.'.$category.'.xml');

    // place the pointer on the right node
    if(nodes == null) {
      //TODO Implement this exception
      throw new NoPmdDataException();
    }

    $fileNodes = $nodes->pmd;
		
		if($fileNodes != null) {
      //for each file, I will populate an PmdMetric object used to ease the insert on DB
			foreach($fileNodes as $file) {
        $type = $category;
        $bundle = $this->getBundle($file['name']);
        foreach ($file->children() as $violation) {
          //Populate an PmdOBject
          if(isset($name)) {
            $name = $violation['class'];
          }
          $priority = $violation['priority'];
        }
        array_push($result, ,new PmdMetric($name, , $type, $bundle));
			}
		}

		return null;
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
		if(preg_match("#[\\]{1}[A-Za-z]{1,100}Bundle#",
					$namespace,
					$bundle,
					PREG_OFFSET_CAPTURE))
		{
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
  private function getBundle($filename) {
    if(preg_match("#[/]{1}[A-Za-z]{1,100}Bundle#",
          $filename,
          $bundle,
          PREG_OFFSET_CAPTURE))
    {
      return $bundle[0][0];
    }
    return null;
  }
}
