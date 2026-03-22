<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\BmcProxyService;
use App\Services\KvmLaunchService;

class RuntimeController extends Controller
{
    public function __construct(
        private ?KvmLaunchService $kvmRuntime = null,
        private ?BmcProxyService $bmcProxy = null
    ) {
        $this->kvmRuntime ??= new KvmLaunchService();
        $this->bmcProxy ??= new BmcProxyService();
    }

    public function ipmiKvm(Request $request): Response
    {
        return $this->kvmRuntime->handle((int) $request->query('id', 0));
    }

    public function bmcProxy(Request $request): Response
    {
        return $this->bmcProxy->handle();
    }
}
