<?php

namespace Marietje\Scrobbler\Model;

use Doctrine\DBAL\Connection;

class Retrieved
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getLatest($where)
    {
        // TODO: do some sql shizzle here, or return false if none was found
        return false;
    }

    public function insertTrack(array $track, $where)
    {
        // TODO: do some sql shizzle here
    }
}
