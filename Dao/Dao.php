<?php

namespace Dao;

use Exception\NotFoundException;
use Exception\NothingFoundException;
use Doctrine\CouchDB\CouchDBClient;
use Monolog\Logger;

class Dao implements IDao
{
    protected $dbClient;
    protected $dbViews;
    protected $logger;

    public function __construct(CouchDBClient $dbClient, $dbViews, Logger $logger)
    {
        $this->dbClient = $dbClient;
        $this->dbViews = $dbViews;
        $this->logger = $logger;
    }

    /**
     * List documents with the given parameters. If no parameter is given, we will list all document
     *
     * @param Array $params Filter for the query
     * @throws \Exception\NothingFoundException
     * @return Array of Objects
     */
    public function listAll($params = null)
    {
        $response = null;
        if ($params == null) {
            $response = $this->dbClient->allDocs();
            if ($response == null) {
                throw new NothingFoundException('No document exist in the database.');
            }
            return $response;
        }
        return null;
    }

    /**
     * Add or update a document with the given ID and the given content.
     * @param $name
     * @param  Object $object
     * @return Object saved
     */
    public function save($name, $object)
    {
        $this->logger->addDebug("[DAO] Save the object : " . $name);
        try {
            try {
                //the target document doesn't exist, so we have to create one
                $document = $this->find($name);
                if ($document == null) {
                    $this->logger->addDebug("[DAO] Document null");
                }
                $document = $this->dbClient->putDocument((array)$object, $document['_id'], $document['_rev']);
            } catch (NotFoundException $e) {
                $this->logger->addDebug("[DAO] Object not found : " . $name);
                $document = $this->dbClient->postDocument((array)$object);
            }
        } catch (HTTPException $e) {
            $this->logger->addDebug("An exception has occured : " . $e->getMessage());
            return null;
        }
        return $document;

    }

    /**
     * Get a document with the given name ID
     *
     * @param  String $name
     * @return Object
     * @throws NotFoundException If no document is found
     *
     */
    public function find($name)
    {
        $document = $this->getFilteredItems('filters', 'name', $name);
        if ($document == null) {
            throw new NotFoundException("The document with the name '" . $name . "' not found !");
        }
        return $document[0]['value'];
    }

    /**
     * Delete a document with the given ID.
     * @param  Int $id
     *
     * @return void
     */
    public function delete($id)
    {

    }

    /**
     * List all document with a filter on the type of the class
     *
     * @param $type
     * @internal param $designDocument
     * @return mixed
     */
    public function listByType($type)
    {
        return $this->getFilteredItems('filters', 'type', $type);
    }

    /**
     *
     * @param $designDocName
     * @param $viewName
     * @param $filterValue
     * @param bool $group
     * @throws \Exception\NothingFoundException
     * @internal param $designDocument
     * @return mixed
     */
    protected function getFilteredItems($designDocName, $viewName, $filterValue, $group = false)
    {
        $viewQuery = $this->dbClient->createViewQuery($designDocName, $viewName, $this->dbViews);
        if ($filterValue != null) {
            $viewQuery->setStartKey($filterValue);
            $viewQuery->setEndKey($filterValue);
        }
        if ($group) {
            $viewQuery->setGroup($group);
        }
        $response = $viewQuery->execute();
        if ($response == null) {
            throw new NothingFoundException("No item has been found.");

        }
        return $response->toArray();
    }

    /**
     * List all documents with a filter on the bundle
     *
     * @param $bundle
     * @return mixed
     */
    public function listByBundle(
        $bundle)
    {
        return $this->getFilteredItems('filters', 'bundle', $bundle);
    }

    /**
     * List all documents with a filter on the bundle
     *
     * @internal param $bundle
     * @return mixed
     */
    public function listAllBundles()
    {
        return $this->getFilteredItems('filters', 'all-bundles', null, true);
    }

    /**
     * @return \Doctrine\CouchDB\CouchDBClient
     */
    public function getDbClient()
    {
        return $this->dbClient;
    }

    /**
     * @param \Doctrine\CouchDB\CouchDBClient $bdClient
     */
    public function setDbClient($bdClient)
    {
        $this->dbClient = $bdClient;
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \Monolog\Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return mixed
     */
    public function getDbViews()
    {
        return $this->dbViews;
    }

    /**
     * @param mixed $dbViews
     */
    public function setDbViews($dbViews)
    {
        $this->dbViews = $dbViews;
    }

}