<?php

namespace App\Services\Dashboard;

use App\Models\ShortUrl;
use App\Models\User;
use Carbon\CarbonPeriod;

/** Angka ringkas dan perbandingan tren klik Dashboard pengguna. */
class UserStatistikService
{
    /**
     * @return array{hari: int, harian: array<int, array{tanggal: string, klik: int}>, total: int, sebelum: int, delta: int|null, rata: float, terbaik: array{tanggal: string, klik: int}, hari_ini: int}
     */
    public function trenKlik(User $pengguna, int $hari = 30): array
    {
        $hari = in_array($hari, [7, 30, 90], true) ? $hari : 30;
        $awal = now()->subDays($hari - 1)->startOfDay();
        $akhir = now()->endOfDay();
        $awalSebelum = $awal->copy()->subDays($hari);
        $akhirSebelum = $awal->copy()->subSecond();

        $harian = $this->harian($pengguna, $awal, $akhir);
        $total = array_sum(array_column($harian, 'klik'));
        $sebelum = (int) $this->klik($pengguna, $awalSebelum, $akhirSebelum)->count();
        $terbaik = collect($harian)->sortByDesc('klik')->first() ?? ['tanggal' => '-', 'klik' => 0];

        return [
            'hari' => $hari,
            'harian' => $harian,
            'total' => $total,
            'sebelum' => $sebelum,
            'delta' => $sebelum > 0 ? (int) round((($total - $sebelum) / $sebelum) * 100) : ($total > 0 ? 100 : null),
            'rata' => round($total / $hari, 1),
            'terbaik' => $terbaik,
            'hari_ini' => $harian[count($harian) - 1]['klik'] ?? 0,
        ];
    }

    /**
     * @return array{slug: string, klik: int}|null
     */
    public function tautanTerpopuler(User $pengguna): ?array
    {
        $tautan = ShortUrl::query()
            ->where('owner_id', $pengguna->id)
            ->orderByDesc('total_clicks')
            ->first('slug', 'total_clicks');

        if (! $tautan) {
            return null;
        }

        return ['slug' => $tautan->slug, 'klik' => (int) $tautan->total_clicks];
    }

    /**
     * @return array{total: int, aktif: int, klik: int, terproteksi: int}
     */
    public function ringkasan(User $pengguna): array
    {
        $dasar = fn () => ShortUrl::query()->where('owner_id', $pengguna->id);

        return [
            'total' => $dasar()->count(),
            'aktif' => $dasar()->where('is_active', true)->count(),
            'klik' => (int) $dasar()->sum('total_clicks'),
            'terproteksi' => $dasar()->whereNotNull('password_hash')->count(),
        ];
    }

    /**
     * @return array<int, array{tanggal: string, klik: int}>
     */
    private function harian(User $pengguna, $awal, $akhir): array
    {
        $kelompok = $this->klik($pengguna, $awal, $akhir)
            ->selectRaw('DATE(click_events.created_at) as tanggal, COUNT(*) as jumlah')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('jumlah', 'tanggal')
            ->all();

        return collect(CarbonPeriod::create($awal->toDateString(), $akhir->toDateString()))
            ->map(fn ($tanggal): array => [
                'tanggal' => $tanggal->format('d M'),
                'klik' => (int) ($kelompok[$tanggal->toDateString()] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function klik(User $pengguna, $awal, $akhir)
    {
        return ShortUrl::query()
            ->join('click_events', 'short_urls.id', '=', 'click_events.short_url_id')
            ->where('short_urls.owner_id', $pengguna->id)
            ->whereBetween('click_events.created_at', [$awal, $akhir]);
    }
}
