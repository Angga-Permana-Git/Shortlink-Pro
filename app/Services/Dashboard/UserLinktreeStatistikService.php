<?php

namespace App\Services\Dashboard;

use App\Models\LinktreeEvent;
use App\Models\User;

/** Statistik kunjungan dan klik halaman Linktree milik pengguna. */
class UserLinktreeStatistikService
{
    /**
     * @return array{terbit: bool, alamat: string|null, kunjungan: int, pengunjung: int, klik: int, ctr: float, tautan: array<int, array{label: string, klik: int}>}
     */
    public function ringkasan(User $pengguna, int $hari = 30): array
    {
        $profil = $pengguna->linktreeProfile;

        if (! $profil) {
            return ['terbit' => false, 'alamat' => null, 'kunjungan' => 0, 'pengunjung' => 0, 'klik' => 0, 'ctr' => 0, 'tautan' => []];
        }

        $dasar = fn () => LinktreeEvent::query()
            ->where('linktree_profile_id', $profil->id)
            ->where('created_at', '>=', now()->subDays($hari));
        $kunjungan = (clone $dasar())->where('jenis', 'kunjungan')->count();
        $klik = (clone $dasar())->where('jenis', 'klik')->count();
        $pengunjung = (clone $dasar())->where('jenis', 'kunjungan')->whereNotNull('ip_hash')->distinct('ip_hash')->count('ip_hash');
        $jumlah = (clone $dasar())->where('jenis', 'klik')->selectRaw('linktree_link_id, COUNT(*) as jumlah')
            ->groupBy('linktree_link_id')->pluck('jumlah', 'linktree_link_id');

        return [
            'terbit' => $profil->is_active,
            'alamat' => $profil->public_slug ? route('linktree.show', $profil->public_slug) : null,
            'kunjungan' => $kunjungan,
            'pengunjung' => $pengunjung,
            'klik' => $klik,
            'ctr' => $kunjungan > 0 ? round(($klik / $kunjungan) * 100, 1) : 0,
            'tautan' => $profil->links->map(fn ($tautan): array => [
                'label' => $tautan->label,
                'klik' => (int) ($jumlah[$tautan->id] ?? 0),
            ])->sortByDesc('klik')->take(5)->values()->all(),
        ];
    }
}
