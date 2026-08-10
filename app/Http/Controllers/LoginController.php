<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->to($this->homePath());
        }

        return view('auth.login');
    }

    private function homePath(): string
    {
        return auth()->user()?->isAdmin() ? '/admin' : '/user';
    }
}