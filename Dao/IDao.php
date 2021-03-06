<?php

namespace Dao;

/**
 * Generic Dao to do action on the given database
 */
interface IDao
{
    /**
     * Get a document with the given ID
     *
     * @param  Int $name
     *
     * @return Object
     */
    public function find($name);

    /**
     * List documents with the given parameters. If no parameter is given, we will list all document
     *
     * @return Array of Objects
     */
    public function listAll();

    /**
     * List all document with a filter on the type of the class
     *
     * @param $type
     * @internal param $designDocument
     * @return mixed
     */
    public function listByType($type);

    /**
     * List all documents with a filter on the bundle
     *
     * @param $bundle
     * @internal param $designDocument
     * @return mixed
     */
    public function listByBundle($bundle);

    /**
     * @return mixed
     */
    public function listAllBundles();

    /**
     * Add or update a document with the given ID and the given content.
     * @param  Int $name
     * @param  Object $object
     *
     * @return Object saved
     */
    public function save($name, $object);

    /**
     * Delete a document with the given ID.
     * @param  Int $id
     *
     * @return
     */
    public function delete($id);
}