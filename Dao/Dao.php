<?php

namespace Dao;

use Exception\NotFoundException;
use Exception\NothingFoundException;
use Doctrine\CouchDB\CouchDBClient;
use Monolog\Logger;

class Dao implements IDao
{
    protected $bdClient;
    protected $logger;

    public function __construct(CouchDBClient $bdClient, Logger $logger)
    {
        $this->bdClient = $bdClient;
        $this->logger = $logger;
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
        $document = $this->bdClient->findDocument($name);
        if ($document == null || $document->status == 404) {
            throw new NotFoundException("The document with the name '" . $name . "' not found !");
        }
        return $document;
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
        if ($params == null) {
            $response = $this->bdClient->allDocs();
            if ($response == null) {
                throw new NothingFoundException('No document exist in the database.');
            }
        } else {

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
        $this->logger->addDebug("[DAO] Save the object : ".$name);
        try {
            try {
                //the target document doesn't exist, so we have to create one
                $document = $this->find($name);
                if($document == null) {
                    $this->logger->addDebug("[DAO] Document null");
                }

                $document = $this->bdClient->putDocument((array)$object, $name, $document->body['_rev']);
            } catch (NotFoundException $e) {
                $this->logger->addDebug("[DAO] Object not found : ".$name);
                $document = $this->bdClient->postDocument((array)$object);
            }
        } catch (HTTPException $e) {
            $this->logger->addDebug("An exception has occured : ".$e->getMessage());
            return null;
        }
        return $document;

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
     * @param $designDocument
     * @param $type
     * @return mixed
     */
    public function listByType($designDocument, $type)
    {
        return $this->getFilteredItems('filters', 'type', $designDocument, $type);
    }

    /**
     * List all documents with a filter on the bundle
     *
     * @param $designDocument
     * @param $bundle
     * @return mixed
     */
    public function listByBundle($designDocument, $bundle)
    {
        return $this->getFilteredItems('filters', 'bundle', $designDocument, $bundle);
    }

    /**
     *
     * @param $designDocName
     * @param $viewName
     * @param $designDocument
     * @param $filterValue
     * @return mixed
     * @throws \Exception\NothingFoundException
     */
    protected function getFilteredItems($designDocName, $viewName, $designDocument, $filterValue)
    {
        $viewQuery = $this->bdClient->createViewQuery($designDocName, $viewName, $designDocument);
        $viewQuery->setStartKey($filterValue);
        $viewQuery->setEndKey($filterValue);
        $response = $viewQuery->execute();
        if ($response == null) {
            throw new NothingFoundException("No item has been found.");
        }
        return $response->toArray();
    }
}