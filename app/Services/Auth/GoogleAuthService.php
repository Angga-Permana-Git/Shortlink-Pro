<?php

namespace App\Services\Auth;

use App\Dto\RequestContextDTO;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Sesi masuk dengan Google.
 *
 * Penyiapan akunnya ada di GoogleAccountService, jadi kelas ini hanya mengurus
 * pemasangan sesi dan pencatatan jejak masuk.
 */
class GoogleAuthService
{
    public function __construct(
        private readonly GoogleAccountService $accounts,
        private readonly LoginAuditRecorder $audit,
    ) {}

    public function handleSocialiteUser(SocialiteUser $socialUser, RequestContextDTO $context): User
    {
        $user = $this->accounts->provision($socialUser, $context);

        Auth::login($user);

        $this->audit->record($user->getKey(), $user->username, 'google', 'success', $context);
        AuditLogger::log($user, 'auth.login', 'user', $user->id, ['provider' => 'google']);

        return $user;
    }

    /**
     * Tombol Google hanya berguna bila OAuth Client sudah diisi.
     */
    public function isConfigured(): bool
    {
        return (bool) (config('services.google.client_id') && config('services.google.client_secret'));
    }
}
