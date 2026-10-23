<?php

namespace App\Services\Dashboard;

use App\Models\User;

/**
 * Ekspor daftar tautan pengguna ke CSV.
 *
 * Filter (pencarian, status, urutkan) sama dengan yang dipakai halaman daftar,
 * sehingga hasil CSV selalu sesuai tampilan yang terlihat.
 */
class UserEksporTautanService
{
    public function __construct(
        private readonly UserDaftarTautanService $daftar,
    ) {}

    public function eksporCsv(User $pengguna, string $pencarian = '', string $filterStatus = '', string $urutkan = 'terbaru'): string
    {
        $daftar = $this->daftar->daftar($pengguna, $pencarian, $filterStatus, $urutkan);

        $baris = [['Nama Pendek', 'URL Tujuan', 'Total Klik', 'Status', 'Kedaluwarsa', 'Bersandi', 'Di Linktree']];

        foreach ($daftar as $tautan) {
            $baris[] = [
                $tautan['slug'],
                $tautan['tujuan'],
                $tautan['klik'],
                $tautan['status_teks'],
                $tautan['kedaluwarsa'] ? $tautan['kedaluwarsa']->format('Y-m-d H:i') : '',
                $tautan['bersandi'] ? 'Ya' : 'Tidak',
                $tautan['dipilih'] ? 'Ya' : 'Tidak',
            ];
        }

        $csv = fopen('php://temp', 'r+');
        foreach ($baris as $barisData) {
            fputcsv($csv, $barisData, ';');
        }
        rewind($csv);
        $hasil = stream_get_contents($csv);
        fclose($csv);

        return $hasil;
    }
}
