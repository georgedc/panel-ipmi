<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\SsoKvmService;
use RuntimeException;

class SsoKvmController extends Controller
{
    public function __construct(private ?SsoKvmService $service = null)
    {
        $this->service ??= new SsoKvmService();
    }

    public function open(Request $request)
    {
        try {
            return $this->redirect($this->service->consumeLink($request->all()));
        } catch (RuntimeException $e) {
            return $this->view('errors/simple', [
                'title' => 'SSO',
                'heading' => 'SSO access error',
                'message' => $e->getMessage(),
                'back_url' => routeUrl('/login'),
            ], 403);
        }
    }
}
