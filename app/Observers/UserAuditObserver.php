<?php

namespace App\Observers;

use App\Models\User;
use App\Services\Audit\AuditLogger;

/**
 * Mencatat perubahan akun yang dilakukan dari panel admin, pendaftaran, atau
 * proses lain. Perubahan last_login_at tidak diaudit agar log login tetap
 * berada di login_audit_logs.
 */
class UserAuditObserver
{
    private const DIPANTAU = [
        'name',
        'username',
        'email',
        'role',
        'is_active',
        'is_protected',
        'auth_provider',
        'locale',
        'timezone',
        'password',
    ];

    public function created(User $pengguna): void
    {
        $this->catat($pengguna, 'user.create', [
            'email' => $pengguna->email,
            'role' => $pengguna->role,
        ]);
    }

    public function updated(User $pengguna): void
    {
        $perubahan = [];

        foreach (self::DIPANTAU as $kolom) {
            if (! $pengguna->wasChanged($kolom)) {
                continue;
            }

            $perubahan[$kolom] = $kolom === 'password'
                ? 'diperbarui'
                : ['lama' => $pengguna->getOriginal($kolom), 'baru' => $pengguna->getAttribute($kolom)];
        }

        if ($perubahan === []) {
            return;
        }

        $this->catat($pengguna, 'user.update', ['perubahan' => $perubahan]);
    }

    public function deleted(User $pengguna): void
    {
        if ($pengguna->isForceDeleting()) {
            return;
        }

        // Akun yang dihapus tidak boleh meninggalkan tautan/Linktree yang tetap hidup.
        $pengguna->shortUrls()->update(['is_active' => false]);
        $pengguna->linktreeProfile?->update(['is_active' => false]);

        $this->catat($pengguna, 'user.delete', ['email' => $pengguna->email]);
    }

    public function restored(User $pengguna): void
    {
        $this->catat($pengguna, 'user.restore', ['email' => $pengguna->email]);
    }

    public function forceDeleted(User $pengguna): void
    {
        $this->catat($pengguna, 'user.force_delete', ['email' => $pengguna->email]);
    }

    private function catat(User $pengguna, string $aksi, array $metadata): void
    {
        AuditLogger::log(auth()->user(), $aksi, 'user', $pengguna->getKey(), $metadata);
    }
}
