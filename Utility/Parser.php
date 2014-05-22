<?php

function test($child, $results)
{
	echo "Launch parser with ".$child->getName(). "\n";
	if('file' == $child->getName()) {
		$results[] = new FileMetric(
			$child->metrics['name'],
			$child->metrics['namespace'],
			$child->metrics['methods'],
			$child->metrics['coveredmethods'],
			$child->metrics['statements'],
			$child->metrics['coveredstatements']);
		return $results;
	} else {
		if($child->count > 0) {
			foreach(foreach($child->children as $newChild))
			{
				if('package' = $newChild->getName()) {
					$result[] = $this->parser($newChild, $results);
				}
			}
		} else {
			return $results;
		}
	}
}