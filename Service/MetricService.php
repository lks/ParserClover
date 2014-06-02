<?php

namespace Service;

 require_once __DIR__.'/../Exception/FileOpeningException.php';

class MetricService
{
	protected $parserService;
	protected $couchDbWrapper;

	public function __construct($parserService, $couchDbWrapper) {
		$this->parserService = $parserService;
		$this->couchDbWrapper = $couchDbWrapper;
	}

	public function load() {
		return "error";
		$xml = null;
		//TODO : Include this in configuration
		if (file_exists('clover.xml')) {
		    $xml = simplexml_load_file('clover.xml');
		} else {
			return "error";
			
		}
		//list all file without package
		// TODO : Include this in configuration
		$categories = ['Controller', 'Dao', 'Entity', 'Service', 'Exception', 'Other'];
		$parserService->createMetric($xml->project, $categories);
	}

	public function listAll() {

	}

	public function listByType($type) {

	}

	public function listByBundle($bundleName) {

	}

}