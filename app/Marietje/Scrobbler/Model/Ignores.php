<?php

namespace Marietje\Scrobbler\Model;

use Doctrine\DBAL\Connection;

class Ignores
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function isIgnored($track, $user)
    {
        return false;
    }

    public function addIgnoredTrack($artist, $track)
    {

    }

    public function addIgnoredArtist($artist)
    {

    }
}
