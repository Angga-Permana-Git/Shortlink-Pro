<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Fasad data halaman tunggal pengguna.
 *
 * Halaman Filament hanya menerima data siap pakai dari kelas ini, sehingga tidak
 * ada query maupun aturan bisnis yang ditulis di tampilan
 * (disiplin_struktur.md bagian 1).
 */
class UserDashboardService
{
    public function __construct(
        private readonly UserStatistikService $statistik,
        private readonly UserDaftarTautanService $daftar,
        private readonly UserEksporTautanService $ekspor,
        private readonly UserTinjauanLinktreeService $tinjauan,
        private readonly UserAktivitasService $aktivitas,
        private readonly UserKesehatanService $kesehatan,
        private readonly UserLinktreeStatistikService $linktree,
    ) {}

    /**
     * @return array{hari: int, harian: array<int, array{tanggal: string, klik: int}>, total: int, sebelum: int, delta: int|null, rata: float, terbaik: array{tanggal: string, klik: int}, hari_ini: int}
     */
    public function trenKlik(User $pengguna, int $hari = 30): array
    {
        return $this->statistik->trenKlik($pengguna, $hari);
    }

    /**
     * @return array{sumber: array<int, array{label: string, jumlah: int, persen: float}>, jam: array{jam: array<int, int>, puncak: string}, kualitas: array{total: int, berhasil: int, gagal: int, persen: float, rincian: array<int, array{label: string, jumlah: int}>}, terbaru: array<int, array{slug: string, status: string, waktu: string}>}
     */
    public function aktivitas(User $pengguna, int $hari = 30): array
    {
        return [
            'sumber' => $this->aktivitas->sumberKlik($pengguna, $hari),
            'jam' => $this->aktivitas->jamSibuk($pengguna, $hari),
            'kualitas' => $this->aktivitas->kualitas($pengguna, $hari),
            'terbaru' => $this->aktivitas->terbaru($pengguna),
        ];
    }

    /**
     * @return array{ringkasan: array<string, int|bool>, daftar: array<int, array{judul: string, pesan: string, jumlah: int, filter: string, target: string}>}
     */
    public function kesehatan(User $pengguna): array
    {
        return ['ringkasan' => $this->kesehatan->ringkasan($pengguna), 'daftar' => $this->kesehatan->daftar($pengguna)];
    }

    /**
     * @return array{terbit: bool, alamat: string|null, kunjungan: int, pengunjung: int, klik: int, ctr: float, tautan: array<int, array{label: string, klik: int}>}
     */
    public function linktreeStatistik(User $pengguna, int $hari = 30): array
    {
        return $this->linktree->ringkasan($pengguna, $hari);
    }

    /**
     * @return array{slug: string, klik: int}|null
     */
    public function tautanTerpopuler(User $pengguna): ?array
    {
        return $this->statistik->tautanTerpopuler($pengguna);
    }

    /**
     * @return array{total: int, aktif: int, klik: int, terproteksi: int}
     */
    public function ringkasan(User $pengguna): array
    {
        return $this->statistik->ringkasan($pengguna);
    }

    /**
     * @return array<int, array{id: int, slug: string, url: string, tujuan: string, klik: int, aktif: bool, bersandi: bool, kedaluwarsa: CarbonInterface|null, dipilih: bool, status_teks: string, aktif_selamanya: bool}>
     */
    public function daftarTautan(User $pengguna, string $pencarian = '', string $filterStatus = '', string $urutkan = 'terbaru'): array
    {
        return $this->daftar->daftar($pengguna, $pencarian, $filterStatus, $urutkan);
    }

    public function eksporCsv(User $pengguna, string $pencarian = '', string $filterStatus = '', string $urutkan = 'terbaru'): string
    {
        return $this->ekspor->eksporCsv($pengguna, $pencarian, $filterStatus, $urutkan);
    }

    /**
     * @return array{nama: string, bio: string|null, logo: string|null, terbit: bool, slug: string|null, alamat: string|null, tautan: array<int, array{label: string, url: string}>}
     */
    public function tinjauanLinktree(User $pengguna): array
    {
        return $this->tinjauan->tinjauan($pengguna);
    }
}
