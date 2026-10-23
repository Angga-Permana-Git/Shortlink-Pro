<?php

namespace App\Http\Controllers;

use App\Services\Auth\LandingService;
use App\Services\Setting\AccessModeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman yang sesuai dengan mode akses yang sedang berlaku:
     * beranda publik saat wajib SSO nonaktif, halaman masuk Keycloak saat aktif.
     * Route `/login` selalu menuju jalur masuk yang berlaku.
     */
    public function show(Request $request, AccessModeService $accessMode, LandingService $landing): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->to($landing->for(auth()->user()));
        }

        if ($accessMode->forceSso()) {
            return view('auth.login');
        }

        return $request->routeIs('login')
            ? redirect()->route('login.local')
            : view('publik.home');
    }
}
