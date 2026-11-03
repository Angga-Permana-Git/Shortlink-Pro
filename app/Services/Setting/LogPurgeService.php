<?php

namespace App\Services\Setting;

use App\Models\AuditLog;
use App\Models\ClickEvent;
use App\Models\LoginAuditLog;
use App\Models\RoleChangeLog;

/**
 * Pratinjau dan eksekusi pembersihan log manual.
 *
 * Batas hari dibaca dari OperationalSettingsService agar sama dengan
 * perintah terjadwal `logs:purge`. Service ini hanya menghitung dan
 * menghapus; pencatatan audit dilakukan pemanggil yang punya aktor.
 */
class LogPurgeService
{
    public function __construct(private readonly OperationalSettingsService $pengaturan) {}

    public function hariRetensi(): int
    {
        return $this->pengaturan->logRetentionDays();
    }

    /**
     * @return array{hari: int, audit: int, masuk: int, klik: int, peran: int, total: int}
     */
    public function pratinjau(): array
    {
        $batas = now()->subDays($this->hariRetensi());

        $audit = AuditLog::where('created_at', '<', $batas)->count();
        $masuk = LoginAuditLog::where('created_at', '<', $batas)->count();
        $klik = ClickEvent::where('created_at', '<', $batas)->count();
        $peran = RoleChangeLog::where('created_at', '<', $batas)->count();

        return [
            'hari' => $this->hariRetensi(),
            'audit' => $audit,
            'masuk' => $masuk,
            'klik' => $klik,
            'peran' => $peran,
            'total' => $audit + $masuk + $klik + $peran,
        ];
    }

    /**
     * @return array{hari: int, audit: int, masuk: int, klik: int, peran: int}
     */
    public function bersihkan(): array
    {
        $batas = now()->subDays($this->hariRetensi());

        return [
            'hari' => $this->hariRetensi(),
            'audit' => AuditLog::where('created_at', '<', $batas)->delete(),
            'masuk' => LoginAuditLog::where('created_at', '<', $batas)->delete(),
            'klik' => ClickEvent::where('created_at', '<', $batas)->delete(),
            'peran' => RoleChangeLog::where('created_at', '<', $batas)->delete(),
        ];
    }
}
