<?php

namespace Marietje\Scrobbler\Model;

use Doctrine\DBAL\Connection;

class Scrobbles
{
    private $db;

    public $table = 'scrobbles';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function addScrobble($user, $artist, $track, $timestamp)
    {
        $sql = "INSERT INTO {$this->table}(user, artist, title, start, sent) VALUES (?, ?, ?, ?, ?)";
        $this->db->executeUpdate($sql, [
            $user,
            $artist,
            $track,
            $timestamp,
            time()
        ]);
    }

    public function removeScrobble($user, $artist, $track, $timestamp)
    {
        $sql = "DELETE FROM {$this->table} WHERE user = ? AND artist = ? AND title = ? AND start = ?";
        $this->db->executeUpdate($sql, [
            $user,
            $artist,
            $track,
            $timestamp
        ]);
    }

    public function getScrobbles($user, $since = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE start > ?";
        return $this->db->fetchAll($sql, [$since]);
    }

    public function removeOld($time)
    {
        $sql = "DELETE FROM {$this->table} WHERE sent < ?";
        return $this->db->executeUpdate($sql, [time() - $time]);
    }
}
