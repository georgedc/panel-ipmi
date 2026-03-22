<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\KvmService;
use RuntimeException;

class KvmController extends Controller
{
    public function __construct(private ?KvmService $kvm = null)
    {
        $this->kvm ??= new KvmService();
    }

    public function open(Request $request)
    {
        try {
            $url = $this->kvm->authorizedLaunchUrl((int) $request->query('id', 0));
            return $this->redirect($url);
        } catch (RuntimeException $e) {
            return $this->view('errors/simple', [
                'title' => 'KVM',
                'heading' => 'KVM launch error',
                'message' => $e->getMessage(),
                'back_url' => routeUrl('/servers'),
            ], 403);
        }
    }
}
