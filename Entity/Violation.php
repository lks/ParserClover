<?php
/**
 * Created by PhpStorm.
 * User: j.calabrese
 * Date: 01/07/14
 * Time: 11:59
 */

namespace Entity;


/**
 * Class Violation
 * @package Entity
 */
class Violation
{
    public $priority;
    public $value;
    public $rule;
    public $beginLine;
    public $endLine;

    function __construct($beginLine, $endLine, $priority, $rule, $value)
    {
        $this->beginLine = $beginLine;
        $this->endLine = $endLine;
        $this->priority = $priority;
        $this->rule = $rule;
        $this->value = $value;
    }
} 