<?php
include 'Entity/FileMetric.php';
include 'Entity/GroupMetric.php';
include 'Utility/CouchDbWrapper.php';



function test($child, $results)
{
	$couchdb = new CouchDbWrapper();

	if(count($child->children()) > 0) {
		foreach($child->children() as $newChild)
		{
			if('package' == $newChild->getName()) {
				$results = test($newChild, $results);
			} else if ('file' == $newChild->getName()) {
				if($newChild->class['name'] != "") {
					$fileMetric = new FileMetric(
						$newChild->class['name'],
						$newChild->class['namespace'],
						$newChild->metrics['methods'],
						$newChild->metrics['coveredmethods'],
						$newChild->metrics['statements'],
						$newChild->metrics['coveredstatements']
					);

					if(ereg("Controller$", $newChild->class['name'])) {
						$fileMetric->type = "Controller";

						array_push($results['Controller'], $fileMetric);
					} else if (ereg("Service$", $newChild->class['name'])) {
						$fileMetric->type = "Service";
						
						array_push($results['Service'], $fileMetric);
					} else if(ereg("DAO$", $newChild->class['name'])) {
						$fileMetric->type = "Dao";

						array_push($results['Dao'], $fileMetric);
					} else if(ereg("Exception$", $newChild->class['name'])) {
						$fileMetric->type = "Exception";

						array_push($results['Exception'], $fileMetric);
					} else if(ereg("Entity$", $newChild->class['name'])) {
						$fileMetric->type = "Entity";

						array_push($results['Entity'], $fileMetric);
					} else {
						$fileMetric->type = "Other";

						array_push($results['Other'], $fileMetric);
					}

					$couchdb->createDocument(getBundle($fileMetric));
				}
			}
		}
		return $results;
	} else {
		return $results;
	}
}

function getBundle($object)
{
	$namespace = $object->namespace;
	if(preg_match("/[a-z]{1,100}Bundle/", $namespace, $bundle, PREG_OFFSET_CAPTURE))
	{
		print_r($bundle[0]);
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
