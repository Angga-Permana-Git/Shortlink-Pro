<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\KeycloakAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class KeycloakAuthController extends Controller
{
    public function redirect(Request $request, KeycloakAuthService $auth): RedirectResponse
    {
        if (! $auth->isConfigured()) {
            return redirect()->route('login')->withErrors([
                'identifier' => 'SSO belum dikonfigurasi. Gunakan login darurat.',
            ]);
        }

        $request->session()->put('keycloak_state', uniqid('', true));

        return Socialite::driver('keycloak')->redirect();
    }

    public function callback(Request $request, KeycloakAuthService $auth): RedirectResponse
    {
        if ((string) $request->query('state') !== (string) $request->session()->pull('keycloak_state')) {
            return redirect('/')->withErrors('State Mismatch: Sesi tidak valid atau kedaluwarsa.');
        }

        try {
            $socialUser = Socialite::driver('keycloak')->user();
        } catch (InvalidStateException $e) {
            abort(400, 'State tidak valid saat callback.');
        } catch (\Throwable $e) {
            abort(503, 'SSO sedang tidak tersedia. Silakan coba lagi nanti.');
        }

        $user = $auth->handleSocialiteUser($socialUser);

        return redirect()->to($user->isAdmin() ? '/admin' : '/user');
    }

    public function logout(Request $request, KeycloakAuthService $auth): RedirectResponse
    {
        $auth->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (! $auth->isConfigured()) {
            return redirect()->route('login');
        }

        $redirectUri = config('services.keycloak.redirect') ?? url('/');
        $clientId = config('services.keycloak.client_id');

        return redirect()->away(\Socialite::driver('keycloak')->getLogoutUrl($redirectUri, $clientId));
    }
}