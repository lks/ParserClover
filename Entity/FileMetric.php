<?php


class FileMetric
{
	public $name;
	public $namespace;
	public $methodRate;
	public $statementRate;

	public function __construct($name,
		$namespace,
		$methods,
		$coveredMethods,
		$statements,
		$coveredStatements)
	{
		$this->name = "".$name;
		$this->namespace = "".$namespace;
		$this->methodRate = 0;
		if($methods > 0)
		{
			$this->methodRate = $coveredMethods / $methods;
		}
		$this->statementRate = 0;
		if($statements > 0)
		{
			$this->methodRate = $coveredStatements / $statements;
		}
	}

}
