<?php

define('APP_LOCATION', __DIR__);
define('ROOT_LOCATION', dirname(__DIR__));
require_once ROOT_LOCATION . '/vendor/autoload.php';

$app = new Marietje\Scrobbler\App();

$locations = [
    'noord' => [
        'http://noordslet.science.ru.nl:8080/playing',
        'nk'
    ],
    'zuid' => [
        'http://zuidslet.science.ru.nl:8080/playing',
        'zk'
    ],
];

foreach ($locations as $name => $val) {
    list($url, $key) = $val;
    $retriever = new Marietje\Scrobbler\Retriever();
    $latest = $retriever->getNowPlaying();

    $previous = false;
    if ($latest !== false) {
        $previous = $app['retriever']->ifNewInsertTrack($latest, $key);
    }

    if ($previous !== false) {
        $listeners = $app['listeners']->getListeners($key);
        foreach ($listeners as $listener) {
            // Check if track is not in the ignore list for this listener
            // if it is not: send as scrobble and add to scrobbled tracks if accepted
        }
    }
}
