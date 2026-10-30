@php
    $domainPendek = parse_url(config('app.url'), PHP_URL_HOST);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo', [
        'seoJudul' => __('ui.beranda.judul'),
        'seoDeskripsi' => __('ui.beranda.deskripsi'),
        'seoCanonical' => url('/'),
    ])
    @include('partials.tokens')
    @vite(['resources/css/publik/dasar.css', 'resources/css/publik/beranda.css'])
</head>
<body class="halaman-beranda">
@if (filled($pengumuman ?? null))
    <div class="bilah-pengumuman" role="status">{{ $pengumuman }}</div>
@endif
<header class="bilah-atas">
    <div class="bilah-atas__merek">
        @if ($branding['logo'])
            <img class="bilah-atas__logo" src="{{ $branding['logo'] }}" alt="">
        @else
            <span class="bilah-atas__lambang" aria-hidden="true">S</span>
        @endif
        <span>{{ $branding['nama'] }}</span>
    </div>
    <nav class="bilah-atas__aksi" aria-label="{{ __('ui.beranda.nav_akun') }}">
        @include('partials.pemilih-bahasa')

        @auth
            <a href="{{ url('/user') }}" class="bilah-atas__tautan bilah-atas__tautan--padat">{{ __('ui.panel.dashboard') }}</a>
            <form class="bilah-atas__form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bilah-atas__tautan bilah-atas__tautan--garis bilah-atas__tombol">{{ __('ui.umum.keluar') }}</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="bilah-atas__tautan bilah-atas__tautan--garis">{{ __('ui.umum.masuk') }}</a>
            <a href="{{ route('register') }}" class="bilah-atas__tautan bilah-atas__tautan--padat">{{ __('ui.umum.daftar_gratis') }}</a>
        @endauth
    </nav>
</header>

