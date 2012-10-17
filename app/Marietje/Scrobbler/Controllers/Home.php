<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$home = $app['controllers_factory'];

/**
 * Intro page
 */
$home->get('/', function () use ($app) {
    return $app->render('index.twig');
})->bind('index');

/**
 * Homepage
 */
$home->get('/home', function () use ($app) {
    $user = $app['user'];
    if ($user === null) {
        return $app->redirect($app->path('login'));
    } else {
        return $app->render('home.twig', [
            'user' => $user,
            'details' => $app['session']->get('user_details'),
            'nk' => $app['listeners']->isListeningTo($user, 'nk'),
            'zk' => $app['listeners']->isListeningTo($user, 'zk')
        ]);
    }
})->bind('home');


/**
 * Get list of ignores for the user
 */
$home->get('/ignores', function (Request $request) use ($app) {
    return $app->render('ignores.twig', [
        'ignores' => $app['ignores']->getIgnores($app['user'])
    ]);
})->bind('ignores');

return $home;
