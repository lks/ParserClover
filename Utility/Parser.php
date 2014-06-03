<?php
include 'Entity/FileMetric.php';
include 'Entity/GroupMetric.php';
include 'Utility/CouchDbWrapper.php';



function parseFile($child, $results, $categories)
{
	$couchdb = new CouchDbWrapper();

	if(count($child->children()) > 0) {
		foreach($child->children() as $newChild)
		{
			if('package' == $newChild->getName()) {
				$results = test($newChild, $categories);
			} else if ('file' == $newChild->getName()) {
				if($newChild->class['name'] != "") {
					$fileMetric = new FileMetric($newChild->class, $newChild->metrics);
						
					$isFound = false;
					foreach ($categories as $category) {
						if(ereg($category."$", $newChild->class['name'])) {
							$fileMetric->type = $category;
							$isFound = true;
							break;
						}
					}
					if(!$isFound) {
						$fileMetric->type = "Other";
					}
					$fileMetric = $this->setBundle($fileMetric);
					$couchdb->createDocument();
				}
			}
		}
		return true;
	} else {
		return true;
	}
}

function getBundle($object)
{
	$namespace = $object->namespace;
	if(preg_match("#[\\]{1}[A-Za-z]{1,100}Bundle#", $namespace, $bundle, PREG_OFFSET_CAPTURE))
	{
		$object->bundle = $bundle[0][0];
	}
	return $object;
}

function categorizedResult($results, $categories)
{
	$tmp = array();
	foreach ($results as $key => $value) {
		$insertGroup = new GroupMetric($key);
		$nbFile = 0;
		$methodRate = 0;
		$statementRate = 0;
		$listFiles = array();
		foreach ($value as $item)
		{
			$nbFile++;
			$methodRate += $item->methodRate;
			$statementRate += $item->statementRate;
			$listFiles[] = $item->name;
		}
		$insertGroup->nbFile = $nbFile;
		$insertGroup->methodRate = $methodRate / $nbFile;
		$insertGroup->statementRate = $statementRate / $nbFile;
		$insertGroup->listFiles = $listFiles;
		array_push($tmp, $insertGroup);
	}
	return $tmp;
}
