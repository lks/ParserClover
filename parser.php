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
function cmp($a, $b)
{
    return $a->methodRate > $b->methodRate;
}
usort($results, "cmp");

echo "<table>";

foreach ($results as $result)
{
	echo "<tr><td>";
	echo $result->name . "<br />";
	echo "</td><td>";
	echo $result->namespace . "<br />";
	echo "</td><td>";
	echo $result->methodRate . "<br />";
	echo "</td></tr>";
}
echo "</table>";
