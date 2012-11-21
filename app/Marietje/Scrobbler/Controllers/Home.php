<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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

/**
 * Post to add ignores
 */
$home->post('/ignores', function (Request $request) use ($app) {
    $subRequest = Request::create($app->path('ignore'), 'POST', $request->request->all());
    $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

    return $app->redirect($app->path('ignores'));
})->bind('ignores_post');

return $home;
