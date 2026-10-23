<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\ClickEvent;
use App\Models\LoginAuditLog;
use App\Models\RoleChangeLog;
use App\Services\Setting\OperationalSettingsService;
use Illuminate\Console\Command;

/** Menghapus jejak lama agar tabel log tidak tumbuh tanpa batas. */
class PurgeOldLogs extends Command
{
    protected $signature = 'logs:purge {--days= : Simpan log selama jumlah hari ini; kosong memakai pengaturan retensi}';

    protected $description = 'Hapus log audit, masuk, klik, dan perubahan peran yang lebih lama dari batas retensi';

    public function handle(): int
    {
        $hari = $this->option('days') !== null
            ? max(1, (int) $this->option('days'))
            : app(OperationalSettingsService::class)->logRetentionDays();
        $batas = now()->subDays($hari);

        $jumlah = [
            'audit' => AuditLog::where('created_at', '<', $batas)->delete(),
            'masuk' => LoginAuditLog::where('created_at', '<', $batas)->delete(),
            'klik' => ClickEvent::where('created_at', '<', $batas)->delete(),
            'peran' => RoleChangeLog::where('created_at', '<', $batas)->delete(),
        ];

        $this->info("Log lebih lama dari {$hari} hari dihapus: ".collect($jumlah)
            ->map(fn (int $n, string $jenis) => "{$jenis} {$n}")
            ->implode(', '));

        return self::SUCCESS;
    }
}
