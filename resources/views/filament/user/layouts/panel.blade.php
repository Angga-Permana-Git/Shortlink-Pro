{{--
    Layout halaman tunggal pengguna.

    Kerangkanya sengaja meniru halaman beranda (bilah atas, latar gradien,
    kolom isi, kaki halaman) supaya satu-satunya perbedaan dengan beranda adalah
    kartunya. Kelas kerangka, primitif kartu, dan komponen formulir dipakai ulang
    dari beranda.css; gaya yang khas halaman ini ada di panel.css.

    Gaya bawaan Filament sengaja tidak dimuat karena halaman ini tidak memakai
    satu pun komponen Filament, sedangkan Tailwind preflight di dalamnya akan
    mengubah tampilan (bobot judul, garis bawah tautan) sehingga berbeda dari
    beranda. Skrip Filament tetap dimuat karena halaman ini komponen Livewire.

    Tidak ada sidebar maupun navigasi panel, jadi pengguna tidak berpindah
    halaman: seluruh alurnya ada di dalam kartu-kartu pada lembar ini.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $livewire->getTitle() }} — {{ $branding['nama'] }}</title>
    @include('partials.tokens')
    @vite([
        'resources/css/publik/dasar.css',
        'resources/css/publik/beranda.css',
        'resources/css/publik/panel.css',
        'resources/css/publik/linktree-publik.css',
    ])
</head>
<body class="halaman-beranda">
@if (filled($pengumuman ?? null))
    <div class="bilah-pengumuman" role="status">{{ $pengumuman }}</div>
@endif
@if (session('impersonator_id'))
    <div class="bilah-impersonasi" role="status">
        <span>{{ __('ui.panel.masuk_sebagai', ['nama' => auth()->user()->name, 'admin' => session('impersonator_name')]) }}</span>
        <form class="bilah-impersonasi__form" action="{{ route('admin.impersonate.stop') }}" method="POST">
            @csrf
            <button type="submit" class="bilah-impersonasi__tombol">{{ __('ui.panel.kembali_admin') }}</button>
        </form>
    </div>
@endif
<header class="bilah-atas">
    <div class="bilah-atas__merek">
        @if ($branding['logo'])
            <img class="bilah-atas__logo" src="{{ $branding['logo'] }}" alt="">
        @else
            <span class="bilah-atas__lambang" aria-hidden="true">{{ mb_strtoupper(mb_substr($branding['nama'], 0, 1)) }}</span>
        @endif
        <span>{{ $branding['nama'] }}</span>
    </div>
    <div class="bilah-atas__aksi">
        @include('partials.pemilih-bahasa')
        <form class="bilah-atas__form" action="{{ route('filament.user.auth.logout') }}" method="POST">
            @csrf
            <button type="submit" class="bilah-atas__tautan bilah-atas__tautan--garis bilah-atas__tombol">{{ __('ui.umum.keluar') }}</button>
        </form>
    </div>
</header>

<main class="isi-beranda">
    {{ $slot }}
</main>

<footer class="kaki">© {{ date('Y') }} {{ $branding['nama'] }}</footer>

@filamentScripts(withCore: true)
@vite('resources/js/publik/panel.js')
</body>
</html>
