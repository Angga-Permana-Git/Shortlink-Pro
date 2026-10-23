<?php

namespace App\Services\Auth;

use App\Dto\RequestContextDTO;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Hash;

/**
 * Pemeriksaan kredensial akun lokal (username atau email).
 *
 * Mengembalikan null bila akun tidak ditemukan, tidak aktif, bukan akun lokal,
 * atau kata sandinya salah; setiap penolakan dicatat di jejak audit.
 */
class LocalAccountVerifier
{
    public function __construct(private readonly LoginAuditRecorder $audit) {}

    public function verify(string $identifier, string $password, RequestContextDTO $context): ?User
    {
        $user = $this->findLocalUser($identifier);

        if (! $user) {
            $this->audit->record(null, $identifier, 'local', 'failed', $context);

            return null;
        }

        if (! $user->isActive()) {
            $this->audit->record($user->getKey(), $identifier, 'local', 'failed', $context);
            AuditLogger::log($user, 'auth.login_blocked', 'user', $user->id, ['reason' => 'inactive']);

            return null;
        }

        if (! $user->password || ! Hash::check($password, $user->password)) {
            $this->audit->record($user->getKey(), $identifier, 'local', 'failed', $context);
            AuditLogger::log($user, 'auth.login_failed', 'user', $user->id);

            return null;
        }

        return $user;
    }

    /**
     * Hanya akun lokal yang boleh masuk lewat jalur darurat ini.
     */
    private function findLocalUser(string $identifier): ?User
    {
        $identifier = strtolower(trim($identifier));

        return User::query()
            ->where('auth_provider', 'local')
            ->where(function ($query) use ($identifier): void {
                $query->where('username', $identifier)->orWhere('email', $identifier);
            })
            ->first();
    }
}
