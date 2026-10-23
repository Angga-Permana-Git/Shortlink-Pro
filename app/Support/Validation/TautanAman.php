<?php

namespace App\Support\Validation;

use App\Services\ShortUrl\SafeBrowsingService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

/**
 * Menolak tautan tujuan yang terdaftar sebagai ancaman di Google Safe Browsing.
 *
 * Ditempatkan di lapisan validasi supaya galat muncul pada field "tautan
 * panjang" di semua jalur: beranda publik, API v1, panel pengguna, dan panel
 * admin. Pemeriksaan dilewati bila layanan tidak dikonfigurasi atau gagal
 * dihubungi, agar pemendekan tautan tidak ikut mati.
 */
class TautanAman implements ValidationRule
{
    private const LABEL_ANCAMAN = [
        'MALWARE' => 'malware',
        'SOCIAL_ENGINEERING' => 'phishing',
        'UNWANTED_SOFTWARE' => 'perangkat lunak tidak diinginkan',
        'POTENTIALLY_HARMFUL_APPLICATION' => 'aplikasi berpotensi berbahaya',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return;
        }

        $tipe = app(SafeBrowsingService::class)->periksa($value);

        if ($tipe === null) {
            return;
        }

        Log::warning('Tautan tujuan berbahaya ditolak.', [
            'host' => parse_url($value, PHP_URL_HOST),
            'ancaman' => $tipe,
        ]);

        $fail(
            'Tautan tujuan terdeteksi berbahaya oleh Google Safe Browsing ('
            .$this->label($tipe).') sehingga tidak bisa dipendekkan.'
        );
    }

    private function label(string $tipe): string
    {
        return self::LABEL_ANCAMAN[$tipe] ?? 'ancaman keamanan';
    }
}
