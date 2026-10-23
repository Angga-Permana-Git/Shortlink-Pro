<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

/**
 * Mengantar tamu ke halaman persetujuan Google.
 */
class RedirectToGoogleController extends Controller
{
    public function __invoke(GoogleAuthService $auth): RedirectResponse
    {
        if (! $auth->isConfigured()) {
            return redirect()->route('login')->withErrors([
                'identifier' => 'Masuk dengan Google belum dikonfigurasi.',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }
}
