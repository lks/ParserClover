<?php

namespace Entity;

class PmdMetric
{
  public $name;
  public $priority;
  public $complexity;
  public $type;
  public $bundle;

  public function __construct($name, $priority, $complexity, $type, $bundle)
  {
    $this->name 		    = $name;
    $this->priority 		= $priority;
    $this->complexity   = $complexity;
    $this->type         = $type;
    $this->bundle       = $bundle;
  }
}
