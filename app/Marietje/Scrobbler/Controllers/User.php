<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$user = $app['controllers_factory'];

/**
 * Login
 */
$user->get('/login', function () use ($app) {
    $app['lastfm']->sendAuthToken($app->url('auth'));
})->bind('login');

/**
 * Logout
 */
$user->get('/logout', function () use ($app) {
    $app['session']->clear();
    return $app->redirect($app->path('index'));
})->bind('logout');

/**
 * Auth request
 */
$user->get('/auth', function (Request $request) use ($app) {
    $token = $request->get('token');
    if ($token === null) {
        $app->abort(500, "Token was not set.");
    }
    $session = $app['lastfm']->getSession($token);
    $app['lastfm']->setSession($session['key']);
    $app['session']->set('user_details', $app['lastfm']->getUserInfo());
    if (!$session) {
        $app->abort(500, "Invalid token.");
    } else {
        $app['session']->set(Marietje\Scrobbler\App::LASTFM_SESSION, $session);
        return $app->redirect($app->path('home'));
    }
})->bind('auth');

return $user;
