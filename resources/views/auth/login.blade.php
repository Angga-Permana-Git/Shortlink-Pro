<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', [
        'seoJudul' => __('ui.auth.judul_masuk'),
        'seoDeskripsi' => __('ui.beranda.deskripsi'),
        'seoCanonical' => route('login'),
        'seoIndex' => false,
    ])
    @include('partials.tokens')
    @vite(['resources/css/publik/dasar.css', 'resources/css/publik/auth.css'])
</head>
<body class="halaman-auth">
<main class="kartu-auth">
    <div class="kartu-auth__merek">
        @if ($branding['logo'])
            <img class="kartu-auth__logo" src="{{ $branding['logo'] }}" alt="">
        @endif
        <span class="kartu-auth__nama">{{ $branding['nama'] }}</span>
    </div>

    <h1 class="kartu-auth__judul">{{ __('ui.auth.masuk_sso_judul') }}</h1>
    <p class="kartu-auth__catatan">{{ __('ui.auth.masuk_sso_catatan') }}</p>

    @if ($errors->any())
        <div class="pesan-auth" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (app(\App\Services\Auth\KeycloakAuthService::class)->isConfigured())
        <a class="tombol-auth" href="{{ route('keycloak.redirect') }}">{{ __('ui.auth.masuk_sso_tombol') }}</a>
    @else
        <div class="pesan-auth pesan-auth--info" role="status">
            <p>{{ __('ui.auth.sso_belum') }}</p>
        </div>
    @endif

    @include('partials.tombol-google')

    <div class="kartu-auth__kaki">
        <a href="{{ route('login.local') }}">{{ __('ui.auth.masuk_lokal_tautan') }}</a>
        <div class="kartu-auth__bahasa">@include('partials.pemilih-bahasa')</div>
    </div>
</main>

<p class="kaki-auth">&copy; {{ date('Y') }} {{ $branding['nama'] }}</p>
</body>
</html>
