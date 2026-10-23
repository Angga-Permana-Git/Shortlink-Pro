<?php

/**
 * Pelengkap terjemahan bawaan paket Filament untuk locale id.
 *
 * Dua kunci di bawah belum ada di vendor/filament/tables/resources/lang/id/table.php,
 * sehingga tanpa berkas ini Filament menampilkan teks Inggris dari locale en
 * (mis. "No results" pada pembaca layar). Laravel menggabungkan berkas ini di atas
 * terjemahan bawaan paket melalui mekanisme loadNamespaceOverrides.
 */
return [

    'loading' => 'Memuat...',

    'result_count' => '{0} Tidak ada hasil|{1} :count hasil|[2,*] :count hasil',

];
