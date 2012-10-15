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

    public function init()
    {
        // TODO: create tables
    }

    public function getLatest($where)
    {
        // TODO: do some sql shizzle here, or return a default array
    }

    public function insertTrack(array $track, $where)
    {
        // TODO: do some sql shizzle here
    }

    // returns the previous track if inserted, false otherwise
    public function ifNewInsertTrack(array $track, $where)
    {
        $previous = $this->getLatest($where);
        if ($previous['artist'] !== $track['artist'] || $previous['title'] !== $track['title'] || $previous['start'] !== $track['start']) {
            $this->insertTrack($track, $where);
            return $previous;
        } else {
            return false;
        }
    }
}
