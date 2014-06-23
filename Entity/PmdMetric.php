<?php

namespace Entity;

class PmdMetric
{
  public $name;
  public $listViolations;
  public $type;
  public $bundle;

  public function __construct($name, $listViolations, $type, $bundle)
  {
    $this->name 		    = $name;
    $this->listViolations = $listViolations;
    $this->type         = $type;
    $this->bundle       = $bundle;
  }
}
