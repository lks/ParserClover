<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../Service/MetricService.php';
require_once __DIR__.'/../Service/ParserService.php';
require_once __DIR__.'/../Utility/CouchDbWrapper.php';

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