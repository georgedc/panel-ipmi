<?php

namespace App\Services;

use App\Http\Response;

class BmcProxyService
{
    public function handle(): Response
    {
        BmcProxyBootstrap::load();
        return (new BmcProxyPayload())->handle($_GET, $_SERVER);
    }
}
