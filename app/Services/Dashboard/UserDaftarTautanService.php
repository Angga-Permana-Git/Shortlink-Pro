<?php

namespace App\Services\Dashboard;

use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Linktree\LinktreeService;
use App\Services\Time\UserTimezoneService;
use Carbon\CarbonInterface;

/**
 * Daftar tautan milik pengguna beserta status pilihannya.
 *
 * Mendukung pencarian, filter status, dan pengurutan. Data dikembalikan siap
 * tampil supaya tampilan tidak menulis query (disiplin_struktur.md bagian 1).
 */
class UserDaftarTautanService
{
    public function __construct(
        private readonly LinktreeService $linktree,
        private readonly UserTimezoneService $waktu,
    ) {}

    /**
     * @param  string  $pencarian  Kata kunci pencarian (slug/tujuan)
     * @param  string  $filterStatus  '' | 'active' | 'inactive' | 'protected' | 'expired'
     * @param  string  $urutkan  'terbaru' | 'klik' | 'abjad'
     * @return array<int, array{id: int, slug: string, url: string, tujuan: string, klik: int, aktif: bool, bersandi: bool, kedaluwarsa: CarbonInterface|null, dipilih: bool, status: string, status_teks: string, aktif_selamanya: bool}>
     */
    /**
     * @param  string  $filterStatus  '' | 'active' | 'inactive' | 'protected' | 'expired' | 'expiring' | 'scheduled' | 'never' | 'failed'
     */
    public function daftar(User $pengguna, string $pencarian = '', string $filterStatus = '', string $urutkan = 'terbaru'): array
    {
        $dipilih = $this->linktree->profile($pengguna)->links()->pluck('url')->all();

        $query = ShortUrl::query()
            ->where('owner_id', $pengguna->id)
            ->when($pencarian !== '', function ($q) use ($pencarian) {
                $q->where(function ($sub) use ($pencarian) {
                    $sub->where('slug', 'like', "%{$pencarian}%")
                        ->orWhere('destination_url', 'like', "%{$pencarian}%");
                });
            });

        match ($filterStatus) {
            'active' => $query
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())),
            'inactive' => $query->where('is_active', false),
            'protected' => $query->whereNotNull('password_hash'),
            'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<=', now()),
            'expiring' => $query->where('is_active', true)->whereBetween('expires_at', [now(), now()->addDays(7)]),
            'scheduled' => $query->whereNotNull('starts_at')->where('starts_at', '>', now()),
            'never' => $query->where('total_clicks', 0),
            'failed' => $query->whereHas('clicks', fn ($q) => $q->where('status', 'wrong_password')),
            default => null,
        };

        match ($urutkan) {
            'klik' => $query->orderByDesc('total_clicks'),
            'abjad' => $query->orderBy('slug'),
            default => $query->latest(),
        };

        return $query->get()
            ->map(fn (ShortUrl $tautan): array => [
                'id' => $tautan->id,
                'slug' => $tautan->slug,
                'url' => $tautan->shortUrl(),
                'tujuan' => $tautan->destination_url,
                'klik' => $tautan->total_clicks,
                'aktif' => $tautan->is_active,
                'bersandi' => $tautan->hasPassword(),
                'kedaluwarsa' => $this->waktu->kePengguna($tautan->expires_at, $pengguna),
                'dipilih' => in_array($tautan->shortUrl(), $dipilih, true),
                'status' => $this->statusKode($tautan),
                'status_teks' => $this->statusTeks($tautan),
                'aktif_selamanya' => ! $tautan->expires_at && ! $tautan->starts_at,
            ])
            ->all();
    }

    /**
     * Kode status ringkas untuk perbandingan di tampilan: inactive, expired,
     * scheduled, forever, atau active.
     */
    private function statusKode(ShortUrl $tautan): string
    {
        if (! $tautan->is_active) {
            return 'inactive';
        }

        if ($tautan->isExpired()) {
            return 'expired';
        }

        if ($tautan->notStarted()) {
            return 'scheduled';
        }

        if (! $tautan->expires_at && ! $tautan->starts_at) {
            return 'forever';
        }

        return 'active';
    }

    /**
     * Teks status ringkas sesuai bahasa aktif.
     */
    private function statusTeks(ShortUrl $tautan): string
    {
        return match ($this->statusKode($tautan)) {
            'inactive' => __('ui.panel.nonaktif'),
            'expired' => __('ui.panel.kedaluwarsa'),
            'scheduled' => __('ui.panel.akan_mulai'),
            'forever' => __('ui.panel.aktif_selamanya'),
            default => __('ui.panel.aktif'),
        };
    }
}
