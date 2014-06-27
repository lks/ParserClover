<?php

namespace Entity;


class FileStats
{
    /* Name of the file */

    protected $name;

    /* Name of the namespace of the file */

    protected $namespace;

    /* Array contained the number of the violation by type */

    protected $violations;

    /* Type of class */

    protected $type;

    /* bundle of class */

    protected $bundle;

    public function __construct($name, $namespace, $violations, $type, $bundle)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->violations = $violations;
        $this->type = $type;
        $this->bundle = $bundle;
    }

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of namespace.
     *
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Sets the value of namespace.
     *
     * @param mixed $namespace the namespace
     *
     * @return self
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Gets the value of violations.
     *
     * @return mixed
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * Sets the value of violations.
     *
     * @param mixed $violations the violations
     *
     * @return self
     */
    public function setViolations($violations)
    {
        $this->violations = $violations;

        return $this;
    }

    /**
     * Gets the value of type.
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the value of type.
     *
     * @param mixed $type the type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the value of bundle.
     *
     * @return mixed
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Sets the value of bundle.
     *
     * @param mixed $bundle the bundle
     *
     * @return self
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }
}