<?php

namespace App\Core;

abstract class Repository
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
