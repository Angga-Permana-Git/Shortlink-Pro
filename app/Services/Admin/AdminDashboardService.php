<?php

namespace App\Services\Admin;

use App\Models\ClickEvent;
use App\Models\ShortUrl;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/** Data baca-saja untuk dashboard operasional admin. */
class AdminDashboardService
{
    /** @return array<string, int> */
    public function ringkasan(int $hari = 30): array
    {
        $awal = now()->subDays($hari - 1)->startOfDay();
        $akhir = now()->endOfDay();
        $awalSebelum = $awal->copy()->subDays($hari);
        $akhirSebelum = $awal->copy()->subSecond();

        return [
            'pengguna_aktif' => User::where('is_active', true)->count(),
            'pengguna_baru' => User::whereBetween('created_at', [$awal, $akhir])->count(),
            'pengguna_baru_sebelum' => User::whereBetween('created_at', [$awalSebelum, $akhirSebelum])->count(),
            'tautan' => ShortUrl::count(),
            'tautan_baru' => ShortUrl::whereBetween('created_at', [$awal, $akhir])->count(),
            'tautan_baru_sebelum' => ShortUrl::whereBetween('created_at', [$awalSebelum, $akhirSebelum])->count(),
            'tautan_aktif' => ShortUrl::where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count(),
            'klik' => (int) ShortUrl::sum('total_clicks'),
            'klik_periode' => ClickEvent::whereBetween('created_at', [$awal, $akhir])->count(),
            'klik_sebelum' => ClickEvent::whereBetween('created_at', [$awalSebelum, $akhirSebelum])->count(),
            'klik_hari_ini' => ClickEvent::whereDate('created_at', today())->count(),
            'linktree_terbit' => DB::table('linktree_profiles')->where('is_active', true)->count(),
            'tanpa_alamat' => DB::table('linktree_profiles')->where('is_active', true)->whereNull('public_slug')->count(),
        ];
    }

    /** @return array{tautan: array<int, int>, pengguna: array<int, int>} */
    public function pertumbuhan(int $hari = 30): array
    {
        $awal = now()->subDays($hari - 1)->startOfDay();
        $tanggal = collect(CarbonPeriod::create($awal->toDateString(), now()->toDateString()));

        return [
            'tautan' => $this->isiHarian($tanggal, ShortUrl::query(), 'tautan'),
            'pengguna' => $this->isiHarian($tanggal, User::query(), 'pengguna'),
        ];
    }

    /** @return Builder<ShortUrl> */
    public function tautanTerpopulerQuery(): Builder
    {
        return ShortUrl::query()->with('owner')->orderByDesc('total_clicks')->limit(5);
    }

    /** @return Builder<User> */
    public function penggunaAktifQuery(): Builder
    {
        return User::query()
            ->withCount('shortUrls')
            ->withCount(['shortUrls as tautan_aktif_count' => fn ($q) => $q->where('is_active', true)])
            ->withCount(['clicks as klik_gagal_count' => fn ($q) => $q->where('status', 'wrong_password')])
            ->withSum('shortUrls', 'total_clicks')
            ->where('is_active', true)
            ->orderByDesc('short_urls_sum_total_clicks');
    }

    /**
     * Tautan terbaru dari pengguna yang zona waktunya berbeda dari server.
     *
     * @return Builder<ShortUrl>
     */
    public function tautanLuarNegeriQuery(): Builder
    {
        $zonaServer = (string) config('app.timezone', 'UTC');

        return ShortUrl::query()
            ->with('owner')
            ->whereHas('owner', fn (Builder $query) => $query
                ->whereNotNull('timezone')
                ->where('timezone', '!=', $zonaServer))
            ->latest();
    }

    /** @return array<int, int> */
    private function isiHarian($tanggal, Builder $query, string $jenis): array
    {
        $kolom = $jenis === 'tautan' ? 'short_urls.created_at' : 'users.created_at';
        $nilai = $query->where($kolom, '>=', $tanggal->first()->startOfDay())
            ->selectRaw("DATE($kolom) as tanggal, COUNT(*) as jumlah")
            ->groupBy('tanggal')->pluck('jumlah', 'tanggal');

        return $tanggal->map(fn ($hari) => (int) ($nilai[$hari->toDateString()] ?? 0))->all();
    }
}
