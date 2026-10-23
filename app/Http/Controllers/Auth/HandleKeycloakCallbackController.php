<?php

namespace App\Http\Controllers\Auth;

use App\Dto\RequestContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Auth\KeycloakAuthService;
use App\Services\Auth\LandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * Menerima callback Keycloak, memeriksa state sesi, lalu menyerahkan klaim
 * pengguna ke KeycloakAuthService.
 */
class HandleKeycloakCallbackController extends Controller
{
    public function __invoke(Request $request, KeycloakAuthService $auth, LandingService $landing): RedirectResponse
    {
        $state = (string) $request->query('state');

        if ($state !== (string) $request->session()->pull('keycloak_state')) {
            return redirect('/')->withErrors('Sesi masuk tidak valid atau sudah kedaluwarsa. Silakan coba lagi.');
        }

        try {
            $socialUser = Socialite::driver('keycloak')->user();
        } catch (InvalidStateException) {
            abort(400, 'Sesi SSO tidak valid saat kembali dari Keycloak.');
        } catch (\Throwable) {
            abort(503, 'SSO sedang tidak tersedia. Silakan coba lagi nanti.');
        }

        $user = $auth->handleSocialiteUser($socialUser, RequestContextDTO::fromRequest($request));

        return redirect()->to($landing->for($user));
    }
}
