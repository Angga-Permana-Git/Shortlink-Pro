<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $branding['nama'] }} — {{ __('ui.auth.judul_masuk') }}</title>
    @include('partials.tokens')
    @vite(['resources/css/publik/dasar.css', 'resources/css/publik/auth.css', 'resources/js/publik/auth.js'])
</head>
<body class="halaman-auth">
<main class="kartu-auth">
    <div class="kartu-auth__merek">
        @if ($branding['logo'])
            <img class="kartu-auth__logo" src="{{ $branding['logo'] }}" alt="">
        @endif
        <span class="kartu-auth__nama">{{ $branding['nama'] }}</span>
    </div>

    <h1 class="kartu-auth__judul">{{ __('ui.auth.masuk_judul') }}</h1>
    <p class="kartu-auth__catatan">{{ __('ui.auth.masuk_catatan') }}</p>

    @if ($errors->any())
        <div class="pesan-auth" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session('status'))
        <div class="pesan-auth pesan-auth--info" role="status">
            <p>{{ session('status') }}</p>
        </div>
    @endif

    <form class="form-auth" method="POST" action="{{ route('login.local') }}">
        @csrf
        <div>
            <label class="form-auth__label" for="username">{{ __('ui.auth.label_identifier') }}</label>
            <input class="form-auth__kontrol" id="username" type="text" name="username"
                   value="{{ old('username') }}" autocomplete="username" required autofocus>
        </div>
        <div>
            <label class="form-auth__label" for="password">{{ __('ui.auth.label_sandi') }}</label>
            <div class="form-auth__sandi">
                <input class="form-auth__kontrol form-auth__kontrol--sandi" id="password" type="password" name="password"
                       autocomplete="current-password" required>
                @include('partials.tombol-lihat-sandi', ['id' => 'password'])
            </div>
            <p class="form-auth__petunjuk"><a href="{{ route('password.request') }}">{{ __('ui.auth.lupa_sandi') }}</a></p>
        </div>
        <button class="tombol-auth" type="submit">{{ __('ui.umum.masuk') }}</button>
    </form>

    @include('partials.tombol-google')

    <div class="kartu-auth__kaki">
        <p>{{ __('ui.auth.belum_punya_akun') }} <a href="{{ route('register') }}">{{ __('ui.auth.daftar_gratis') }}</a></p>
        <a href="{{ url('/') }}">{{ __('ui.umum.kembali_beranda') }}</a>
        <div class="kartu-auth__bahasa">@include('partials.pemilih-bahasa')</div>
    </div>
</main>

<p class="kaki-auth">&copy; {{ date('Y') }} {{ $branding['nama'] }}</p>
</body>
</html>
