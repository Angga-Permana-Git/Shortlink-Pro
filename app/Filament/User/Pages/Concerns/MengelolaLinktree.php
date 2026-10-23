<?php

namespace App\Filament\User\Pages\Concerns;

use App\Services\Dashboard\LinktreeSayaService;
use App\Services\Dashboard\UserDashboardService;
use App\Services\Linktree\LinktreeAvatarService;
use App\Support\Validation\AturanProfilLinktree;

/**
 * Aksi kartu "Link Saya" bagian penyimpanan pilihan Linktree, dan kartu "Profil
 * Linktree": identitas profil, alamat publik, logo, serta buka/tutup bagian
 * identitasnya.
 *
 * Dipisah dari UserDashboard agar kelas halaman tetap di bawah batas 150 baris
 * (disiplin_struktur.md bagian 2); penyimpanan didelegasikan ke LinktreeSayaService
 * dan LinktreeAvatarService.
 */
trait MengelolaLinktree
{
    /**
     * Id tautan yang dipilih untuk tampil di halaman Linktree.
     *
     * @var array<int, int>
     */
    public array $pilihanLinktree = [];

    public bool $terbitkan = true;

    /**
     * Kabar hasil penyimpanan pilihan Linktree.
     */
    public ?string $pesanPilihan = null;

    /**
     * Isian identitas profil Linktree. Kunci mengikuti nama kolom kanonik agar
     * aturan validasi bersama bisa dipakai apa adanya.
     *
     * @var array<string, mixed>
     */
    public array $profil = [
        'display_name' => '',
        'bio' => '',
        'public_slug' => '',
        'logo' => null,
    ];

    /**
     * Kabar hasil penyimpanan identitas atau logo profil.
     */
    public ?string $pesanProfil = null;

    /**
     * Bagian identitas kartu Profil Linktree sedang terbuka; pratinjaunya selalu
     * tampil. Disimpan di keadaan server supaya pilihan buka/tutup bertahan
     * setelah aksi Livewire.
     */
    public bool $bukaProfil = true;

    public function simpanLinktree(): void
    {
        $layanan = app(UserDashboardService::class);
        $pengguna = auth()->user();

        app(LinktreeSayaService::class)->simpanPilihan($pengguna, $this->pilihanLinktree, $this->terbitkan);

        $this->pilihanLinktree = $this->idTautanTerpilih($layanan, $pengguna);
        $this->pesanPilihan = 'Pilihan Linktree tersimpan.';
    }

    public function simpanProfil(): void
    {
        $pengguna = auth()->user();

        $tervalidasi = $this->validate(
            AturanProfilLinktree::semua('profil.', $pengguna->id),
            [],
            AturanProfilLinktree::atribut('profil.'),
        )['profil'];

        app(LinktreeSayaService::class)->simpanProfil(
            $pengguna,
            $tervalidasi['display_name'],
            $tervalidasi['bio'] ?? null,
            $tervalidasi['public_slug'],
        );

        if ($tervalidasi['logo'] ?? null) {
            app(LinktreeAvatarService::class)->simpan($pengguna, $tervalidasi['logo']);
        }

        // Berkas sementara tidak disimpan di keadaan lagi supaya tidak diunggah
        // ulang pada penyimpanan berikutnya.
        $this->profil['logo'] = null;

        $this->pesanProfil = 'Profil Linktree tersimpan.';
    }

    public function hapusLogo(): void
    {
        app(LinktreeAvatarService::class)->hapus(auth()->user());

        $this->pesanProfil = 'Logo profil dihapus.';
    }

    public function ubahStatusTerbit(): void
    {
        $this->terbitkan = ! $this->terbitkan;
    }

    public function lipatProfil(): void
    {
        $this->bukaProfil = ! $this->bukaProfil;
    }
}
