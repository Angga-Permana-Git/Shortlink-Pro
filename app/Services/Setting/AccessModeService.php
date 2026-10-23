<?php

namespace App\Services\Setting;

use App\Models\Setting;
use App\Models\User;
use App\Services\Audit\AuditLogger;

/**
 * Sumber tunggal mode akses publik.
 *
 * Kunci pengaturan force_sso hanya dibaca dan ditulis dari sini, supaya middleware,
 * halaman masuk, dan panel admin selalu memakai nilai yang sama.
 */
class AccessModeService
{
    private const FORCE_SSO_KEY = 'force_sso';

    public function forceSso(): bool
    {
        return (bool) Setting::get(self::FORCE_SSO_KEY, false);
    }

    public function setForceSso(User $actor, bool $active): void
    {
        Setting::set(self::FORCE_SSO_KEY, $active);

        AuditLogger::log($actor, 'access_mode.force_sso', 'setting', null, ['force_sso' => $active]);
    }
}
