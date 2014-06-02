<?php

include "Utility/Parser.php";


if (file_exists('clover.xml')) {
    $xml = simplexml_load_file('clover.xml');
} else {
	exit('File doesn\'t exist! ');
}

//list all file without package
$categories = ['Controller', 'Dao', 'Entity', 'Service', 'Exception', 'Other'];
$results = array();
foreach ($categories as $category)
{
	$results[$category] = array();
}

$results = test($xml->project, $results);

// Manage the group by category
$categorizedResult = categorizedResult($results, $categories);

if (file_exists('results.json')) {
  unlink('results.json');
}
$fp = fopen('results.json', 'w');
fwrite($fp, json_encode($categorizedResult));
fclose($fp);
