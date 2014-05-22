<?php

include "entity/FileMetric.php";


if (file_exists('clover.xml')) {
    $xml = simplexml_load_file('clover.xml');
} else {

	exit('File doesn\'t exist! ');
}

echo "test";

//list all file without package
$results = array();
//test($xml->project, $results);

print_r(var_dump($results));