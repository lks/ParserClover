<?php

require_once __DIR__.'/../vendor/autoload.php';

use Service\MetricService;
use Service\ParserService;
use Utility\CouchDbWrapper;
use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\View\FolderDesignDocument;
use Doctrine\CouchDB\View\DesignDocument;

$app = new Silex\Application();

$app['couchBdConfig'] = array(
		'dbname' => 'clover',
		'host'	 =>	'192.168.56.101'
	);

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../log/development.log',
));

$app['couchDbWrapper'] =$app->share(function ($app) {
	return new CouchDbWrapper();
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
		echo var_dump($list);
		
		return "Works";
	} catch (Exception $e) {
		return $e->getMessage();
	}
});

$app->get('/type/{typeName}', function ($typeName) use ($app) {
   try {
		$view = new FolderDesignDocument("../Couchdb");
		$list = $app["metricService"]->listByType($view, $typeName);
		echo var_dump($list);
		
		return "Works";
	} catch (Exception $e) {
		return $e->getMessage();
	}
});

$app->get('/bundle/{bundleName}', function ($typeName) use ($app) {
   try {
		$view = new FolderDesignDocument("../Couchdb");
		$list = $app["metricService"]->listByType($view, $bundleName);
		echo var_dump($list);
		
		return "Works";
	} catch (Exception $e) {
		return $e->getMessage();
	}
});

$app->run();