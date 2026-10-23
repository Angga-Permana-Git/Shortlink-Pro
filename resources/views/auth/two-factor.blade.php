<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $branding['nama'] }} - Verifikasi Dua Faktor</title>
    @include('partials.tokens')
    @vite(['resources/css/publik/dasar.css', 'resources/css/publik/auth.css'])
</head>
<body class="halaman-auth">
<main class="kartu-auth">
    <div class="kartu-auth__merek">
        <span class="kartu-auth__nama">{{ $branding['nama'] }}</span>
    </div>

    <h1 class="kartu-auth__judul">Verifikasi Dua Faktor</h1>
    <p class="kartu-auth__catatan">Masukkan kode 6 angka dari aplikasi autentikator Anda.</p>

    @if ($errors->any())
        <div class="pesan-auth" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form class="form-auth" method="POST" action="{{ route('admin.2fa.verify') }}">
        @csrf
        <div>
            <label class="form-auth__label" for="code">Kode verifikasi</label>
            <input class="form-auth__kontrol" id="code" type="text" name="code" inputmode="numeric"
                   maxlength="6" autocomplete="one-time-code" required autofocus>
        </div>
        <button class="tombol-auth" type="submit">Verifikasi</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="tombol-auth tombol-auth--sekunder">Keluar</button>
    </form>
</main>
</body>
</html>
