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
					array_push($results, new FileMetric(
						$newChild->class['name'],
						$newChild->class['namespace'],
						$newChild->metrics['methods'],
						$newChild->metrics['coveredmethods'],
						$newChild->metrics['statements'],
						$newChild->metrics['coveredstatements']));
				}
			}
			return $results;
		} else {
			return $results;
		}
}
