<?php

namespace Marietje\Scrobbler\Model;

use Doctrine\DBAL\Connection;

class Listeners
{
    private $db;

    public $table = 'listeners';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getListeners($where)
    {
        // array of session => [checkin-time, user]
        $sql = "SELECT started, user, session FROM {$this->table} WHERE location = ?";
        $data = $this->db->fetchAll($sql, [$where]);
        if (!is_array($data)) {
            return [];
        } else {
            $results = [];
            foreach ($data as $row) {
                $results[$row['session']] = [(int)$row['started'], $row['user']];
            }
            return $results;
        }
    }

    public function addListener($user, $session, $where)
    {
        $sql = "INSERT INTO {$this->table}(user, session, location, started) VALUES (?, ?, ?, ?)";
        $this->db->executeUpdate($sql, [$user, $session, $where, time()]);
    }

    public function removeListener($user)
    {
        $sql = "DELETE FROM {$this->table} WHERE user = ?";
        return $this->db->executeUpdate($sql, [$user]);
    }

    public function isListening($user)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user = ?";
        $data = $this->db->fetchAssoc($sql, [$user]);
        return $data !== false;
    }

    public function isListeningTo($user, $where)
    {
        return $this->getListeningTo($user) === $where;
    }

    public function getListeningTo($user)
    {
        $sql = "SELECT location FROM {$this->table} WHERE user = ?";
        $data = $this->db->fetchAssoc($sql, [$user]);
        if (is_array($data)) {
            return $data['location'];
        } else {
            return false;
        }
    }

    public function listenerCount($where)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE location = ?";
        return $this->db->fetchColumn($sql, [$where]);
    }

    public function clear()
    {
        $sql = "DELETE FROM {$this->table}";
        return $this->db->executeUpdate($sql);
    }
}
