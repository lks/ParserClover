<?php

namespace Service;

 use Service\parserService;
 use Exception\FileOpeningException;
  use Exception\NothingFoundException;
 use Doctrine\CouchDB\CouchDBClient;

class MetricService
{
	protected $parserService;
	protected $couchDbClient;
	protected $monolog;

	public function __construct($parserService, $couchDbClient, $monolog) {
		$this->parserService = $parserService;
		$this->couchDbClient = $couchDbClient;
		$this->monolog 		 = $monolog;
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
		$categories = ['Controller', 'DAO', 'Entity', 'Service', 'Exception', 'Other'];
		return $this->parserService->createMetric($xml->project, $categories);
	}

	public function listAll($designDocument) {
		$response = $this->couchDbClient->allDocs();
		if($response == null) {
			throw new NothingFoundException("No item has been found.");
		}
		return $response->body;
		
	}

	public function listByType($designDocument, $type) {
		return $this->getFilteredItems('filters', 'type', $designDocument, $type);
	}

	public function listByBundle($designDocument, $bundleName) {
		return $this->getFilteredItems('filters', 'bundle', $designDocument, $bundleName);
	}

	protected function getFilteredItems($designDocName, $viewName, $designDocument, $filterValue) {
		$viewQuery = $this->couchDbClient->createViewQuery($designDocName, $viewName, $designDocument);
		$viewQuery->setStartKey($filterValue);
		$viewQuery->setEndKey($filterValue);
		$response = $viewQuery->execute();
		if($response == null) {
			throw new NothingFoundException("No item has been found.");
		}
		return $response->toArray();
	}

}