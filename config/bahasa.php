<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bahasa Antarmuka
    |--------------------------------------------------------------------------
    |
    | Daftar bahasa yang boleh dipilih pengguna. Kunci array dipakai sebagai
    | nilai locale Laravel, sedangkan nilainya ditampilkan pada pemilih bahasa.
    | Pilihan tamu disimpan di sesi; pilihan pengguna masuk disimpan pada
    | kolom `users.locale` sehingga tidak saling memengaruhi.
    |
    */

    'tersedia' => [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ],

    'bawaan' => env('APP_LOCALE', 'id'),

];
