<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\ClickEvent;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/** Query analitik dashboard admin di luar angka ringkas. */
class AdminAnalyticsService
{
    /**
     * @return array<int, array{domain: string, tautan: int, klik: int}>
     */
    public function domainTujuan(int $batas = 8): array
    {
        $hasil = [];
        $baris = ShortUrl::query()
            ->select('destination_url')
            ->selectRaw('COUNT(*) as tautan, SUM(total_clicks) as klik')
            ->groupBy('destination_url')
            ->get();

        foreach ($baris as $barisData) {
            $domain = parse_url((string) $barisData->destination_url, PHP_URL_HOST) ?: 'Tidak valid';
            $domain = preg_replace('/^www\./', '', $domain);
            $hasil[$domain] ??= ['domain' => $domain, 'tautan' => 0, 'klik' => 0];
            $hasil[$domain]['tautan'] += (int) $barisData->tautan;
            $hasil[$domain]['klik'] += (int) $barisData->klik;
        }

        return collect($hasil)->sortByDesc('klik')->take($batas)->values()->all();
    }

    /**
     * @return array{grid: array<int, array<int, int>>, maks: int, hari: array<int, string>}
     */
    public function heatmap(int $hari = 30): array
    {
        $grid = array_fill(0, 7, array_fill(0, 24, 0));
        $waktu = ClickEvent::query()
            ->where('created_at', '>=', now()->subDays($hari - 1)->startOfDay())
            ->pluck('created_at');

        foreach ($waktu as $nilai) {
            $carbon = Carbon::parse($nilai);
            $grid[$carbon->dayOfWeekIso - 1][(int) $carbon->format('G')]++;
        }

        return [
            'grid' => $grid,
            'maks' => max(1, max(array_map('max', $grid))),
            'hari' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        ];
    }

    /** @return Builder<AuditLog> */
    public function aktivitasTerbaru(int $batas = 10): Builder
    {
        return AuditLog::query()->with('actor')->latest()->limit($batas);
    }

    /** @return Builder<User> */
    public function penggunaPerluPerhatianQuery(): Builder
    {
        return User::query()
            ->withCount(['shortUrls as tautan_aktif_count' => fn (Builder $q) => $q->where('is_active', true)])
            ->where(function (Builder $q): void {
                $q->where(fn (Builder $x) => $x->where('created_at', '<=', now()->subDays(7))->whereDoesntHave('shortUrls'))
                    ->orWhere(fn (Builder $x) => $x->where('is_active', false)->whereHas('shortUrls', fn (Builder $t) => $t->where('is_active', true)))
                    ->orWhere(fn (Builder $x) => $x->where('role', 'admin')->whereNull('two_factor_confirmed_at'))
                    ->orWhereNull('timezone');
            })
            ->latest();
    }

    /** @return Builder<ShortUrl> */
    public function klikGagalQuery(): Builder
    {
        return ShortUrl::query()
            ->with('owner')
            ->whereHas('clicks', fn (Builder $q) => $q->where('status', 'wrong_password'))
            ->orderByDesc('total_clicks');
    }
}
