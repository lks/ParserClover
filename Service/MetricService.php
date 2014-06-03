<?php

namespace Service;

 require_once __DIR__.'/../Exception/FileOpeningException.php';
require_once __DIR__.'/../Service/ParserService.php';

class MetricService
{
	protected $parserService;

	public function __construct($parserService) {
		$this->parserService = $parserService;
	}

	public function load() {
		$xml = null;
		//TODO : Include this in configuration
		if (file_exists('../clover.xml')) {
		    $xml = simplexml_load_file('../clover.xml');
		} else {
			return "error";
			
		}
		//list all file without package
		// TODO : Include this in configuration
		$categories = ['Controller', 'Dao', 'Entity', 'Service', 'Exception', 'Other'];
		return $this->parserService->createMetric($xml->project, $categories);
	}

	public function listAll() {

	}

	public function listByType($type) {

	}

	public function listByBundle($bundleName) {

	}

}