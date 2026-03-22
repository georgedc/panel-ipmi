<?php

namespace App\Repositories;

class SettingsRepository extends Repository
{
    public function get(string $name, mixed $default = null): mixed
    {
        $row = $this->db->fetch('SELECT value FROM settings WHERE name = ?', [$name]);
        return $row['value'] ?? $default;
    }
}
