<?php

namespace Service;

 use Dao\Dao;
 use Service\ParserService;
 use Exception\FileOpeningException;
  use Exception\NothingFoundException;
 use Doctrine\CouchDB\CouchDBClient;

/**
 * This class manage the metric query to provide to the controller the datas.
 */
 class MetricService implements IMetricService
 {
     protected $parserService;
	protected $couchDbClient;
    protected $classCategories;
    protected $monolog;
    protected $dao;

    /**
     * @param ParserService $parserService
     * @param CouchDBClient $couchDbClient
     * @param $classCategories
     * @param Monolog $monolog
     * @param \Dao\Dao $dao
     */
    public function __construct($parserService, $couchDbClient, $classCategories, $monolog, Dao $dao)
    {
        $this->parserService = $parserService;
        $this->couchDbClient = $couchDbClient;
		$this->monolog 		 = $monolog;
        $this->dao           = $dao;
	}

    /**
     * List all the document contained in the Database
     *
     * @throws \Exception\NothingFoundException
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
     public function listByType($type)
     {
         return $this->dao->listByType($type);
     }

     /**
      * List by bundle name the document contained in the Database
      * @param $bundleName
      * @param $isMetric
      *
      * @return Array with all Document Item
     */
     public function listByBundle($bundleName, $isMetric)
     {
         $list = $this->dao->listByBundle($bundleName);
         if ($isMetric === 'true') {
            $list = $this->listByBundleWithMetrics($list);
        }
        return $list;
    }

     public function listAllBundles()
     {
         return $this->dao->listAllBundles();
     }

     /**
      * List by bundle name the document contained in the Database
     * @param  DesignDocument Design document associated to the view
     * @param  String bundle name.
     *
     * @return Array with all Document Item
     */
     private function listByBundleWithMetrics($list)
     {
         $fitList = array();
         foreach ($list as $key => $value) {
             if ($value['value']['stats'] != null
                 && count($value['value']['stats']) == 1
                 && array_key_exists('phpUnit', $value['value']['stats'])
             ) {

                 if ($this->classCategories[$value['value']['type']] > $value['value']['stats']['phpUnit']['lineAverage']) {
                     array_push($fitList, $value);
                 }
             } else if ($value['value']['stats'] != null && array_key_exists('pmd', $value['value']['stats'])) {
                 array_push($fitList, $value);
             }
         }
         return $fitList;
     }
 }
