<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$action = $app['controllers_factory'];

/**
 * Switch from active to inactive
 */
$action->post('/switcheroo/{where}', function (Request $request, $where) use ($app) {
    $user = $app['user'];
    $session = $app['sess'];
    if ($user === null || $session === null) {
        return $app->redirect($app->path('login'));
    } else {
        if ($app['listeners']->isListening($user)) {
            $listening = $app['listeners']->getListeningTo($user);
            $app['listeners']->removeListener($user);
            if ($listening !== $where) {
                $app['listeners']->addListener($user, $session, $where);
            }
        } else {
            $app['listeners']->addListener($user, $session, $where);
        }
        return $app->redirect($app->path('home'));
    }
})->bind('switch');

/**
 * Now playing
 */
$action->get('/update', function (Request $request) use ($app) {
    $zk = $app['retrieved']->getNowPlaying('zk');
    $nk = $app['retrieved']->getNowPlaying('nk');
    $scrobbles = $app['scrobbles']->getScrobbles($app['user'], (int)$request->get('since'));
    return $app->json([
        'nk' => $nk,
        'zk' => $zk,
        'scrobbles' => $scrobbles
    ]);
})->bind('update');

/**
 * Add to ignore list
 */
$action->post('/ignore', function (Request $request) use ($app) {
    $artist = $request->get('artist');
    $title = $request->get('title');
    if ($title === null) {
        $app['ignores']->addIgnoredArtist($app['user'], $artist);
    } else {
        $app['ignores']->addIgnoredTrack($app['user'], $artist, $title);
    }
})->bind('ignore');

/**
 * Remove from ignore list
 */
$action->post('/unignore', function (Request $request) use ($app) {
    $artist = $request->get('artist');
    $title = $request->get('title');
    if ($title === null) {
        $app['ignores']->removeIgnoredArtist($app['user'], $artist);
    } else {
        $app['ignores']->removeIgnoredTrack($app['user'], $artist, $title);
    }
})->bind('unignore');

/**
 * Get list of ignores for the user
 */
$action->get('/ignores', function (Request $request) use ($app) {
    return $app->json($app['ignores']->getIgnores($app['user']));
})->bind('ignores');

/**
 * Remove a scrobble
 */
$action->post('/unscrobble', function (Request $request) use ($app) {
    $artist = $request->get('artist');
    $track = $request->get('track');
    $timestamp = $request->get('timestamp');
    $app['lastfm']->removeScrobble($artist, $track, $timestamp);
    $app['scrobbles']->removeScrobble($app['user'], $artist, $track, $timestamp);
})->bind('delete');

return $action;
