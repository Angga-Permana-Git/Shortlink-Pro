<?php

namespace App\Filament\User\Pages\Concerns;

use App\Services\Dashboard\BioSayaService;
use App\Services\Dashboard\UserDashboardService;
use App\Services\Bio\BioAvatarService;
use App\Support\Validation\AturanProfilBio;

/**
 * Aksi kartu "Link Saya" bagian penyimpanan pilihan Bio, dan kartu "Profil
 * Bio": identitas profil, alamat publik, logo, serta buka/tutup bagian
 * identitasnya.
 *
 * Dipisah dari UserDashboard agar kelas halaman tetap di bawah batas 150 baris
 * (disiplin_struktur.md bagian 2); penyimpanan didelegasikan ke BioSayaService
 * dan BioAvatarService.
 */
trait MengelolaBio
{
    /**
     * Id tautan yang dipilih untuk tampil di halaman Bio.
     *
     * @var array<int, int>
     */
    public array $pilihanBio = [];

    public bool $terbitkan = true;

    /**
     * Kabar hasil penyimpanan pilihan Bio.
     */
    public ?string $pesanPilihan = null;

    /**
     * Isian identitas profil Bio. Kunci mengikuti nama kolom kanonik agar
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
     * Bagian identitas kartu Profil Link in Bio sedang terbuka; pratinjaunya selalu
     * tampil. Disimpan di keadaan server supaya pilihan buka/tutup bertahan
     * setelah aksi Livewire.
     */
    public bool $bukaProfil = true;

    public function simpanBio(): void
    {
        $layanan = app(UserDashboardService::class);
        $pengguna = auth()->user();

        app(BioSayaService::class)->simpanPilihan($pengguna, $this->pilihanBio, $this->terbitkan);

        $this->pilihanBio = $this->idTautanTerpilih($layanan, $pengguna);
        $this->pesanPilihan = __('ui.panel.pilihan_tersimpan');
    }

    public function simpanProfil(): void
    {
        $pengguna = auth()->user();

        $tervalidasi = $this->validate(
            AturanProfilBio::semua('profil.', $pengguna->id),
            [],
            AturanProfilBio::atribut('profil.'),
        )['profil'];

        app(BioSayaService::class)->simpanProfil(
            $pengguna,
            $tervalidasi['display_name'],
            $tervalidasi['bio'] ?? null,
            $tervalidasi['public_slug'],
        );

        if ($tervalidasi['logo'] ?? null) {
            app(BioAvatarService::class)->simpan($pengguna, $tervalidasi['logo']);
        }

        // Berkas sementara tidak disimpan di keadaan lagi supaya tidak diunggah
        // ulang pada penyimpanan berikutnya.
        $this->profil['logo'] = null;

        $this->pesanProfil = __('ui.panel.profil_tersimpan');
    }

    public function hapusLogo(): void
    {
        app(BioAvatarService::class)->hapus(auth()->user());

        $this->pesanProfil = __('ui.panel.logo_dihapus');
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
