<?php

define('APP_LOCATION', __DIR__);
define('ROOT_LOCATION', dirname(__DIR__));
require_once ROOT_LOCATION . '/vendor/autoload.php';

$app = new Marietje\Scrobbler\App();
// TODO
