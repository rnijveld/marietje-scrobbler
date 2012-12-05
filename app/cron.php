<?php

define('APP_LOCATION', __DIR__);
define('ROOT_LOCATION', dirname(__DIR__));
require_once ROOT_LOCATION . '/vendor/autoload.php';

$app = new Marietje\Scrobbler\App();

$startTime = microtime(true);
$running = 0;

// clear listeners at 21:30
if (date('Hi') === '2130') {
    $app['listeners']->clear();
}

// keep running for a minute
while ($running < 60.0) {
    foreach ($app['locations'] as $val) {
        list($url, $key) = $val;

        // if there are no listeners there's no need
        if ($app['listeners']->listenerCount($key) > 0) {

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
                        list($checkin, $user) = $listener;

                        // if the track isn't ignored: scrobble it
                        if (!$app['ignores']->isIgnored($user, $latest['artist'], $latest['title'])) {
                            $app['lastfm']->setSession($session);
                            $result = $app['lastfm']->scrobble(
                                $latest['artist'],
                                $latest['title'],
                                $latest['start']
                            );

                            if ((string)$result->ignoredMessage->attributes()->code === '0') {
                                $app['scrobbles']->addScrobble(
                                    $user,
                                    (string)$result->artist,
                                    (string)$result->track,
                                    (int)$result->timestamp
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    $running = microtime(true) - $startTime;
    $step = $app['interval'] - fmod($running, $app['interval']);
    usleep($step * 1000000 + 1000); // a little extra margin
    $running = microtime(true) - $startTime;
}

$app['retrieved']->removeOld($app['keep_retrieved'] * 3600);
$app['scrobbles']->removeOld($app['keep_scrobbles'] * 3600);
