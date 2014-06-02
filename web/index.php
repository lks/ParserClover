<?php

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Service', __DIR__.'/../Service');
$loader->add('Entity', __DIR__.'/../Entity');
$loader->add('Excpetion', __DIR__.'/../Exception');
$loader->add('Utility', __DIR__.'/../Utility');

use Service\MetricService;

$app = new Silex\Application();

$app['CouchDbWrapper'] = $app->share(function () {
	return new CouchDbWrapper();
});

$app['ParserService'] = $app->share(function () {
	return new ParserService($app['CouchDbWrapper']);
});

$app['MetricService'] = $app->share(function () {
	return new MetricService($app['ParserService'], $app['CouchDbWrapper']);
});

$metricService = $app['MetricService'];

$app->get('/load', function ($metricService) {

	return "res";

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