<?php


class FileMetric
{
	public $name;
	public $namespace;
	public $methodRate;
	public $statementRate;
	public $type;
	public $bundle;
	public $_rev;

	public function __construct($class, $metric)
	{
		$this->name 		= "".$newChild->class['name'];
		$this->namespace 	= "".$newChild->class['namespace'];
		$this->methodRate = 0;
		if($methods > 0)
		{
			$this->methodRate = $newChild->metrics['coveredmethods'] / $newChild->metrics['methods'];
		}
		$this->statementRate = 0;
		if($statements > 0)
		{
			$this->methodRate = $newChild->metrics['coveredstatements'] / $newChild->metrics['statements'];
		}
	}

}
