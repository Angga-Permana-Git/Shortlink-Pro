{{--
    Kerangka halaman status tautan (tidak ditemukan, tidak aktif, kedaluwarsa,
    dilindungi kata sandi, atau terlalu banyak percobaan).

    Isi tambahan seperti tombol atau formulir dikirim lewat slot.
--}}
@props(['judul', 'pesan', 'namaIkon' => 'tautan'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }} — {{ $branding['nama'] }}</title>
    @include('partials.tokens')
    @vite(['resources/css/publik/dasar.css', 'resources/css/publik/tautan.css'])
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
