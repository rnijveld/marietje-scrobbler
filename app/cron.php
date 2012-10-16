<?php

define('APP_LOCATION', __DIR__);
define('ROOT_LOCATION', dirname(__DIR__));
require_once ROOT_LOCATION . '/vendor/autoload.php';

$app = new Marietje\Scrobbler\App();

$startTime = microtime(true);
$running = 0;


while ($running < 60.0) {
    foreach ($app['locations'] as $val) {
        list($url, $key) = $val;

        // don't query database in a loop, but whatever
        $latest = $app['retrieved']->getLatest($key);

        // don't create objects in a loop, but whatever
        $retriever = new Marietje\Scrobbler\Retriever($url, $app);
        $track = $retriever->getNowPlaying();

        // check for changes
        if ($latest === false || $track === false || $latest['start'] !== $track['start']) {
            $listeners = $app['listeners']->getListeners($key);

            // if we really have a new track
            if ($track !== false) {
                $track = $app['lastfm']->updateTrackInfo($track);
                $app['retrieved']->insertTrack($track, $key);

                // update nowplaying for all listeners
                foreach ($listeners as $session => $listener) {
                    $app['lastfm']->setSession($session);
                    $app['lastfm']->setNowPlaying(
                        $track['artist'],
                        $track['title']
                    );
                }
            }

            // if we have an old track and it is scrobble-able
            if ($latest !== false && $app['lastfm']->hasScrobbleQuality($latest)) {
                foreach ($listeners as $session => $listener) {
                    // - when has someone checked in

                    // if the track isn't ignored: scrobble it
                    if (!$app['ignores']->isIgnored($latest, $listener)) {
                        $app['lastfm']->setSession($session);
                        $result = $app['lastfm']->scrobble(
                            $latest['artist'],
                            $latest['title'],
                            $latest['start']
                        );
                    }
                    // if result is positive: add to scrobbles
                }
            }
        }
    }

    $running = microtime(true) - $startTime;
    $step = $app['interval'] - fmod($running, $app['interval']);
    usleep($step * 1000000 + 1000); // a little extra margin
    $running = microtime(true) - $startTime;
}
