<?php

namespace Dao;

interface Dao
{
	public function get($id);

	public function list($params = null);

	public function save($id, $object);

	public function delete($id);
}