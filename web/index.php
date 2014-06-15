<?php

require_once __DIR__.'/../vendor/autoload.php';

use Service\MetricService;
use Service\ParserService;
use Utility\CouchDbWrapper;
use Utility\DataManagementUtility;
use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\View\FolderDesignDocument;
use Doctrine\CouchDB\View\DesignDocument;

$app = new Silex\Application();

$app['couchBdConfig'] = array(
		'dbname' => 'clover',
		'host'	 =>	'192.168.56.101'
	);

$app['metricConfig'] = array(
	'interval' => array (0.30, 0.50, 0.80, 1)
);

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../log/development.log',
));

$app['couchDbWrapper'] =$app->share(function ($app) {
	return new CouchDbWrapper();
});

$app['dataManagementUtility'] =$app->share(function ($app) {
	return new DataManagementUtility($app['monolog']);
});

$app['couchDbClient'] =$app->share(function ($app) {

	return CouchDBClient::create($app['couchBdConfig']);
});

$app['parserService'] = $app->share(function ($app) {
	return new ParserService(
					$app['couchDbClient'],
					$app['monolog']);
});

$app['metricService'] = $app->share(function ($app) {
	return new MetricService(
					$app['parserService'],
					$app['couchDbClient'],
					$app['monolog']);
});

$app->get('/load', function() use($app) {
	try {
			$app['couchDbClient']->deleteDatabase($app['couchBdConfig']['dbname']);
			$app['couchDbClient']->createDatabase($app['couchBdConfig']['dbname']);
	} catch (Exception $e) {
		$app['monolog']->addDebug("Database already exists.");
	}
	try {
		$view = new FolderDesignDocument("../Couchdb");
		$app['couchDbClient']->createDesignDocument("filters", $view);
		$app["metricService"]->load();
		return "The file has been loaded in the server";
	} catch (Exception $e) {
		return $e->getMessage();
	}
});

$app->get('/all', function() use($app) {
	try {
		$view = new FolderDesignDocument("../Couchdb");
		$list = $app["metricService"]->listAll();
		$listInterval = $app['dataManagementUtility']->groupByInterval($list['rows'], $app['metricConfig']['interval']);
		$result = array();
		$result['data'] = $list;
		$result ['stat'] = $listInterval;
		return json_encode($result);

	} catch (Exception $e) {
		return $e->getMessage();
	}
});

$app->get('/type/{typeName}', function ($typeName) use ($app) {
   try {
		$view = new FolderDesignDocument("../Couchdb");
		$list = $app["metricService"]->listByType($view, $typeName);
		$listInterval = $app['dataManagementUtility']->groupByInterval($list, $app['metricConfig']['interval'], 'value');
		$result = array();
		$result['data'] = $list;
		$result ['stat'] = $listInterval;
		return json_encode($result);

	} catch (Exception $e) {
		return $e->getMessage();
	}
});

$app->get('/bundle/{bundleName}', function ($bundleName) use ($app) {
   try {
		$view = new FolderDesignDocument("../Couchdb");
		$list = $app["metricService"]->listByBundle($view, $bundleName);
		return json_encode($list);
	} catch (Exception $e) {
		return $e->getMessage();
	}
});

$app->run();
