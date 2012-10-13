<?php

define('APP_LOCATION', __DIR__);
require_once ROOT_LOCATION . '/vendor/autoload.php';

$app = new Marietje\Scrobbler\App();

$app->get('/', function () use ($app) {
    var_dump("Got here!");
});

$app->run();
