<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', [
        'seoJudul' => __('ui.auth.lupa_judul'),
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

    <h1 class="kartu-auth__judul">{{ __('ui.auth.lupa_judul') }}</h1>
    <p class="kartu-auth__catatan">{{ __('ui.auth.lupa_catatan') }}</p>

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

    {{-- Hanya terisi saat mailer `log` (pengembangan), jadi di produksi tidak muncul. --}}
    @if (session('tautan_reset'))
        <div class="pesan-auth pesan-auth--info" role="status">
            <p>Email tidak benar-benar terkirim karena pengiriman masih memakai mailer <code>log</code>. Tautan pengujian:</p>
            <p><a href="{{ session('tautan_reset') }}">{{ session('tautan_reset') }}</a></p>
        </div>
    @endif

    <form class="form-auth" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div>
            <label class="form-auth__label" for="email">{{ __('ui.auth.label_email') }}</label>
            <input class="form-auth__kontrol" id="email" type="email" name="email"
                   value="{{ old('email') }}" autocomplete="email" required autofocus>
        </div>
        <button class="tombol-auth" type="submit">{{ __('ui.auth.tombol_kirim') }}</button>
    </form>

    @include('partials.tombol-google')

    <div class="kartu-auth__kaki">
        <p><a href="{{ route('login.local') }}">{{ __('ui.umum.masuk') }}</a></p>
        <a href="{{ url('/') }}">{{ __('ui.umum.kembali_beranda') }}</a>
        <div class="kartu-auth__bahasa">@include('partials.pemilih-bahasa')</div>
    </div>
</main>

<p class="kaki-auth">&copy; {{ date('Y') }} {{ $branding['nama'] }}</p>
</body>
</html>
