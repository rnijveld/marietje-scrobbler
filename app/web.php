<?php

define('APP_LOCATION', __DIR__);
require_once ROOT_LOCATION . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Marietje\Scrobbler\App();

$app->mount('/', $app->controller('home'));
$app->mount('/', $app->controller('user'));
$app->mount('/', $app->controller('action'));

$app->run();
