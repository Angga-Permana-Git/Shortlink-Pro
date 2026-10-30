<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $branding['nama'] }} — {{ __('ui.auth.daftar_judul') }}</title>
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

    <h1 class="kartu-auth__judul">{{ __('ui.auth.daftar_judul') }}</h1>
    <p class="kartu-auth__catatan">{{ __('ui.auth.daftar_catatan') }}</p>

    @if ($errors->any())
        <div class="pesan-auth" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form class="form-auth" method="POST" action="{{ route('register.store') }}">
        @csrf
        <div>
            <label class="form-auth__label" for="name">{{ __('ui.auth.label_nama') }}</label>
            <input class="form-auth__kontrol" id="name" type="text" name="name"
                   value="{{ old('name') }}" placeholder="{{ __('ui.auth.contoh_nama') }}"
                   autocomplete="name" required autofocus>
        </div>
        <div>
            <label class="form-auth__label" for="username">{{ __('ui.auth.label_username') }}</label>
            <input class="form-auth__kontrol" id="username" type="text" name="username"
                   value="{{ old('username') }}" placeholder="{{ __('ui.auth.contoh_username') }}"
                   pattern="[A-Za-z0-9_-]+" title="{{ __('ui.auth.petunjuk_username') }}"
                   aria-describedby="petunjuk-username"
                   autocapitalize="none" spellcheck="false" autocomplete="username" required>
            <p class="form-auth__petunjuk" id="petunjuk-username">{{ __('ui.auth.petunjuk_username') }}</p>
        </div>
        <div>
            <label class="form-auth__label" for="email">{{ __('ui.auth.label_email') }}</label>
            <input class="form-auth__kontrol" id="email" type="email" name="email"
                   value="{{ old('email') }}" placeholder="{{ __('ui.auth.contoh_email') }}"
                   autocomplete="email" required>
        </div>
        <div>
            <label class="form-auth__label" for="password">{{ __('ui.auth.label_sandi_baru') }}</label>
            <div class="form-auth__sandi">
                <input class="form-auth__kontrol form-auth__kontrol--sandi" id="password" type="password"
                       name="password" minlength="12" autocomplete="new-password"
                       aria-describedby="petunjuk-sandi" required>
                @include('partials.tombol-lihat-sandi', ['id' => 'password'])
            </div>
            <p class="form-auth__petunjuk" id="petunjuk-sandi">{{ __('ui.auth.petunjuk_sandi') }}</p>
        </div>
        <div>
            <label class="form-auth__label" for="password_confirmation">{{ __('ui.auth.label_ulangi_sandi') }}</label>
            <div class="form-auth__sandi">
                <input class="form-auth__kontrol form-auth__kontrol--sandi" id="password_confirmation"
                       type="password" name="password_confirmation" autocomplete="new-password" required>
                @include('partials.tombol-lihat-sandi', ['id' => 'password_confirmation'])
            </div>
        </div>
        <button class="tombol-auth" type="submit">{{ __('ui.auth.tombol_daftar') }}</button>
    </form>

    <div class="kartu-auth__kaki">
        <p>{{ __('ui.auth.sudah_punya_akun') }} <a href="{{ route('login') }}">{{ __('ui.umum.masuk') }}</a></p>
        <a href="{{ url('/') }}">{{ __('ui.umum.kembali_beranda') }}</a>
        <div class="kartu-auth__bahasa">@include('partials.pemilih-bahasa')</div>
    </div>
</main>

<p class="kaki-auth">&copy; {{ date('Y') }} {{ $branding['nama'] }}</p>
</body>
</html>
