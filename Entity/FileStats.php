<?php

namespace Entity;


class FileStats
{
    /* Name of the file */

    public $name;

    /* Name of the namespace of the file */

    public $namespace;

    /* Array contained the number of the violation by type */

    public $stats;

    /* Type of class */

    public $type;

    /* bundle of class */

    public $bundle;

    public function __construct($name, $namespace, $stats, $type, $bundle)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->stats = $stats;
        $this->type = $type;
        $this->bundle = $bundle;
    }

}