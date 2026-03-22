<?php

namespace App\Repositories;

class IsoRepository extends Repository
{
    public function active(): array
    {
        return $this->db->fetchAll('SELECT * FROM iso_images WHERE is_active = 1 ORDER BY name');
    }

    public function find(int $isoId): ?array
    {
        return $this->db->fetch('SELECT * FROM iso_images WHERE id = ?', [$isoId]);
    }
}
