<?php

namespace App\Http\Controllers\Auth;

use App\Dto\RequestContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Auth\KeycloakAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Menutup sesi pengguna, lalu ikut menutup sesi SSO bila Keycloak dipakai.
 */
class LogoutController extends Controller
{
    public function __invoke(Request $request, KeycloakAuthService $auth): RedirectResponse
    {
        $auth->logout(RequestContextDTO::fromRequest($request));

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $logoutUrl = $auth->logoutUrl();

        return $logoutUrl ? redirect()->away($logoutUrl) : redirect()->route('login');
    }
}
