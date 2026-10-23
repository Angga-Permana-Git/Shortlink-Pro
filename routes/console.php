<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jejak lama dibersihkan otomatis; batasnya mengikuti Pengaturan Operasional.
Schedule::command('logs:purge')->dailyAt('02:00');

// Peringatan operasional dikirim ke admin tanpa membanjiri notifikasi.
Schedule::command('admin:notify-attention')->hourlyAt(5);

// Denyut untuk widget status: membuktikan penjadwal benar-benar berjalan.
Schedule::call(fn () => Cache::put('jadwal_terakhir', now()->toIso8601String(), now()->addMinutes(5)))
    ->everyMinute()
    ->name('denyut-jadwal');
