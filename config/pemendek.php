<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tautan Pendek
    |--------------------------------------------------------------------------
    |
    | Konfigurasi domain pemendek tautan. Nilai bawaan mengikuti PRDapp.md:
    | QR boleh di-cache lama, sedangkan pembatas kata sandi 5 percobaan
    | dengan penguncian 60 detik.
    |
    */

    'qr_cache_seconds' => (int) env('PEMENDEK_QR_CACHE_SECONDS', 86400),

    'password_max_attempts' => (int) env('PEMENDEK_PASSWORD_MAX_ATTEMPTS', 5),

    'password_lock_seconds' => (int) env('PEMENDEK_PASSWORD_LOCK_SECONDS', 60),

    /*
    |--------------------------------------------------------------------------
    | Pemeriksaan Tautan Tujuan
    |--------------------------------------------------------------------------
    |
    | Tautan tujuan diperiksa ke Google Safe Browsing sebelum disimpan.
    | Tanpa kunci API, pemeriksaan dilewati sehingga pengembangan lokal dan
    | pengujian tidak memanggil layanan luar.
    |
    */

    'safe_browsing' => [
        'enabled' => filter_var(env('PEMENDEK_SAFE_BROWSING', true), FILTER_VALIDATE_BOOL),
        'api_key' => (string) env('PEMENDEK_SAFE_BROWSING_KEY', ''),
        'endpoint' => 'https://safebrowsing.googleapis.com/v4/threatMatches:find',
        'timeout_seconds' => (int) env('PEMENDEK_SAFE_BROWSING_TIMEOUT_SECONDS', 3),
    ],

];
