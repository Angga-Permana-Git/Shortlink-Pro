<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\KeycloakAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * Mengantar tamu ke halaman login Keycloak.
 */
class RedirectToKeycloakController extends Controller
{
    public function __invoke(Request $request, KeycloakAuthService $auth): RedirectResponse
    {
        if (! $auth->isConfigured()) {
            return redirect()->route('login')->withErrors([
                'identifier' => 'SSO belum dikonfigurasi. Gunakan login darurat.',
            ]);
        }

        $request->session()->put('keycloak_state', uniqid('', true));

        return Socialite::driver('keycloak')->redirect();
    }
}
