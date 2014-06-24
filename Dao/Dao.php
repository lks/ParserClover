<?php

namespace Dao;

class Dao
{
	/**
	 * Get a document with the given ID
	 * 
	 * @param  Int 	$id
	 * 
	 * @return Object
	 */
	public function get($id) {
		return null;
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
	public function save($id, $object) {
		return null;
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