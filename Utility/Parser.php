<?php
include 'Entity/FileMetric.php';
include 'Entity/GroupMetric.php';

function test($child, $results)
{
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
						$newChild->metrics['coveredstatements']);

					

					if(ereg("Controller$", $newChild->class['name'])) {
						array_push($results['Controller'], $fileMetric);
					} else if (ereg("Service$", $newChild->class['name'])) {
						array_push($results['Service'], $fileMetric);
					} else if(ereg("DAO$", $newChild->class['name'])) {
						array_push($results['Dao'], $fileMetric);
					} else if(ereg("Exception$", $newChild->class['name'])) {
						array_push($results['Exception'], $fileMetric);
					} else {
						array_push($results['Other'], $fileMetric);
					}
				}
			}
		}
		return $results;
	} else {
		return $results;
	}
}

function categorizedResult($results, $categories)
{
	$tmp = array();
	foreach ($results as $key => $value) {
		$insertGroup = new GroupMetric($key);
		$nbFile = 0;
		$methodRate = 0;
		$statementRate = 0;
		foreach ($value as $item)
		{
			$nbFile++;
			$methodRate += $item->methodRate;
			$statementRate += $item->statementRate;
		}
		$insertGroup->nbFile = $nbFile;
		$insertGroup->methodRate = $methodRate / $nbFile;
		$insertGroup->statementRate = $statementRate / $nbFile;
		array_push($tmp, $insertGroup);
	}
	return $tmp;
}
