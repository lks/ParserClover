<?php

namespace Service;

 use Service\ParserService;
 use Exception\FileOpeningException;
  use Exception\NothingFoundException;
 use Doctrine\CouchDB\CouchDBClient;

/**
 * This class manage the metric query to provid to the controller the datas.
 */
class MetricService
{
	protected $parserService;
	protected $couchDbClient;
	protected $monolog;

	/**
     * @param ParserService $parserService
     * @param CouchDBClient $couchDbClient
     * @param Monolog $monolog
     */
	public function __construct($parserService, $couchDbClient, $monolog) {
		$this->parserService = $parserService;
		$this->couchDbClient = $couchDbClient;
		$this->monolog 		 = $monolog;
	}

	/**
     * Load the data from the file defined in configuration
     *
     * @return Nothing
     */
	public function load() {
		$xml = null;
		$filename = 'clover.xml';

		$this->monolog->addDebug("Begin the loading...");
		if (file_exists('../'.$filename)) {
		    $xml = simplexml_load_file('../'.$filename);
		} else {
			return "error";

		}
		$this->monolog->addDebug(sprintf("File '%s' is loaded", $filename));

		//list all file without package
		// TODO : Include this in configuration
		$categories = ['Controller', 'DAO', 'Entity', 'Service', 'Exception', 'Other'];
		$this->parserService->createMetric($xml->project, $categories);
	}

	/**
     * List all the document contained in the Database
     *
     * @return Array with all Document Item
     */
	public function listAll() {
		$response = $this->couchDbClient->allDocs();
		if($response == null) {
			throw new NothingFoundException("No item has been found.");
		}
		return $response->body;

	}

	/**
     * List by type the document contained in the Database
     * @param  DesignDocument Design document associated to the view
     * @param  String Type name. It can have the following value: Controller, DAO, Service, Entity, Exception, Other
     *
     * @return Array with all Document Item
     */
	public function listByType($designDocument, $type) {
		return $this->getFilteredItems('filters', 'type', $designDocument, $type);
	}

	/**
     * List by bundle name the document contained in the Database
     * @param  DesignDocument Design document associated to the view
     * @param  String bundle name.
     *
     * @return Array with all Document Item
     */
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
