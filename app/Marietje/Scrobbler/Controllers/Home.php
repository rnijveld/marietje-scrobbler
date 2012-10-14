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
        $userData = $app['lastfm']->getTrackInfo('Foo Fighters', 'Everlong');
        var_dump($userData);
        return $app->render('home.twig', [
            'user' => $user
        ]);
    }
})->bind('home');

return $home;
