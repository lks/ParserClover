<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dao\Dao;
use Service\MetricService;
use Service\ParserService;
use Utility\CouchDbWrapper;
use Utility\DataManagementUtility;
use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\View\FolderDesignDocument;
use Symfony\Component\Finder\Finder;

$app = new Silex\Application();

//define the configuration for the couchDb tool
$app['couchBdConfig'] = array(
    'dbname' => 'improve-quality',
    'host' => '192.168.56.101'
);

$app['classCategories'] = array(
    'Controller' => 0,
    'DAO' => 0.85,
    'Entity' => 0.80,
    'Service' => 0.85,
    'Other' => 0.80,
    'Test' => 0,
    'Renderer' => 0,
    'Extension' => 0
);

// define our thresholds criterias
$app['metricConfig'] = array(
    'interval' => array(0.30, 0.50, 0.80, 1)
);

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../log/development.log',
));

$app->register(new JMS\SerializerServiceProvider\SerializerServiceProvider(), array(
    'serializer.src_directory' => '../vendor/jms/serializer-bundle',
    'serializer.cache.directory' => '../cache'
));

$app['couchDbWrapper'] = $app->share(function ($app) {
    return new CouchDbWrapper();
});

$app['finder'] = $app->share(function ($app) {
    return new Finder();
});

$app['dataManagementUtility'] = $app->share(function ($app) {
    return new DataManagementUtility($app['monolog']);
});

/**
 * Used to get and to insert in Database the files associate their metrics.
 * This service needs as dependencies:
 * - couchDbClient: define our SGBD client,
 * - monolog: manage the log.
 */
$app['couchDbClient'] = $app->share(function ($app) {

    return CouchDBClient::create($app['couchBdConfig']);
});

$app['dao'] = $app->share(function ($app) {

    return new Dao($app['couchDbClient'], $app['monolog']);
});

/**
 * Used to get and to insert in Database the files associate their metrics.
 * This service needs as dependencies:
 * - couchDbClient: define our SGBD client,
 * - monolog: manage the log.
 */
$app['parserService'] = $app->share(function ($app) {
    return new ParserService(
        $app['monolog'],
        $app['finder'],
        $app['classCategories'],
        $app['dao']);
});

/**
 * Define the Metrics Service as a shared service. This service will manage all the metrics actions
 * management. This service needs as dependencies:
 * - ParserService: get the metrics for a given file,
 * - couchDbClient: define our SGBD client,
 * - monolog: manage the log.
 */
$app['metricService'] = $app->share(function ($app) {
    return new MetricService(
        $app['parserService'],
        $app['couchDbClient'],
        $app['monolog'],
        $app['dao']);
});

/**
 * All service will get all the files metrics and the statistics associated.
 *
 * @throws Exception If an error occured during the getting of the files metrics.
 *
 * @return Json object with the following formation:
 *                     {"data": [
 *                        {
 *                          id: "",
 *                          "_rev": "",
 *                          "doc":
 *                          {
 *                            object serialized...
 *                          }
 *                        },
 *                      ],
 *                      "stat": [..., ..., ..., ...]
 *                     }
 */
$app->get('/all', function () use ($app) {
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

/**
 * Type service allow us to get the metrics for a given type of Classes. A type of class can be:
 *    - Controller,
 *    - Service,
 *    - DAO,
 *      - Entity,
 *      - Exception.
 *
 * @throws Exception If an error occured during the getting of the files metrics.
 *
 * @return Json object with the following formation:
 *                     {"data": [
 *                        {
 *                          id: "",
 *                          "_rev": "",
 *                          "value":
 *                          {
 *                            object serialized...
 *                          }
 *                        },
 *                      ],
 *                      "stat": [..., ..., ..., ...]
 *                     }
 */
$app->get('/type/{typeName}', function ($typeName) use ($app) {
    try {
        $view = new FolderDesignDocument("../Couchdb");
        $list = $app["metricService"]->listByType($view, $typeName);
        return $app['serializer']->serialize($list, 'json');
    } catch (Exception $e) {
        return $e->getMessage();
    }
});

/**
 * Type service allow us to get the metrics for a given bundle. A bundle can be:
 *    - AmoBundle,
 *    - ProfileBundle,
 *    - SecurityBundle,
 *    - ...
 *
 * @throws Exception If an error occured during the getting of the files metrics.
 *
 * @return Json object with the following formation:
 *                     {"data": [
 *                        {
 *                          id: "",
 *                          "_rev": "",
 *                          "value":
 *                          {
 *                            object serialized...
 *                          }
 *                        },
 *                      ],
 *                      "stat": [..., ..., ..., ...]
 *                     }
 */
$app->get('/bundle/{bundleName}', function ($bundleName) use ($app) {
    try {
        $view = new FolderDesignDocument("../Couchdb");
        $list = $app["metricService"]->listByBundle($view, $bundleName);
        return json_encode($list);
    } catch (Exception $e) {
        return $e->getMessage();
    }
});

$app->get('/report', function () use ($app) {
    try {
        //$view = new FolderDesignDocument("../Couchdb");
        $app['couchDbClient']->deleteDatabase($app['couchBdConfig']['dbname']);
        $app['couchDbClient']->createDatabase($app['couchBdConfig']['dbname']);
        $list = $app["parserService"]->mergeReport();
        return $app['serializer']->serialize($list, 'json');
    } catch (Exception $e) {
        return $e->getMessage();
    }
});

$app->get('/allImprovements', function () use ($app) {
    try {
       $list = $app["metricService"]->listAllImprovements();
       return $app['serializer']->serialize($list, 'json');
    } catch (Exception $e) {
        return $e->getMessage();
    }
});




$app->run();
