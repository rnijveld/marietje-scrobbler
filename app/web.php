<?php

define('APP_LOCATION', __DIR__);
require_once ROOT_LOCATION . '/vendor/autoload.php';

$app = new Marietje\Scrobbler\App();

$app->get('/', function () use ($app) {
    $user = $app['session']->get('user');
    if ($user === null) {
        $app->redirect('/login');
    }
});

$app->get('/login', function () use ($app) {

});

$app->run();
