<?php

namespace Entity;

class GroupMetric
{
	public $name;
	public $methodRate;
	public $statementRate;
	public $nbFile;
	public $listFiles;

	public function __construct($name)
	{
		$this->name = $name;
	}
	
}