<?php

namespace App\Services;

use App\Repositories\IsoRepository;

class IsoCatalogService
{
    public function __construct(private ?IsoRepository $isos = null)
    {
        $this->isos ??= new IsoRepository();
    }

    public function activeCatalog(): array
    {
        return $this->isos->active();
    }
}
