<?php

namespace Marietje\Scrobbler\Model;

use Doctrine\DBAL\Connection;

class Listeners
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getListeners($where)
    {
        return array();
    }

    public function addListener($user, $session)
    {

    }

    public function removeListener($user)
    {

    }

    public function isListener($user)
    {

    }
}
