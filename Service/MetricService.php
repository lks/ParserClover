<?php

namespace Service;

 require_once __DIR__.'/../Exception/FileOpeningException.php';
require_once __DIR__.'/../Service/ParserService.php';

class MetricService
{
	protected $parserService;
	protected $monolog;


	public function __construct($parserService, $monolog) {
		$this->parserService = $parserService;
		$this->monolog = $monolog;
	}

	public function load() {
		$xml = null;
		$filename = 'clover.xml';
		
		$this->monolog->addDebug("Begin the loading...");
		//TODO : Include this in configuration
		if (file_exists('../'.$filename)) {
		    $xml = simplexml_load_file('../'.$filename);
		} else {
			return "error";
			
		}
		$this->monolog->addDebug(sprintf("File '%s' is loaded", $filename));

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