<?php

namespace Marietje\Scrobbler;

use Guzzle\Service\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\CurlException;
use SimpleXMLElement;

class Retriever
{
    private $client;

    public function __construct($url)
    {
        $this->client = new Client($url);
    }

    public function getNowPlaying()
    {
        try {
            $response = $this->client->get('')->send();
            libxml_use_internal_errors();
            $xml = simplexml_load_string((string)$response->getBody());

            if ($xml !== false) {
                $attrs = $xml->attributes();

                $data = [];
                $data['artist'] = (string)$attrs->artist;
                $data['title'] = (string)$attrs->title;
                $data['length'] = (int)$attrs->length;
                $data['endTime'] = (int)$attrs->endTime;
                $data['serverTime'] = (int)$attrs->serverTime;
                $data['media'] = (int)$attrs->media;
                $data['start'] = $data['endTime'] - $data['length'];
                return $data;
            } else {
                return false;
            }
        } catch (BadResponseException $e) {
            return false;
        } catch (CurlException $e) {
            return false;
        }
    }
}
