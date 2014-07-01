<?php
/**
 * Created by PhpStorm.
 * User: j.calabrese
 * Date: 01/07/14
 * Time: 11:49
 */

namespace Entity;


use Utility\UtilityXml;

class PmdItem
{
    protected $className;
    protected $namespace;
    protected $bundleName;
    protected $typeName;
    protected $stats;

    public function __construct($listNodes, $categories)
    {
        $this->stats = array();
        foreach ($listNodes->children() as $violation) {
            if ($this->className == null) {
                $this->className = UtilityXml::getAttribute("class", $violation);
                $this->typeName = $this->getType($this->className, $categories);
            }
            if (!isset($namespace)) {
                $this->namespace = UtilityXml::getAttribute("package", $violation);
                //$this->bundleName = $this->getBundle($this->namespace);
            }
            array_push($this->stats, new Violation(
                UtilityXml::getAttribute("beginline", $violation),
                UtilityXml::getAttribute("endline", $violation),
                UtilityXml::getAttribute("priority", $violation),
                UtilityXml::getAttribute("rule", $violation),
                UtilityXml::getAttribute("value", $violation)
            ));
        }
    }

    /**
     * Get the Type name from the class name
     *
     * @param  $className String name of the class
     *
     * @param $categories
     * @return String Type associated to the class name
     */
    private function getType($className, $categories)
    {
        $type = '';
        $isFound = false;
        foreach ($categories as $key => $value) {
            if (preg_match("#" . $key . "$#", $className)) {
                $type = $key;
                $isFound = true;
                break;
            }
        }
        if (!$isFound) {
            $type = "Other";
        }
        return $type;
    }

    /**
     * @param mixed $bundleName
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;
    }

    /**
     * @return mixed
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * @param null $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return null
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $listViolations
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
    }

    /**
     * @return mixed
     */
    public function getStats()
    {
        return array('pmd',$this->stats);
    }

    /**
     * @param null $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param String $typeName
     */
    public function setTypeName($typeName)
    {
        $this->typeName = $typeName;
    }

    /**
     * @return String
     */
    public function getTypeName()
    {
        return $this->typeName;
    }
} 