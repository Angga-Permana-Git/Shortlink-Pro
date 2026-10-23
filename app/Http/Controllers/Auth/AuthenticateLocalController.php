<?php

namespace App\Http\Controllers\Auth;

use App\Dto\RequestContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Auth\LandingService;
use App\Services\Auth\LocalAuthService;
use App\Support\Enums\LoginOutcome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Memproses login darurat akun lokal.
 *
 * Batas percobaan dan pencatatan jejak masuk ada di LocalAuthService; di sini
 * hanya validasi bentuk input dan penerjemahan hasilnya menjadi respons.
 */
class AuthenticateLocalController extends Controller
{
    public function __invoke(Request $request, LocalAuthService $auth, LandingService $landing): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $outcome = $auth->attempt(
            (string) $request->input('username'),
            (string) $request->input('password'),
            RequestContextDTO::fromRequest($request),
        );

        if ($outcome !== LoginOutcome::Success) {
            return back()
                ->withErrors(['username' => $this->message($outcome)])
                ->onlyInput('username');
        }

        return redirect()->intended($landing->for(auth()->user()));
    }

    private function message(LoginOutcome $outcome): string
    {
        return match ($outcome) {
            LoginOutcome::TooManyAttempts => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.',
            default => 'Kredensial tidak dikenali atau akun tidak aktif.',
        };
    }
}
