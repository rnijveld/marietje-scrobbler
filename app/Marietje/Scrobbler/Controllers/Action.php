<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$action = $app['controllers_factory'];

/**
 * Switch from active to inactive
 */
$action->post('/switcheroo/{where}', function (Request $request, $where) use ($app) {
    // TODO: update user status for where he/she is
    return $app->redirect($app->path('home'));
})->bind('switch');

/**
 * Now playing
 */
$action->get('/nowplaying', function ($where) use ($app) {
    var_dump("Blurp");
})->bind('nowplaying');
return $action;
