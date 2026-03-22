<?php

namespace App\Controllers;

use App\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if ($this->auth()->isLoggedIn()) {
            return $this->redirect(routeUrl('/dashboard'));
        }

        return $this->redirect(routeUrl('/login'));
    }
}
