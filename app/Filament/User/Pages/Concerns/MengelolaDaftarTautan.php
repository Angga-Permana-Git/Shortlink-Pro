<?php

namespace App\Filament\User\Pages\Concerns;

/**
 * Keadaan dan aksi daftar tautan pada kartu "Link Saya".
 *
 * Berisi pencarian, filter, urutan, ekspor CSV, dan buka/tutup kartu. Aksi
 * sunting/hapus/QR ada di trait MengelolaTautan; pemisahan ini menjaga setiap
 * berkas tetap di bawah batas 150 baris (disiplin_struktur.md bagian 2).
 */
trait MengelolaDaftarTautan
{
    /**
     * Kabar hasil pembuatan, perubahan, atau penghapusan tautan.
     */
    public ?string $pesanTautan = null;

    /**
     * Isi kartu Link Saya sedang terbuka. Hanya tampilan; disimpan di keadaan
     * server supaya pilihan buka/tutup bertahan setelah aksi Livewire.
     */
    public bool $bukaTautan = true;

    /**
     * Kata kunci pencarian tautan (slug/URL).
     */
    public string $pencarian = '';

    /**
     * Filter status tautan: '' | 'active' | 'inactive' | 'protected' | 'expired'.
     */
    public string $filterStatus = '';

    /**
     * Urutan daftar: 'terbaru' | 'klik' | 'abjad'.
     */
    public string $urutkan = 'terbaru';

    /**
     * Unduh CSV tautan yang terlihat pada filter saat ini.
     */
    public function eksporCsv(): void
    {
        $url = route('user.export.csv', [
            'search' => $this->pencarian,
            'status' => $this->filterStatus,
            'sort' => $this->urutkan,
        ]);

        $this->redirect($url);
    }

    public function lipatTautan(): void
    {
        $this->bukaTautan = ! $this->bukaTautan;
    }

    public function terapkanFilter(string $filter): void
    {
        $this->filterStatus = $filter;
    }
}
