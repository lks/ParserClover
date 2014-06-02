<?php

use Entity\FileMetric;
use Utility\CouchDbWrapper;

namespace Service;

class ParserService
{
	protected $couchDbWrapper;

	/**
	 * 
	 */
	public function __construct($couchDbWrapper) {
		$this->couchDbWrapper = $couchDbWrapper;
	}

	/**
	 * Create a metric for each child. It's recursive method to explore all xml node
	 * 
	 * @param child 		Xml Node: Xml node to explore
	 * @param categories 	Categories to search in the namespace of the classes
	 * 
	 * @return true 		if all are ok.
	 */
	public function createMetric($child, $categories)
	{
		$couchdb = new CouchDbWrapper();

		if(count($child->children()) > 0) {
			foreach($child->children() as $newChild)
			{
				if('package' == $newChild->getName()) {
					$results = $this->createMetric($newChild, $categories);
				} else if ('file' == $newChild->getName()) {
					if($newChild->class['name'] != "") {
						$fileMetric = new FileMetric($newChild->class, $newChild->metrics);
						$isFound = false;

						foreach ($categories as $category) {
							if(ereg($category."$", $newChild->class['name'])) {
								$fileMetric->type = $category;
								$isFound = true;
								exit;
							}
						}
						if(!$isFound) {
							$fileMetric->type = "Other";
						}

						$fileMetric = $this->setBundle($fileMetric);
						$couchdb->createDocument($fileMetric);
					}
				}
			}
			return true;
		} else {
			return true;
		}
	}

	private function getBundle($object)
	{
		$namespace = $object->namespace;
		if(preg_match("#[\\]{1}[A-Za-z]{1,100}Bundle#", $namespace, $bundle, PREG_OFFSET_CAPTURE))
		{
			$object->bundle = $bundle[0][0];
		}
		return $object;
	}
}


