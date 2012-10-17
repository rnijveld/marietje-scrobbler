<?php

namespace Marietje\Scrobbler\Model;

use Doctrine\DBAL\Connection;

class Ignores
{
    private $db;

    public $table = 'ignores';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function isIgnored($user, $artist, $track)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user = :user AND (
            (artist = :artist AND title IS NULL)
            OR
            (artist = :artist AND title = :title))";

        return 0 !== (int)$this->db->fetchColumn($sql, [
            'user' => $user,
            'artist' => $artist,
            'title' => $track
        ]);
    }

    public function getIgnores($user)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user = ?";
        return $this->db->fetchAll($sql, [$user]);
    }

    public function addIgnoredTrack($user, $artist, $track)
    {
        $sql = "INSERT INTO {$this->table}(user, artist, title) VALUES (?, ?, ?)";
        return $this->db->executeUpdate($sql, [$user, $artist, $track]);
    }

    public function addIgnoredArtist($user, $artist)
    {
        $sql = "INSERT INTO {$this->table}(user, artist) VALUES (?, ?)";
        return $this->db->executeUpdate($sql, [$user, $artist]);
    }

    public function removeIgnoredTrack($user, $artist, $track)
    {
        $sql = "DELETE FROM {$this->table} WHERE user = ? AND artist = ? AND title = ?";
        return $this->db->executeUpdate($sql, [$user, $artist, $track]);
    }

    public function removeIgnoredArtist($user, $artist)
    {
        $sql = "DELETE FROM {$this->table} WHERE user = ? AND artist = ? AND title IS NULL";
        return $this->db->executeUpdate($sql, [$user, $artist]);
    }
}
