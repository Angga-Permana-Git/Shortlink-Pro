<?php

namespace App\Support\Validation;

use App\Services\Setting\OperationalSettingsService;
use Closure;

/**
 * Aturan validasi tautan pendek.
 *
 * Dipakai bersama oleh formulir publik, halaman panel pengguna, dan formulir
 * perubahan tautan milik pengguna supaya bentuk data yang diterima
 * ShortUrlService hanya ditulis di satu tempat.
 */
final class AturanTautanPendek
{
    public const POLA_SLUG = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*$/';

    public const PANJANG_SLUG_MAKS = 100;

    public const PANJANG_URL_MAKS = 2048;

    /**
     * Kunci aturan memakai nama kolom kanonik (`destination_url`, `slug`,
     * `expires_at`, `password`). Halaman panel menyimpan isian formulir di
     * dalam properti `data`, sehingga memanggilnya dengan awalan `data.`.
     *
     * `$pindaiTujuan` dimatikan oleh pengimpor CSV: pemeriksaan Safe Browsing
     * memanggil layanan luar per baris, sedangkan impor bisa mencapai ribuan
     * baris dan dibatasi kuota serta waktu proses.
     *
     * @return array<string, array<int, string|TautanAman>>
     */
    public static function semua(string $awalan = '', bool $pindaiTujuan = true): array
    {
        return [
            $awalan.'destination_url' => [
                'required',
                'url:http,https',
                'max:'.self::PANJANG_URL_MAKS,
                ...($pindaiTujuan ? [new TautanAman] : []),
            ],
            $awalan.'slug' => [
                'nullable',
                'string',
                'max:'.self::PANJANG_SLUG_MAKS,
                'regex:'.self::POLA_SLUG,
                'unique:short_urls,slug',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $dicadangkan = app(OperationalSettingsService::class)->reservedSlugs();

                    if (in_array(mb_strtolower($value), $dicadangkan, true)) {
                        $fail('Nama pendek "'.$value.'" dicadangkan oleh admin.');
                    }
                },
            ],
            $awalan.'expires_at' => ['nullable', 'date', 'after:now'],
            $awalan.'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }

    /**
     * Aturan perubahan tautan yang sudah ada.
     *
     * `slug` sengaja tidak ikut: alamat pendek yang sudah dibagikan (beserta
     * QR-nya) akan mati bila slug berubah. `protected` ikut divalidasi supaya
     * kuncinya tetap ada setelah data tervalidasi diteruskan ke
     * UpdateShortUrlDTO.
     *
     * Tujuan tetap diperiksa ulang: tanpa itu tautan yang sudah aman bisa
     * dialihkan ke alamat berbahaya setelah dibuat.
     *
     * @return array<string, array<int, string|TautanAman>>
     */
    public static function perubahan(string $awalan = ''): array
    {
        return [
            $awalan.'destination_url' => [
                'required',
                'url:http,https',
                'max:'.self::PANJANG_URL_MAKS,
                new TautanAman,
            ],
            $awalan.'is_active' => ['boolean'],
            $awalan.'protected' => ['boolean'],
            $awalan.'expires_at' => ['nullable', 'date', 'after:now'],
            $awalan.'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }

    /**
     * Nama field yang dipakai di pesan galat, supaya pengguna membaca
     * "tautan panjang" alih-alih "destination url".
     *
     * @return array<string, string>
     */
    public static function atribut(string $awalan = ''): array
    {
        return [
            $awalan.'destination_url' => 'tautan panjang',
            $awalan.'slug' => 'nama pendek',
            $awalan.'is_active' => 'status aktif',
            $awalan.'protected' => 'proteksi kata sandi',
            $awalan.'expires_at' => 'waktu kedaluwarsa',
            $awalan.'password' => 'kata sandi',
        ];
    }
}
