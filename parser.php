<?php

include "Utility/Parser.php";


if (file_exists('clover.xml')) {
    $xml = simplexml_load_file('clover.xml');
} else {

	exit('File doesn\'t exist! ');
}

//list all file without package
$results = array();
$results = test($xml->project, $results);

if (file_exists('results.json')) {
  unlink('results.json');
}
$fp = fopen('results.json', 'w');
fwrite($fp, json_encode($results));
fclose($fp);
