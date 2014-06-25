<?php

namespace Dao;

use Exception\NothingFoundException;

class Dao
{
	protected $bdClient;

	public function __construct ($bdClient) {
		$this->bdClient = $bdClient;
	}
	/**
	 * Get a document with the given name ID
	 * 
	 * @param  String 	$name
	 * 
	 * @return Object
	 *
	 * @throws NothingFoundException If no document is found
	 * 
	 */
	public function find($name) {
		$docuement = $this->bdClient->findDocument($name);
		if($document == null || $document->status == 404) {
			throw new NothingFoundException("The document with the name '". $name ."' not found !");
		}
		return $document;
	}

	/**
	 * List documents with the given parameters. If no parameter is given, we will list all document
	 * 
	 * @param  Array $params 	Filter for the query
	 * 
	 * @return Array of Objects
	 */
	public function list($params = null) {

		return null;
	}

	/**
	 * Add or update a document with the given ID and the given content.
	 * @param  Int 		$id
	 * @param  Object 	$object
	 * 
	 * @return Object saved
	 */
	public function save($name, $object) {
		try {
			$document = $this->find($name);	
			$document = $this->couchDbClient->putDocument((array) $object, $name, $document->body['_rev']);
		} catch (NothingFoundException $e) {
			$document = $this->couchDbClient->postDocument((array) $object);
		}
		
		return $document;
	}

	/**
	 * Delete a document with the given ID.
	 * @param  Int 		$id
	 * 
	 * @return 
	 */
	public function delete($id) {

	}
}