<?php

namespace App\Services\Auth;

use App\Dto\RequestContextDTO;
use App\Services\Audit\AuditLogger;
use App\Support\Enums\LoginOutcome;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Masuk darurat dengan akun lokal, termasuk pemasangan sesi dan pembatasan
 * percobaan.
 *
 * Pemeriksaan kredensialnya ada di LocalAccountVerifier, jadi kelas ini hanya
 * mengurus alur masuk dan menerjemahkan hasilnya menjadi LoginOutcome.
 */
class LocalAuthService
{
    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 60;

    public function __construct(
        private readonly LocalAccountVerifier $verifier,
        private readonly LoginAuditRecorder $audit,
    ) {}

    public function attempt(string $identifier, string $password, RequestContextDTO $context): LoginOutcome
    {
        $key = $this->attemptKey($identifier);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return LoginOutcome::TooManyAttempts;
        }

        $user = $this->verifier->verify($identifier, $password, $context);

        if (! $user) {
            RateLimiter::hit($key, self::LOCK_SECONDS);

            return LoginOutcome::InvalidCredentials;
        }

        RateLimiter::clear($key);

        Auth::login($user);

        $user->forceFill(['last_login_at' => now()])->save();

        $this->audit->record($user->getKey(), $identifier, 'local', 'success', $context);
        AuditLogger::log($user, 'auth.login_local', 'user', $user->id);

        return LoginOutcome::Success;
    }

    private function attemptKey(string $identifier): string
    {
        return 'local-login:'.strtolower(trim($identifier));
    }
}
