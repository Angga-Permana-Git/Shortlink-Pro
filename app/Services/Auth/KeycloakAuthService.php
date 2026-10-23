<?php

namespace App\Services\Auth;

use App\Dto\RequestContextDTO;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Sesi masuk dan keluar untuk autentikasi Keycloak.
 *
 * Penyiapan akunnya ada di KeycloakAccountService. Penolakan login dilempar
 * sebagai AuthorizationException supaya service ini tidak tahu soal status HTTP
 * (disiplin_struktur.md bagian 1).
 */
class KeycloakAuthService
{
    public function __construct(
        private readonly KeycloakAccountService $accounts,
        private readonly LoginAuditRecorder $audit,
    ) {}

    public function handleSocialiteUser(SocialiteUser $socialUser, RequestContextDTO $context): User
    {
        $user = $this->accounts->provision($socialUser, $context);

        Auth::login($user);

        $this->audit->record($user->getKey(), $user->username, 'keycloak', 'success', $context);
        AuditLogger::log($user, 'auth.login', 'user', $user->id, ['provider' => 'keycloak']);

        return $user;
    }

    public function logout(RequestContextDTO $context): void
    {
        $user = Auth::user();

        Auth::logout();

        if ($user) {
            AuditLogger::log($user, 'auth.logout', 'user', $user->id, ['provider' => 'keycloak']);
            $this->audit->record($user->getKey(), $user->username, 'keycloak', 'success', $context);
        }
    }

    public function isConfigured(): bool
    {
        return (bool) (config('services.keycloak.base_url') && config('services.keycloak.realms'));
    }

    /**
     * Alamat keluar dari Keycloak, null bila SSO belum dikonfigurasi.
     */
    public function logoutUrl(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $redirectUri = config('services.keycloak.redirect') ?? url('/');

        return Socialite::driver('keycloak')->getLogoutUrl($redirectUri, config('services.keycloak.client_id'));
    }
}
