<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\LocalAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LocalAuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->to($this->homeUrl());
        }

        return view('auth.login-local');
    }

    public function login(Request $request, LocalAuthService $auth): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $key = 'local-login:'.(strtolower((string) $request->input('username')));

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'username' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.',
            ])->onlyInput('username');
        }

        if (! $auth->attemptLocal(
            (string) $request->input('username'),
            (string) $request->input('password'),
        )) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'username' => 'Kredensial tidak dikenali atau akun tidak aktif.',
            ])->onlyInput('username');
        }

        RateLimiter::clear($key);

        return redirect()->intended($this->homeUrl());
    }

    private function homeUrl(): string
    {
        return auth()->user()?->isAdmin() ? '/admin' : '/user';
    }
}