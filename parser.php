<?php

include "Utility/Parser.php";


if (file_exists('clover.xml')) {
    $xml = simplexml_load_file('clover.xml');
} else {

	exit('File doesn\'t exist! ');
}

echo "test";

//list all file without package
$results = array();
$results = test($xml->project, $results);

print_r($results);
