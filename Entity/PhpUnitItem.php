<?php
/**
 * Created by PhpStorm.
 * User: j.calabrese
 * Date: 30/06/14
 * Time: 15:56
 */

namespace Entity;

use Utility\UtilityXml;

class PhpUnitItem
{

    protected $className;
    protected $namespace;
    protected $bundleName;
    protected $typeName;
    protected $lineAverage;
    protected $methodAverage;

    public function __construct($listNodes, $categories)
    {
        foreach ($listNodes as $node) {
            if ('class' == $node->getName()) {
                $this->setClassInformation($node, $categories);
            } else if ('totals' == $node->getName()) {
                $this->setStatInformation($node);
            }
        }
    }

    /**
     * Get the class information from the class node of a php unit file report.
     *
     * @param $classNode
     *
     * return Array
     * @param $categories
     */
    public function setClassInformation($classNode, $categories)
    {
        foreach ($classNode->attributes() as $key => $value) {
            if ($key == 'name') {
                $this->className = '' . $value;
                $this->typeName = $this->getType($value, $categories);
            }
        }
        foreach ($classNode->children() as $result) {
            if ('namespace' == $result->getName()) {
                $this->namespace = UtilityXml::getAttribute('name', $result);
                $this->bundleName = UtilityXml::getBundle($this->namespace);
            }
        }
    }

    public function setStatInformation($statNode)
    {
        foreach ($statNode->children() as $result) {
            //define the line average coverage for the given class
            if ('lines' == $result->getName()) {
                $this->lineAverage = $this->computeLineAverage($result->attributes());
            } else if ('methods' == $result->getName()) {
                $this->methodAverage = $this->computeMethodAverage($result->attributes());
            }
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


    private function computeLineAverage($attributes)
    {
        $nbExecutable = 0;
        $nbExecuted = 0;
        foreach ($attributes as $key => $value) {
            if ($key == 'executable') {
                $nbExecutable = $value;
            } elseif ($key == 'executed') {
                $nbExecuted = $value;
            }
        }
        if ($nbExecutable > 0) {
            return $nbExecuted / $nbExecutable;
        }
        return null;
    }

    private function computeMethodAverage($attributes)
    {
        $nbExecutable = 0;
        $nbExecuted = 0;
        foreach ($attributes as $key => $value) {
            if ($key == 'count') {
                $nbExecutable = $value;
            } elseif ($key == 'tested') {
                $nbExecuted = $value;
            }
        }
        if ($nbExecutable > 0) {
            return $nbExecuted / $nbExecutable;
        }
        return null;
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
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $lineAverage
     */
    public function setLineAverage($lineAverage)
    {
        $this->lineAverage = $lineAverage;
    }

    /**
     * @return mixed
     */
    public function getLineAverage()
    {
        return $this->lineAverage;
    }

    /**
     * @param mixed $methodAverage
     */
    public function setMethodAverage($methodAverage)
    {
        $this->methodAverage = $methodAverage;
    }

    /**
     * @return mixed
     */
    public function getMethodAverage()
    {
        return $this->methodAverage;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $type
     */
    public function setTypeName($typeName)
    {
        $this->typeName = $typeName;
    }

    /**
     * @return mixed
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    public function getStats()
    {
        $result = array();
        $result['lineAverage'] = $this->lineAverage;
        $result['methodAverage'] = $this->methodAverage;
        return array('phpUnit' => $result);
    }

} 