<main class="isi-beranda">
    <section class="pahlawan">
        <h1 class="pahlawan__judul">{{ __('ui.beranda.hero_1') }}<br>{{ __('ui.beranda.hero_2') }}</h1>
        <p class="pahlawan__catatan">{{ __('ui.beranda.hero_catatan') }}</p>
    </section>

    <section class="kartu-pendek">
        @if (session('short_url'))
            <div class="hasil" role="status">
                <p class="hasil__label">
                    <x-ikon nama="cek" />
                    {{ __('ui.beranda.siap') }}
                </p>
                <div class="hasil__baris">
                    <a class="hasil__tautan" href="{{ session('short_url') }}" target="_blank" rel="noopener">{{ session('short_url') }}</a>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="pesan-galat" role="alert">{{ $errors->first() }}</div>
        @endif

        <form id="formPendek" action="{{ route('shorten.public') }}" method="POST" data-hook="formulir">
            @csrf
            <div class="kolom-tautan">
                <x-ikon nama="tautan" kelas="ikon kolom-tautan__ikon" />
                <label class="sr-only" for="destination_url">{{ __('ui.beranda.label_url') }}</label>
                <input class="kolom-tautan__input" id="destination_url" type="url" name="destination_url"
                       placeholder="{{ __('ui.beranda.placeholder_url') }}" required autocomplete="off">
            </div>

            <fieldset class="pilihan">
                <legend class="pilihan__judul">{{ __('ui.beranda.fitur') }}</legend>
                <div class="pilihan__daftar">
                    <label class="pilihan__chip">
                        <input type="checkbox" class="pilihan__kotak" data-panel="panelKustom">
                        <x-ikon nama="tautan" />
                        <span>{{ __('ui.beranda.kustom') }}</span>
                    </label>
                    <label class="pilihan__chip">
                        <input type="checkbox" class="pilihan__kotak" data-panel="panelSandi">
                        <x-ikon nama="kunci" />
                        <span>{{ __('ui.beranda.sandi') }}</span>
                    </label>
                    <label class="pilihan__chip">
                        <input type="checkbox" class="pilihan__kotak" data-panel="panelKedaluwarsa">
                        <x-ikon nama="jam" />
                        <span>{{ __('ui.beranda.kedaluwarsa') }}</span>
                    </label>
                    <label class="pilihan__chip">
                        <input type="checkbox" class="pilihan__kotak" data-hook="pilihan-qr" checked>
                        <x-ikon nama="kotak" />
                        <span>{{ __('ui.beranda.qr') }}</span>
                    </label>
                </div>
            </fieldset>

            <div class="panel-opsi" id="panelKustom" hidden>
                <label class="panel-opsi__label" for="slug">{{ __('ui.beranda.nama_kustom') }}</label>
                <div class="panel-opsi__awalan">
                    <span class="panel-opsi__domain">{{ $domainPendek }}/</span>
                    <input class="panel-opsi__kontrol" id="slug" name="slug" type="text" placeholder="{{ __('ui.beranda.placeholder_nama') }}"
                           maxlength="100" pattern="[a-zA-Z0-9][a-zA-Z0-9\-_]*" autocomplete="off">
                </div>
            </div>

            <div class="panel-opsi" id="panelSandi" hidden>
                <label class="panel-opsi__label" for="password">{{ __('ui.beranda.sandi_tautan') }}</label>
                <input class="panel-opsi__kontrol" id="password" name="password" type="password" minlength="8"
                       maxlength="255" placeholder="{{ __('ui.beranda.placeholder_sandi') }}" autocomplete="new-password">
            </div>

            <div class="panel-opsi" id="panelKedaluwarsa" hidden>
                <label class="panel-opsi__label" for="expires_at">{{ __('ui.beranda.berakhir') }}</label>
                <input class="panel-opsi__kontrol" id="expires_at" name="expires_at" type="datetime-local">
            </div>

            <button type="submit" class="tombol-utama" data-hook="tombol-pendek">
                <span data-hook="label-tombol">{{ __('ui.beranda.tombol') }}</span>
                <x-ikon nama="panah" />
            </button>
        </form>

        <div class="hasil" data-hook="panel-hasil" hidden aria-live="polite">
            <p class="hasil__label">
                <x-ikon nama="cek" />
                {{ __('ui.beranda.siap') }}
            </p>
            <div class="hasil__baris">
                <a class="hasil__tautan" data-hook="hasil-tautan" href="#" target="_blank" rel="noopener"></a>
                <button type="button" class="tombol-kecil" data-hook="tombol-salin">
                    <x-ikon nama="salin" />
                    <span data-hook="label-salin">{{ __('ui.umum.salin') }}</span>
                </button>
            </div>
            <div class="hasil__qr" data-hook="hasil-qr" hidden>
                <img class="hasil__qr-gambar" data-hook="gambar-qr" src="" alt="{{ __('ui.beranda.alt_qr') }}" width="128" height="128">
                <a class="tombol-kecil" data-hook="unduh-qr" href="#" download="qr-tautan-pendek.svg">
                    <x-ikon nama="unduh" />
                    {{ __('ui.umum.unduh_qr') }}
                </a>
            </div>
            <p class="hasil__catatan" data-hook="catatan-hasil" hidden></p>
        </div>

        <div class="pesan-galat" data-hook="pesan-galat" hidden role="alert"></div>
    </section>

    <section class="promo">
        <div class="promo__teks">
            <p class="promo__label">{{ __('ui.beranda.promo_label') }}</p>
            <h2 class="promo__judul">{{ __('ui.beranda.promo_judul') }}</h2>
            <p class="promo__deskripsi">
                {{ __('ui.beranda.promo_awal') }}
                <span class="promo__alamat">{{ $domainPendek }}/u/nama-anda</span>.
                {{ __('ui.beranda.promo_akhir') }}
            </p>
        </div>
        <ul class="promo__daftar">
            <li class="promo__item"><span class="promo__nomor">01</span><span>{{ __('ui.beranda.promo_1') }}</span></li>
            <li class="promo__item"><span class="promo__nomor">02</span><span>{{ __('ui.beranda.promo_2') }}</span></li>
            <li class="promo__item"><span class="promo__nomor">03</span><span>{{ __('ui.beranda.promo_3') }}</span></li>
            <li class="promo__item"><span class="promo__nomor">04</span><span>{{ __('ui.beranda.promo_4') }}</span></li>
        </ul>
    </section>
</main>

<footer class="kaki">© {{ date('Y') }} {{ $branding['nama'] }}</footer>

@vite('resources/js/publik/beranda.js')
</body>
</html>
