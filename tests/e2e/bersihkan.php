<?php

/**
 * Bersihkan data uji E2E.
 *
 * Dipanggil manual setelah `npx e2e run`:
 *   C:\xampp\php\php.exe tests/e2e/bersihkan.php
 *
 * Hanya menghapus data yang dibuat uji (penanda @e2e.test dan tujuan contoh),
 * bukan data pengguna nyata.
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tujuanUji = [
    'https://contoh.e2e/%',
    'https://contoh-api.e2e/%',
    'https://example.com/kebijakan',
    'https://example.com/qr',
];

$idPengguna = DB::table('users')->where('email', 'like', '%@e2e.test')->pluck('id');

$tautanPengguna = 0;
if ($idPengguna->isNotEmpty()) {
    $tautanPengguna = DB::table('short_urls')->whereIn('owner_id', $idPengguna)->delete();
    DB::table('sessions')->whereIn('user_id', $idPengguna)->delete();
}

$tautanTamu = 0;
foreach ($tujuanUji as $pola) {
    $tautanTamu += DB::table('short_urls')
        ->whereNull('owner_id')
        ->where('destination_url', 'like', $pola)
        ->delete();
}

$pengguna = $idPengguna->isNotEmpty()
    ? DB::table('users')->whereIn('id', $idPengguna)->delete()
    : 0;

echo "Tautan pengguna uji dihapus: {$tautanPengguna}\n";
echo "Tautan tamu uji dihapus: {$tautanTamu}\n";
echo "Pengguna uji dihapus: {$pengguna}\n";
