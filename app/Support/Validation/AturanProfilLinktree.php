<?php

namespace App\Support\Validation;

/**
 * Aturan validasi identitas halaman Linktree pengguna.
 *
 * Dipakai halaman panel pengguna saat menyimpan nama tampilan, keterangan,
 * alamat publik, dan logo profil, supaya bentuk data yang diterima layanan
 * hanya ditulis di satu tempat.
 */
final class AturanProfilLinktree
{
    /**
     * Jenis berkas logo diperiksa di App\Support\Keamanan\BerkasLogo dan
     * App\Services\Linktree\LinktreeAvatarService: ekstensi ditentukan dari isi
     * berkas, dan SVG yang memuat skrip ditolak.
     *
     * @param  int|null  $penggunaId  Pemilik profil yang sedang disimpan, supaya
     *                                alamat publiknya sendiri tidak dianggap bentrok.
     * @return array<string, array<int, string>>
     */
    public static function semua(string $awalan = '', ?int $penggunaId = null): array
    {
        return [
            $awalan.'display_name' => ['required', 'string', 'max:255'],
            $awalan.'bio' => ['nullable', 'string', 'max:500'],
            $awalan.'public_slug' => array_merge(['required'], self::alamatPublik($penggunaId)),
            $awalan.'logo' => ['nullable', 'file', 'max:2048'],
        ];
    }

    /**
     * Aturan alamat publik halaman (`/u/{public_slug}`).
     *
     * Hanya huruf kecil, angka, dan tanda hubung; tidak boleh diakhiri tanda
     * hubung, dan tidak boleh dipakai profil lain. Bentuk `required`/`sometimes`
     * ditentukan pemanggil karena kebutuhan tiap jalur berbeda.
     *
     * @return array<int, string>
     */
    public static function alamatPublik(?int $penggunaId = null): array
    {
        $unik = 'unique:linktree_profiles,public_slug';

        if ($penggunaId !== null) {
            $unik .= ','.$penggunaId.',user_id';
        }

        return ['string', 'max:60', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', $unik];
    }

    /**
     * @return array<string, string>
     */
    public static function atribut(string $awalan = ''): array
    {
        return [
            $awalan.'display_name' => 'nama tampilan',
            $awalan.'bio' => 'keterangan',
            $awalan.'public_slug' => 'alamat publik',
            $awalan.'logo' => 'logo',
        ];
    }
}
