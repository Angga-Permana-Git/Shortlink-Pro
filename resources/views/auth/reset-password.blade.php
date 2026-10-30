<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', [
        'seoJudul' => __('ui.auth.reset_judul'),
        'seoIndex' => false,
    ])
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

    <h1 class="kartu-auth__judul">{{ __('ui.auth.reset_judul') }}</h1>
    <p class="kartu-auth__catatan">{{ $email ?: '' }}</p>

    @if ($errors->any())
        <div class="pesan-auth" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form class="form-auth" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="form-auth__label" for="email">{{ __('ui.auth.label_email') }}</label>
            <input class="form-auth__kontrol" id="email" type="email" name="email"
                   value="{{ old('email', $email) }}" autocomplete="email" readonly>
        </div>
        <div>
            <label class="form-auth__label" for="password">{{ __('ui.auth.label_sandi_baru2') }}</label>
            <div class="form-auth__sandi">
                <input class="form-auth__kontrol form-auth__kontrol--sandi" id="password" type="password" name="password"
                       autocomplete="new-password" required autofocus>
                @include('partials.tombol-lihat-sandi', ['id' => 'password'])
            </div>
        </div>
        <div>
            <label class="form-auth__label" for="password_confirmation">{{ __('ui.auth.label_ulangi_sandi_baru') }}</label>
            <div class="form-auth__sandi">
                <input class="form-auth__kontrol form-auth__kontrol--sandi" id="password_confirmation" type="password"
                       name="password_confirmation" autocomplete="new-password" required>
                @include('partials.tombol-lihat-sandi', ['id' => 'password_confirmation'])
            </div>
        </div>
        <button class="tombol-auth" type="submit">{{ __('ui.auth.tombol_simpan_sandi') }}</button>
    </form>

    <div class="kartu-auth__kaki">
        <a href="{{ route('password.request') }}">{{ __('ui.auth.minta_tautan_baru') }}</a>
        <div class="kartu-auth__bahasa">@include('partials.pemilih-bahasa')</div>
    </div>
</main>

<p class="kaki-auth">&copy; {{ date('Y') }} {{ $branding['nama'] }}</p>
</body>
</html>
