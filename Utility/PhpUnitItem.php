<?php
/**
 * Created by PhpStorm.
 * User: j.calabrese
 * Date: 30/06/14
 * Time: 15:56
 */

namespace Utility;


class PhpUnitItem {

    protected $className;
    protected $namespace;
    protected $bundleName;
    protected $type;
    protected $lineAverage;
    protected $methodAverage;

    public function __construct($listNodes, $categories)
    {
        foreach ($listNodes as $node) {
            if ('class' == $node->getName()) {
                $this->setClassInformation($node, $categories);
            } else if('totals' == $node->getName()) {
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
        echo "dans setClassInformation";
        foreach ($classNode->attributes() as $key => $value) {
            if ($key == 'name') {
                $this->className = '' . $value;
            }
        }
    }

    public function setStatInformation($statNode)
    {
        foreach ($statNode->children() as $result) {
            //define the line average coverage for the given class
            if ('lines' == $result->getName()) {
                $this->lineAverage = $this->computeAverage($result->attributes());
            } else if ('methods' == $result->getName()) {
                $this->methodAverage = $this->computeAverage($result->attributes());
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


    private function computeAverage($attributes)
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
        if($nbExecutable > 0) {
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
    public function setTypeName($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getTypeName()
    {
        return $this->type;
    }

    public function getStats()
    {
        $result = array();
        $result['lineAverage'] = $this->lineAverage;
        $result['methodAverage'] = $this->methodAverage;
        return $result;
    }

} 