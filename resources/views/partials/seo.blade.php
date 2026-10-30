{{--
    Meta SEO terpusat untuk halaman publik.

    Dipakai semua halaman non-panel: beranda, auth, linktree publik, dan
    halaman status tautan. Menerima:
      - $seoJudul: judul tanpa nama brand (brand ditambah di sini)
      - $seoDeskripsi: meta description (sudah diterjemahkan pemanggil)
      - $seoCanonical: URL kanonis absolut (tanpa query string)
      - $seoIndex: bool, false untuk halaman yang tidak boleh diindeks
--}}
@php
    $seoJudul ??= trim($__env->yieldContent('judul_halaman', ''));
    $seoJudulLengkap = $seoJudul !== '' ? $seoJudul.' — '.$branding['nama'] : $branding['nama'];
    $seoIndex ??= true;
@endphp
<title>{{ $seoJudulLengkap }}</title>
@if (isset($seoDeskripsi) && $seoDeskripsi !== '')
<meta name="description" content="{{ $seoDeskripsi }}">
@endif
@if (isset($seoCanonical) && $seoCanonical !== '')
<link rel="canonical" href="{{ $seoCanonical }}">
@endif
<meta name="robots" content="{{ $seoIndex ? 'index, follow' : 'noindex, nofollow' }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $branding['nama'] }}">
<meta property="og:title" content="{{ $seoJudulLengkap }}">
@if (isset($seoDeskripsi) && $seoDeskripsi !== '')
<meta property="og:description" content="{{ $seoDeskripsi }}">
@endif
@if (isset($seoCanonical) && $seoCanonical !== '')
<meta property="og:url" content="{{ $seoCanonical }}">
@endif
@if (! empty($branding['logo']))
<meta property="og:image" content="{{ $branding['logo'] }}">
@endif
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $seoJudulLengkap }}">
@if (isset($seoDeskripsi) && $seoDeskripsi !== '')
<meta name="twitter:description" content="{{ $seoDeskripsi }}">
@endif
