<?php

namespace App\Services\Auth;

use App\Models\LoginAuditLog;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LocalAuthService
{
    public function attempt(string $identifier, string $password): bool
    {
        $user = $this->findByIdentifier($identifier);

        if (! $user) {
            AuditLogger::log(null, 'auth.login_failed', 'user', null, ['identifier' => $identifier]);

            return false;
        }

        if (! $user->isActive()) {
            AuditLogger::log($user, 'auth.login_blocked', 'user', $user->id, ['reason' => 'inactive']);

            return false;
        }

        if (! Hash::check($password, $user->password)) {
            AuditLogger::log($user, 'auth.login_failed', 'user', $user->id);

            return false;
        }

        Auth::login($user);

        $user->forceFill(['last_login_at' => now()])->save();

        AuditLogger::log($user, 'auth.login', 'user', $user->id);

        return true;
    }

    /**
     * Local emergency login. Only local accounts with a set password are allowed.
     */
    public function attemptLocal(string $identifier, string $password): bool
    {
        $user = $this->findByUsername($identifier);

        if (! $user) {
            $this->record(null, $identifier, 'local', 'failed');

            return false;
        }

        if (! $user->isActive()) {
            $this->record($user->getKey(), $identifier, 'local', 'failed');
            AuditLogger::log($user, 'auth.login_blocked', 'user', $user->id, ['reason' => 'inactive']);

            return false;
        }

        if (! $user->password || ! Hash::check($password, $user->password)) {
            $this->record($user->getKey(), $identifier, 'local', 'failed');
            AuditLogger::log($user, 'auth.login_failed', 'user', $user->id);

            return false;
        }

        Auth::login($user);

        $user->forceFill(['last_login_at' => now()])->save();

        $this->record($user->getKey(), $identifier, 'local', 'success');
        AuditLogger::log($user, 'auth.login_local', 'user', $user->id);

        return true;
    }

    public function logout(): void
    {
        $user = Auth::user();

        Auth::logout();

        if ($user) {
            AuditLogger::log($user, 'auth.logout', 'user', $user->id);
        }
    }

    private function findByIdentifier(string $identifier): ?User
    {
        return User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();
    }

    private function findByUsername(string $identifier): ?User
    {
        $identifier = strtolower(trim($identifier));

        return User::query()
            ->where('auth_provider', 'local')
            ->where(function ($q) use ($identifier) {
                $q->where('username', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();
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