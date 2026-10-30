{{--
    Kerangka halaman status tautan (tidak ditemukan, tidak aktif, kedaluwarsa,
    dilindungi kata sandi, atau terlalu banyak percobaan).

    Isi tambahan seperti tombol atau formulir dikirim lewat slot.

    Halaman status sengaja noindex: slug pendek tidak punya nilai pencarian
    dan tidak boleh menghabiskan jatah crawl Google.
--}}
@props(['judul', 'pesan', 'namaIkon' => 'tautan'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', [
        'seoJudul' => $judul,
        'seoIndex' => false,
    ])
    @include('partials.tokens')
    @vite(['resources/css/publik/dasar.css', 'resources/css/publik/tautan.css', 'resources/js/publik/auth.js'])
</head>
<body class="halaman-tautan">
<main class="tautan">
    <span class="tautan__lambang" aria-hidden="true">
        <x-ikon :nama="$namaIkon" />
    </span>
    <h1 class="tautan__judul">{{ $judul }}</h1>
    <p class="tautan__pesan">{{ $pesan }}</p>
    {{ $slot }}
</main>
</body>
</html>
