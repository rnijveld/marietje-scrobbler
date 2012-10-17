<?php

namespace Marietje\Scrobbler;

use Guzzle\Service\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\CurlException;
use SimpleXMLElement;

/**
 * Provides access to the last.fm API
 */
class Lastfm
{
    /**
     * Guzzle HTTP client
     * @var \Guzzle\Service\Client
     */
    private $client;

    /**
     * Last.fm app key
     * @var string
     */
    private $key;

    /**
     * Last.fm app secret
     * @var string
     */
    private $secret;

    /**
     * Session key of the currently authenticated user
     * @var string
     */
    private $session;

    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->session = null;
        $this->client = new Client('http://ws.audioscrobbler.com/2.0/');
    }

    /**
     * Creates a signature for the given data
     * @param  array  $data
     * @return string
     */
    private function createSignature(array $data)
    {
        ksort($data);
        $string = '';
        foreach ($data as $key => $value) {
            $string .= $key . $value;
        }
        $string .= $this->secret;
        return hash('md5', $string);
    }

    /**
     * Sends an authenticated request.
     * Authenticated requests add the api_key and add a api_sig.
     * If sendSession is set to true, then the session key is also added if it exists.
     * @param  string  $apiMethod   API method to call
     * @param  array   $data        Array of parameters
     * @param  string  $method      get or post HTTP request
     * @param  boolean $sendSession Whether or not to add the session key
     * @return \SimpleXMLElement    The response XML
     */
    private function sendAuthenticatedRequest($apiMethod, array $data = [], $method = 'get', $sendSession = true)
    {
        $data['method'] = $apiMethod;
        $data['api_key'] = $this->key;
        if ($sendSession && $this->session) {
            $data['sk'] = $this->session;
        }
        $data['api_sig'] = $this->createSignature($data);
        return $this->sendRequest($data, $method);
    }

    /**
     * Sends an unauthenticated request.
     * Only adds the api key, no signature or session keys are added.
     * @param  string $apiMethod API method to call
     * @param  array  $data      Array of parameters
     * @param  string $method    get or post HTTP request
     * @return \SimpleXMLElement The response XML
     */
    private function sendUnauthenticatedRequest($apiMethod, array $data = [], $method = 'get')
    {
        $data['method'] = $apiMethod;
        $data['api_key'] = $this->key;
        return $this->sendRequest($data, $method);
    }

    /**
     * Actually sends a request to the last.fm API
     * @param  array $data       The data to be submitted to the server
     * @param  string $method    Either a get or post HTTP request
     * @return \SimpleXMLElement The response XML
     */
    private function sendRequest(array $data = [], $method = 'get')
    {
        if ($method === 'post') {
            $request = $this->client->post('');
            $request->addPostFields($data);
        } else {
            $request = $this->client->get('');
            $request->getQuery()->merge($data);
        }

        try {
            $response = $request->send();
        } catch (BadResponseException $e) {
            return simplexml_load_string((string)$e->getResponse()->getBody());
        } catch (CurlException $e) {
            return null;
        }
        return simplexml_load_string((string)$response->getBody());
    }

    /**
     * Send the user to a location so that a token may be generated for logging in.
     * @param  string $to Location that the user should be redirected to after logging in.
     * @return void
     */
    public function sendAuthToken($to)
    {
        header(sprintf(
            'Location: http://www.last.fm/api/auth/?api_key=%s&cb=%s',
            urlencode($this->key),
            urlencode($to)
        ));
        exit;
    }

    /**
     * Retrieve a session key given a token
     * @param  string $token The token retrieved from the public request
     * @return array|boolean Name, session key and subscriber status. False if an error occured.
     */
    public function getSession($token)
    {
        $data = ['token' => $token];
        $result = $this->sendAuthenticatedRequest('auth.getSession', $data, 'get', false);
        if ($result !== null && (string)$result->attributes()->status === 'ok') {
            return $this->toArray($result->session);
        }
        return false;
    }

    /**
     * Update the session parameter.
     * @param  string $sessionKey
     * @return void
     */
    public function setSession($sessionKey)
    {
        $this->session = $sessionKey;
    }

    /**
     * Get detailed user information.
     * If no username is given, details of the authenticated user are retrieved.
     * @param  string $username Name of the user
     * @return array|boolean    Detailed information of the user requested.
     */
    public function getUserInfo($username = null)
    {
        if ($username === null) {
            $result = $this->sendAuthenticatedRequest('user.getInfo', []);
        } else {
            $result = $this->sendUnauthenticatedRequest('user.getInfo', ['username' => $username]);
        }

        if ($result && (string)$result->attributes()->status === 'ok') {
            return $this->toArray($result->user);
        }
        return false;
    }

    /**
     * Retrieve information about a track.
     * @param  string  $artist           Name of the artist
     * @param  string  $track            Name of the track
     * @param  boolean $autocorrect      Whether or not to autocorrect spelling mistakes (etc)
     * @return \SimpleXMLElement|boolean XML representing the track or false if an error occured.
     */
    public function getTrackInfo($artist, $track, $autocorrect = true)
    {
        $data = [
            'track' => $track,
            'artist' => $artist,
            'autocorrect' => $autocorrect ? '1' : '0'
        ];
        $result = $this->sendUnauthenticatedRequest('track.getInfo', $data);
        if ($result && (string)$result->attributes()->status === 'ok') {
            return $result->track;
        }
        return false;
    }

    public function scrobble($artist, $track, $timestamp)
    {
        $data = [
            'artist[0]' => $artist,
            'track[0]' => $track,
            'timestamp[0]' => $timestamp
        ];
        $result = $this->sendAuthenticatedRequest('track.scrobble', $data, 'post');
        if ($result && (string)$result->attributes()->status === 'ok') {
            return $result->scrobbles->scrobble[0];
        }
        return false;
    }

    public function setNowPlaying($artist, $track)
    {
        $data = [
            'artist' => $artist,
            'track' => $track
        ];
        $result = $this->sendAuthenticatedRequest('track.updateNowPlaying', $data, 'post');
        if ($result && (string)$result->attributes()->status === 'ok') {
            return $result->nowplaying;
        }
        return false;
    }

    public function removeScrobble($artist, $track, $timestamp)
    {
        $data = [
            'artist' => $artist,
            'track' => $track,
            'timestamp' => $timestamp
        ];
        $result = $this->sendAuthenticatedRequest('library.removeScrobble', $data, 'post');
        if ($result && (string)$result->attributes()->status === 'ok') {
            // TODO: find out how this response looks like
            return $result;
        }
        return false;
    }

    private function toArray(SimpleXMLElement $elem)
    {
        $data = [];
        foreach ($elem->children() as $key => $child) {
            if (count($child->children()) > 0) {
                $result = $this->toArray($child);
            } else {
                $result = (string)$child;
            }

            if (!isset($data[$key])) {
                $data[$key] = [$result];
            } else {
                $data[$key][] = $result;
            }
        }

        $result = [];
        foreach ($data as $key => $item) {
            if (count($item) === 1) {
                $result[$key] = $item[0];
            } else {
                $result[$key] = $item;
            }
        }
        return $result;
    }

    public function hasScrobbleQuality(array $track)
    {
        // TODO: determine if a track should be scrobbled at this time
        // - when was the previous track played?
        // - has the track fully played or not
        return true;
    }

    public function updateTrackInfo(array $track)
    {
        $info = $this->getTrackInfo($track['artist'], $track['title']);
        $images = $info->album->image;
        if ($images !== null) {
            foreach ($images as $img) {
                if ((string)$img->attributes()->size === 'extralarge') {
                    $track['image'] = (string)$img;
                    break;
                }
            }
        }

        if (!isset($track['image'])) {
            $track['image'] = null;
        }
        return $track;
    }
}
