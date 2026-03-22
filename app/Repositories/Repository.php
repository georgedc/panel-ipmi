<?php

namespace App\Repositories;

use App\Database\Database;

abstract class Repository
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
