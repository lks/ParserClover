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
class Violation {
    protected $priority;
    protected $value;
    protected $rule;
    protected $beginLine;
    protected $endLine;

    function __construct($beginLine, $endLine, $priority, $rule, $value)
    {
        $this->beginLine = $beginLine;
        $this->endLine = $endLine;
        $this->priority = $priority;
        $this->rule = $rule;
        $this->value = $value;
    }

    /**
     * @param mixed $beginLine
     */
    public function setBeginLine($beginLine)
    {
        $this->beginLine = $beginLine;
    }

    /**
     * @return mixed
     */
    public function getBeginLine()
    {
        return $this->beginLine;
    }

    /**
     * @param mixed $endLine
     */
    public function setEndLine($endLine)
    {
        $this->endLine = $endLine;
    }

    /**
     * @return mixed
     */
    public function getEndLine()
    {
        return $this->endLine;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $rule
     */
    public function setRule($rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return mixed
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

} 