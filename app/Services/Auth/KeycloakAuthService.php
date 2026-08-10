<?php

namespace App\Services\Auth;

use App\Models\LoginAuditLog;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Two\User as SocialiteUser;

class KeycloakAuthService
{
    private const RESERVED_USERNAMES = ['root', 'admin', 'superadmin', 'administrator'];

    /**
     * Provision a user from Keycloak claims and log them in.
     */
    public function handleSocialiteUser(SocialiteUser $socialUser): User
    {
        $subject = (string) $socialUser->getId();
        $username = strtolower(trim((string) ($socialUser->getNickname() ?? '')));
        $name = $socialUser->getName() ?? $username;
        $email = $socialUser->getEmail();

        if ($username === '' || in_array($username, self::RESERVED_USERNAMES, true)) {
            $this->record(null, $subject, 'keycloak', 'failed');
            AuditLogger::log(null, 'auth.login_failed', 'user', null, [
                'provider' => 'keycloak',
                'reason' => 'Reserved or empty username',
            ]);

            abort(403, 'Login SSO ditolak karena username tidak valid.');
        }

        $user = User::query()
            ->where('keycloak_sub', $subject)
            ->first();

        if ($user) {
            if (! $user->isActive()) {
                $this->record($user, $username, 'keycloak', 'failed');

                abort(403, 'Akun Anda dinonaktifkan.');
            }

            $user->forceFill([
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'auth_provider' => 'keycloak',
                'last_login_at' => now(),
            ])->save();
        } else {
            $user = User::create([
                'keycloak_sub' => $subject,
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'auth_provider' => 'keycloak',
                'role' => 'user',
                'is_active' => true,
                'is_protected' => false,
                'last_login_at' => now(),
            ]);
        }

        Auth::login($user);

        $this->record($user->getKey(), $username, 'keycloak', 'success');
        AuditLogger::log($user, 'auth.login', 'user', $user->id, ['provider' => 'keycloak']);

        return $user;
    }

    public function logout(): void
    {
        $user = Auth::user();

        Auth::logout();

        if ($user) {
            AuditLogger::log($user, 'auth.logout', 'user', $user->id, ['provider' => 'keycloak']);
            $this->record($user->getKey(), $user->username, 'keycloak', 'success');
        }
    }

    public function isConfigured(): bool
    {
        return (bool) (config('services.keycloak.base_url') && config('services.keycloak.realms'));
    }

    private function record(?int $userId, ?string $username, string $provider, string $status): void
    {
        LoginAuditLog::create([
            'user_id' => $userId,
            'username' => $username,
            'auth_provider' => $provider,
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => $this->safeUserAgent(),
            'created_at' => now(),
        ]);
    }

    private function safeUserAgent(): ?string
    {
        $ua = request()->userAgent();

        return $ua !== null ? substr($ua, 0, 255) : null;
    }
}