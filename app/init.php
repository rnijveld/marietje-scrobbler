<?php

require_once __DIR__ . '/../vendor/autoload.php';
define('APP_LOCATION', __DIR__);
define('ROOT_LOCATION', dirname(__DIR__));

$app = new Marietje\Scrobbler\App();

$app->get('/', function () use ($app) {
    var_dump("Got here!");
});

$app->run();
