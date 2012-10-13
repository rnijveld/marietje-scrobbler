<?php

if (php_sapi_name() === 'cli-server') {
    $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (is_file($filename)) {
        return false;
    }
}

define('ROOT_LOCATION', dirname(__DIR__));

require_once ROOT_LOCATION . '/app/web.php';
