<?php

namespace App\Services\Admin;

use App\Services\Setting\OperationalSettingsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Ringkasan kesehatan teknis aplikasi untuk dashboard admin. */
class AdminSystemService
{
    public function __construct(private readonly OperationalSettingsService $pengaturan) {}

    /** @return array<string, string|int|bool> */
    public function ringkasan(): array
    {
        $denyut = Cache::get('jadwal_terakhir');
        $jadwalAktif = $denyut && Carbon::parse($denyut)->gte(now()->subMinutes(2));

        return [
            'jadwal' => $jadwalAktif ? 'Aktif' : 'Belum tercatat',
            'jadwal_aktif' => (bool) $jadwalAktif,
            'antrean' => $this->hitungTabel('jobs'),
            'antrean_gagal' => $this->hitungTabel('failed_jobs'),
            'retensi_log' => $this->pengaturan->logRetentionDays().' hari',
            'mailer' => (string) config('mail.default', '-'),
            'versi' => app()->version(),
            'ukuran_database' => $this->ukuranDatabase(),
        ];
    }

    private function hitungTabel(string $tabel): int
    {
        return Schema::hasTable($tabel) ? DB::table($tabel)->count() : 0;
    }

    private function ukuranDatabase(): string
    {
        try {
            if (DB::connection()->getDriverName() === 'sqlite') {
                $jalur = DB::connection()->getDatabaseName();
                $byte = is_file($jalur) ? (int) filesize($jalur) : 0;

                return $this->formatUkuran($byte);
            }

            $baris = DB::selectOne('SELECT SUM(data_length + index_length) AS ukuran FROM information_schema.tables WHERE table_schema = DATABASE()');

            return $this->formatUkuran((int) ($baris->ukuran ?? 0));
        } catch (\Throwable) {
            return 'Tidak tersedia';
        }
    }

    private function formatUkuran(int $byte): string
    {
        return match (true) {
            $byte >= 1073741824 => round($byte / 1073741824, 1).' GB',
            $byte >= 1048576 => round($byte / 1048576, 1).' MB',
            $byte >= 1024 => round($byte / 1024, 1).' KB',
            default => $byte.' B',
        };
    }
}
