<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dao\Dao;
use Service\MetricService;
use Service\ParserService;
use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\View\FolderDesignDocument;
use Symfony\Component\Finder\Finder;

$app = new Silex\Application();

//define the configuration for the couchDb tool
$app['couchBdConfig'] = array(
    'dbname' => 'improve-quality',
    'host'   => '192.168.56.101'
);

$app['classCategories'] = array(
    'Controller' => 0,
    'DAO'        => 0.85,
    'Entity'     => 0.80,
    'Service'    => 0.85,
    'Other'      => 0.80,
    'Test'       => 0,
    'Renderer'   => 0,
    'Extension'  => 0
);

// define our thresholds criterias
$app['metricConfig'] = array(
    'interval' => array(0.30, 0.50, 0.80, 1)
);

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../log/development.log',
));

$app->register(new JMS\SerializerServiceProvider\SerializerServiceProvider(), array(
    'serializer.src_directory'   => '../vendor/jms/serializer-bundle',
    'serializer.cache.directory' => '../cache'
));

$app['finder'] = $app->share(function ($app) {
    return new Finder();
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

    $dao = new Dao($app['couchDbClient'], new FolderDesignDocument("../Couchdb"), $app['monolog']);
    try {
        $dao->getDbClient()->getDatabaseInfo($app['couchBdConfig']['dbname']);
    } catch (\Doctrine\CouchDB\HTTP\HTTPException $e) {
        $dao->getDbClient()->createDatabase($app['couchBdConfig']['dbname']);
    }
    return $dao;

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
        $app['classCategories'],
        $app['monolog'],
        $app['dao']);
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
 * @return Json object
 */
$app->get('/type/{typeName}', function ($typeName) use ($app) {
    try {
        $list = $app["metricService"]->listByType($typeName);
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
 * @return Json object
 */
$app->get('/bundles/{bundleName}/isWithMetric/{isMetric}', function ($bundleName, $isMetric) use ($app) {
    try {
        $list = $app["metricService"]->listByBundle($bundleName, $isMetric);
        return $app['serializer']->serialize($list, 'json');
    } catch (Exception $e) {
        return $e->getMessage();
    }
});

$app->post('/load', function () use ($app) {
    try {
        $count = $app["parserService"]->mergeReport();
        return $app['serializer']->serialize($count, 'json');
    } catch (Exception $e) {
        return $e->getMessage();
    }
});

$app->get('/bundles', function () use ($app) {
    try {
        $list = $app["metricService"]->listAllBundles();
        return $app['serializer']->serialize($list, 'json');
    } catch (Exception $e) {
        return $e->getMessage();
    }
});
$app->run();
