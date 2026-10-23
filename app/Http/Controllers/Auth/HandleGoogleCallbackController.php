<?php

namespace App\Http\Controllers\Auth;

use App\Dto\RequestContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleAuthService;
use App\Services\Auth\LandingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * Menerima callback Google, lalu menyerahkan klaim pengguna ke GoogleAuthService.
 *
 * Penolakan (akun nonaktif, email belum terverifikasi, atau email milik
 * penyedia masuk lain) dikembalikan ke halaman masuk beserta pesannya supaya
 * pengguna tahu langkah berikutnya.
 */
class HandleGoogleCallbackController extends Controller
{
    public function __invoke(Request $request, GoogleAuthService $auth, LandingService $landing): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            abort(400, 'Sesi masuk Google tidak valid saat kembali dari Google.');
        } catch (\Throwable) {
            abort(503, 'Masuk dengan Google sedang tidak tersedia. Silakan coba lagi nanti.');
        }

        try {
            $user = $auth->handleSocialiteUser($socialUser, RequestContextDTO::fromRequest($request));
        } catch (AuthorizationException $exception) {
            return redirect()->route('login')->withErrors(['identifier' => $exception->getMessage()]);
        }

        return redirect()->to($landing->for($user));
    }
}
