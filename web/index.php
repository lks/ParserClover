<?php

require_once __DIR__.'/../vendor/autoload.php';

use Service\MetricService;
use Service\ParserService;
use Utility\CouchDbWrapper;

$app = new Silex\Application();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../log/development.log',
));

$app['couchDbWrapper'] =$app->share(function ($app) {
	return new CouchDbWrapper();
});

$app['parserService'] = $app->share(function ($app) {
	return new ParserService($app['couchDbWrapper'], $app['monolog']);
});

$app['metricService'] = $app->share(function ($app) {
	return new MetricService($app['parserService'], $app['monolog']);
});

$app->get('/load', function() use($app) {
	try {
		$app["metricService"]->load();
		return "Works";
	} catch (Exception $e) {
		return $e->getMessage();
	}
});

// Get files link to the givent category
$app->get('/categories/{name}', function () {
   return null;
});

$app->run();