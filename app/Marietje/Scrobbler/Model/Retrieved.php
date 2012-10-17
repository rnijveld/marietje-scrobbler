<?php

namespace Marietje\Scrobbler\Model;

use Doctrine\DBAL\Connection;

class Retrieved
{
    const INTER_TIME = 60;

    private $db;

    public $table = 'retrieved';

    // id, artist, track, image, start, length, offset

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getLatest($where)
    {
        $sql = "SELECT *, max(start) as mx FROM retrieved WHERE location = ? GROUP BY location";
        $data = $this->db->fetchAssoc($sql, array($where));

        if (is_array($data)) {
            unset($data['mx']);
            $data['start'] = (int)$data['start'];
            $data['length'] = (int)$data['length'];
            $data['offset'] = (int)$data['offset'];
            return $data;
        } else {
            return false;
        }
    }

    public function getNowPlaying($where)
    {
        $latest = $this->getLatest($where);
        if ($latest === false) {
            return false;
        } else {
            if ($latest['start'] + $latest['offset'] + $latest['length'] + self::INTER_TIME >= time()) {
                return $latest;
            } else {
                return false;
            }
        }
    }

    public function insertTrack(array $track, $where)
    {
        $sql = "INSERT INTO {$this->table}(artist, title, image, start, length, location, offset) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $offset = time() - $track['serverTime'];
        return $this->db->executeUpdate($sql, [
            $track['artist'],
            $track['title'],
            $track['image'],
            $track['start'],
            $track['length'],
            $where,
            $offset
        ]);
    }

    public function removeOldRetrieves($where)
    {
        // TODO
    }
}
