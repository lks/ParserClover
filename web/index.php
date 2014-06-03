<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../Service/MetricService.php';
require_once __DIR__.'/../Service/ParserService.php';
require_once __DIR__.'/../Utility/CouchDbWrapper.php';

use Service\MetricService;
use Service\ParserService;
use Utility\CouchDbWrapper;


$app = new Silex\Application();

$app['CouchDbWrapper'] =$app->share(function () {
	return new CouchDbWrapper();
});

$app['ParserService'] = $app->share(function ($app) {
	return new ParserService($app['CouchDbWrapper']);
});

$app['MetricService'] = $app->share(function ($app) {
	return new MetricService($app['ParserService']);
});

$metricService = $app['MetricService'];

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../log/development.log',
));

$app->get('/load', function() use($metricService) {
	try {
		return $metricService->load();
	} catch (Exception $e) {
		return $e->getMessage();
	}
		

});

// Get files link to the givent category
$app->get('/categoryries/{name}', function () {
    $output = '';
    $test = new GroupMetric();
    foreach ($blogPosts as $post) {
        $output .= $post['title'];
        $output .= '<br />';
    }

    return $output;
});

$app->run();