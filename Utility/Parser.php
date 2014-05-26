<?php
include 'Entity/FileMetric.php';

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

					array_push($results['All'], $fileMetric);

					if(ereg("Controller$", $newChild->class['name'])) {
						array_push($results['Controller'], $fileMetric);
					} else if (ereg("Service$", $newChild->class['name'])) {
						array_push($results['Service'], $fileMetric);
					} else if(ereg("DAO$", $newChild->class['name'])) {
						array_push($results['Dao'], $fileMetric);
					} else if(ereg("Exception$", $newChild->class['name'])) {
						array_push($results['Exception'], $fileMetric);
					}

				}
			}
		}
		return $results;
	} else {
		return $results;
	}
}
