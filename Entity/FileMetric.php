<?php

namespace Entity;

//TODO depreciate it
class FileMetric
{
  public $name;
  public $namespace;
  public $methodRate;
  public $statementRate;
  public $type;
  public $bundle;

  public function __construct($class, $metric)
  {
    $this->name     = "".$class['name'];
    $this->namespace  = "".$class['namespace'];
    $this->methodRate = 0;
    if($metric['methods'] > 0)
    {
      $this->methodRate = $metric['coveredmethods'] / $metric['methods'];
    }
    $this->statementRate = 0;
    if($metric['statements'] > 0)
    {
      $this->methodRate = $metric['coveredstatements'] / $metric['statements'];
    }
  }
}
