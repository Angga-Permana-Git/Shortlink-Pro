<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', [
        'seoJudul' => $profile->display_name,
        'seoDeskripsi' => $profile->bio ?: $profile->display_name,
        'seoCanonical' => route('linktree.show', $profile->public_slug),
    ])
    @include('partials.tokens')
    @vite(['resources/css/publik/dasar.css', 'resources/css/publik/linktree-publik.css'])
</head>
<body class="halaman-profil">
<main class="profil">
    <header>
        <div class="profil__lambang" aria-hidden="true">
            @if ($logo)
                <img class="profil__lambang-gambar" src="{{ $logo }}" alt="">
            @else
                {{ mb_strtoupper(mb_substr($profile->display_name, 0, 1)) }}
            @endif
        </div>
        <h1 class="profil__nama">{{ $profile->display_name }}</h1>
        @if ($profile->bio)
            <p class="profil__bio">{{ $profile->bio }}</p>
        @endif
    </header>

    <ul class="profil__daftar">
        @forelse ($profile->links as $link)
            <li>
                <a class="profil__tombol" href="{{ route('linktree.click', [$profile->public_slug, $link]) }}" target="_blank" rel="noopener noreferrer nofollow">
                    <x-ikon nama="tautan" />
                    <span>{{ $link->label }}</span>
                </a>
            </li>
        @empty
            <li class="profil__kosong">{{ __('ui.linktree.kosong') }}</li>
        @endforelse
    </ul>

    <footer class="profil__kaki">
        <a href="{{ url('/') }}">{{ __('ui.linktree.dibuat_dengan', ['nama' => $branding['nama']]) }}</a>
    </footer>
</main>
</body>
</html>
