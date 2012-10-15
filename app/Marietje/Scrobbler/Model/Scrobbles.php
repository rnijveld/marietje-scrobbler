<?php

namespace Marietje\Scrobbler\Model;

use Doctrine\DBAL\Connection;

class Scrobbles
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function addScrobble($user, $artist, $track, $timestamp)
    {

    }

    public function getScrobbles($user)
    {

    }

    public function removeOldScrobbles($user)
    {

    }
}
