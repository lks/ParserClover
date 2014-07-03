<?php
/**
 * Created by PhpStorm.
 * User: j.calabrese
 * Date: 03/07/14
 * Time: 14:31
 */

namespace Service;


use Exception\NothingFoundException;

interface IMetricService
{
    /**
     * List all the document contained in the Database
     *
     * @throws NothingFoundException
     * @return Array with all Document Item
     */
    public function listAll();

    /**
     * List by type the document contained in the Database
     * @param  DesignDocument Design document associated to the view
     *
     * @return Array with all Document Item
     */
    public function listByType($type);

    /**
     * List by bundle name the document contained in the Database
     * @param $bundleName
     * @param $isMetric
     *
     * @return Array with all Document Item
     */
    public function listByBundle($bundleName, $isMetric);
} 