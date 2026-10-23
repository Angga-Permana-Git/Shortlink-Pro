<?php

namespace App\Services\Dashboard;

use App\Models\ShortUrl;
use App\Models\User;

/** Ringkasan hal yang perlu ditindaklanjuti pemilik tautan. */
class UserKesehatanService
{
    /**
     * @return array<string, int|bool>
     */
    public function ringkasan(User $pengguna): array
    {
        $dasar = fn () => ShortUrl::query()->where('owner_id', $pengguna->id);
        $profil = $pengguna->linktreeProfile;

        return [
            'kedaluwarsa_7' => $dasar()->where('is_active', true)->whereBetween('expires_at', [now(), now()->addDays(7)])->count(),
            'sudah_kedaluwarsa' => $dasar()->whereNotNull('expires_at')->where('expires_at', '<=', now())->count(),
            'terjadwal' => $dasar()->whereNotNull('starts_at')->where('starts_at', '>', now())->count(),
            'belum_diklik' => $dasar()->where('total_clicks', 0)->count(),
            'aktif_tanpa_klik' => $dasar()->where('is_active', true)->where('total_clicks', 0)->count(),
            'klik_gagal' => $dasar()->whereHas('clicks', fn ($q) => $q->where('status', 'wrong_password'))->count(),
            'linktree_kosong' => (bool) ($profil?->is_active && $profil->links()->count() === 0),
            'linktree_tanpa_alamat' => (bool) ($profil?->is_active && ! $profil->public_slug),
        ];
    }

    /**
     * @return array<int, array{judul: string, pesan: string, jumlah: int, filter: string, target: string}>
     */
    public function daftar(User $pengguna): array
    {
        $angka = $this->ringkasan($pengguna);
        $butir = [
            ['Kedaluwarsa ≤7 hari', 'Tautan aktif yang segera berakhir.', 'kedaluwarsa_7', 'expiring', 'tautan'],
            ['Sudah kedaluwarsa', 'Tautan yang tidak dapat dibuka lagi.', 'sudah_kedaluwarsa', 'expired', 'tautan'],
            ['Terjadwal', 'Tautan yang belum aktif.', 'terjadwal', 'scheduled', 'tautan'],
            ['Belum pernah diklik', 'Tautan yang belum menghasilkan klik.', 'belum_diklik', 'never', 'tautan'],
            ['Klik gagal', 'Percobaan dengan kata sandi salah.', 'klik_gagal', 'failed', 'tautan'],
        ];

        $hasil = collect($butir)
            ->filter(fn (array $item): bool => $angka[$item[2]] > 0)
            ->map(fn (array $item): array => [
                'judul' => $item[0],
                'pesan' => $item[1],
                'jumlah' => (int) $angka[$item[2]],
                'filter' => $item[3],
                'target' => $item[4],
            ])->values()->all();

        if ($angka['linktree_kosong']) {
            $hasil[] = ['judul' => 'Linktree kosong', 'pesan' => 'Halaman publik terbit tanpa tautan.', 'jumlah' => 1, 'filter' => '', 'target' => 'linktree'];
        }

        if ($angka['linktree_tanpa_alamat']) {
            $hasil[] = ['judul' => 'Alamat Linktree kosong', 'pesan' => 'Isi alamat publik agar halaman bisa dibagikan.', 'jumlah' => 1, 'filter' => '', 'target' => 'linktree'];
        }

        return $hasil;
    }
}
