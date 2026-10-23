<?php

namespace App\Services\Auth;

use App\Dto\RequestContextDTO;
use App\Models\LoginAuditLog;

/**
 * Pencatatan riwayat percobaan masuk ke tabel login_audit_logs.
 *
 * Konteks HTTP dikirim pemanggil lewat RequestContextDTO, jadi tidak ada
 * service yang membaca request sendiri (disiplin_struktur.md bagian 1).
 */
class LoginAuditRecorder
{
    public function record(
        ?int $userId,
        ?string $username,
        string $provider,
        string $status,
        RequestContextDTO $context,
    ): void {
        LoginAuditLog::create([
            'user_id' => $userId,
            'username' => $username,
            'auth_provider' => $provider,
            'status' => $status,
            'ip_address' => $context->ipAddress,
            'user_agent' => $context->userAgent !== null ? substr($context->userAgent, 0, 255) : null,
            'created_at' => now(),
        ]);
    }
}
