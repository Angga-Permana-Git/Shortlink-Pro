<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', [
        'seoJudul' => $halaman === 'tentang' ? __('ui.legal.tentang_judul') : ($halaman === 'syarat' ? __('ui.legal.syarat_judul') : __('ui.legal.privasi_judul')),
        'seoDeskripsi' => $halaman === 'tentang' ? __('ui.legal.tentang_deskripsi') : ($halaman === 'syarat' ? __('ui.legal.syarat_deskripsi') : __('ui.legal.privasi_deskripsi')),
        'seoCanonical' => $halaman === 'tentang' ? route('legal.tentang') : ($halaman === 'syarat' ? route('legal.syarat') : route('legal.privasi')),
    ])
    @include('partials.tokens')
    @vite(['resources/css/publik/dasar.css', 'resources/css/publik/beranda.css'])
    <style>
        .legal { max-width: 700px; margin-inline: auto; background: #fff; color: #1f2937; border-radius: 1rem; padding: 2rem; line-height: 1.6; box-shadow: var(--bayang-smart); }
        .legal h1 { color: var(--warna-smart-navy); line-height: 1.3; margin-top: 0; }
        .legal h2 { color: var(--warna-smart-navy); margin-top: 1.75rem; }
        .legal a { color: var(--warna-smart-navy); font-weight: 600; }
        .legal ul, .legal ol { padding-left: 1.25rem; }
        .legal li { margin-bottom: .5rem; }
    </style>
</head>
<body class="halaman-beranda">
<main class="isi-beranda">
    <div class="legal">
        @if ($halaman === 'tentang')
            <h1>{{ __('ui.legal.tentang_judul') }}</h1>
            <p><strong>{{ __('ui.legal.tentang_salam') }}</strong></p>
            <p>{{ __('ui.legal.tentang_p1') }}</p>
            <p>{{ __('ui.legal.tentang_p2') }}</p>
            <p>{{ __('ui.legal.tentang_p3') }}</p>
            <h2>{{ __('ui.legal.tentang_subjudul') }}</h2>
            <p>{{ __('ui.legal.tentang_p4') }}</p>
            <ul>
                <li>{{ __('ui.legal.tentang_f1') }}</li>
                <li>{{ __('ui.legal.tentang_f2') }}</li>
                <li>{{ __('ui.legal.tentang_f3') }}</li>
            </ul>
            <p>{{ __('ui.legal.tentang_p5') }}</p>
            <p><strong>{{ __('ui.legal.tentang_hubung') }}</strong><br>
                @php $email = 'angga.permana.n.n@gmail.com'; @endphp
                {{ __('ui.legal.tentang_email', ['email' => '']) }}<a href="mailto:{{ $email }}">{{ $email }}</a>
            </p>
            <p>{{ __('ui.legal.tentang_penutup') }}</p>
            <p><a class="tombol-kecil" href="{{ url('/') }}">{{ __('ui.legal.tentang_tombol') }} ➔</a></p>
        @elseif ($halaman === 'syarat')
            <h1>{{ __('ui.legal.syarat_judul') }}</h1>
            <p>{{ __('ui.legal.diperbarui', ['tanggal' => $tanggal]) }}</p>
            <p>{{ __('ui.legal.syarat_intro', ['domain' => $domain]) }}</p>
            <h2>{{ __('ui.legal.syarat_1j') }}</h2>
            <p>{{ __('ui.legal.syarat_1i') }}</p>
            <h2>{{ __('ui.legal.syarat_2j') }}</h2>
            <p>{{ __('ui.legal.syarat_2i') }}</p>
            <h2>{{ __('ui.legal.syarat_3j') }}</h2>
            <p>{{ __('ui.legal.syarat_3i') }}</p>
            <ul>
                <li>{{ __('ui.legal.syarat_3a') }}</li>
                <li>{{ __('ui.legal.syarat_3b') }}</li>
                <li>{{ __('ui.legal.syarat_3c') }}</li>
                <li>{{ __('ui.legal.syarat_3d') }}</li>
                <li>{{ __('ui.legal.syarat_3e') }}</li>
            </ul>
            <p>{{ __('ui.legal.syarat_3catatan') }}</p>
            <h2>{{ __('ui.legal.syarat_4j') }}</h2>
            <ul>
                <li>{{ __('ui.legal.syarat_4a') }}</li>
                <li>{{ __('ui.legal.syarat_4b') }}</li>
                <li>{{ __('ui.legal.syarat_4c') }}</li>
            </ul>
            <h2>{{ __('ui.legal.syarat_5j') }}</h2>
            <p>{{ __('ui.legal.syarat_5i') }}</p>
            <ul>
                <li>{{ __('ui.legal.syarat_5a') }}</li>
                <li>{{ __('ui.legal.syarat_5b') }}</li>
                <li>{{ __('ui.legal.syarat_5c') }}</li>
            </ul>
            <h2>{{ __('ui.legal.syarat_6j') }}</h2>
            <p>{{ __('ui.legal.syarat_6i') }}</p>
            <h2>{{ __('ui.legal.syarat_7j') }}</h2>
            <p>{{ __('ui.legal.syarat_7i') }}</p>
            <h2>{{ __('ui.legal.syarat_8j') }}</h2>
            <p>{{ __('ui.legal.syarat_8i') }}</p>
            @php $email = 'angga.permana.n.n@gmail.com'; @endphp
            <p>{!! __('ui.legal.syarat_kontak', ['email' => '<a href="mailto:'.$email.'">'.$email.'</a>']) !!}</p>
        @else
            <h1>{{ __('ui.legal.privasi_judul') }}</h1>
            <p>{{ __('ui.legal.diperbarui', ['tanggal' => $tanggal]) }}</p>
            <p>{{ __('ui.legal.privasi_intro', ['domain' => $domain]) }}</p>
            <h2>{{ __('ui.legal.privasi_1j') }}</h2>
            <ul>
                <li>{{ __('ui.legal.privasi_1a') }}</li>
                <li>{{ __('ui.legal.privasi_1b') }}</li>
                <li>{{ __('ui.legal.privasi_1c') }}</li>
            </ul>
            <h2>{{ __('ui.legal.privasi_2j') }}</h2>
            <ul>
                <li>{{ __('ui.legal.privasi_2a') }}</li>
                <li>{{ __('ui.legal.privasi_2b') }}</li>
                <li>{{ __('ui.legal.privasi_2c') }}</li>
            </ul>
            <h2>{{ __('ui.legal.privasi_3j') }}</h2>
            <p>{{ __('ui.legal.privasi_3i') }}</p>
            <h2>{{ __('ui.legal.privasi_4j') }}</h2>
            <p>{{ __('ui.legal.privasi_4i') }}</p>
            <h2>{{ __('ui.legal.privasi_5j') }}</h2>
            <p>{{ __('ui.legal.privasi_5i') }}</p>
            <h2>{{ __('ui.legal.privasi_6j') }}</h2>
            <p>{{ __('ui.legal.privasi_6i') }}</p>
            <ul>
                <li>{{ __('ui.legal.privasi_6a') }}</li>
                <li>{{ __('ui.legal.privasi_6b') }}</li>
            </ul>
            <h2>{{ __('ui.legal.privasi_7j') }}</h2>
            <p>{{ __('ui.legal.privasi_7i') }}</p>
            <h2>{{ __('ui.legal.privasi_8j') }}</h2>
            @php $email = 'angga.permana.n.n@gmail.com'; @endphp
            <p>{!! __('ui.legal.privasi_8i', ['email' => '<a href="mailto:'.$email.'">'.$email.'</a>']) !!}</p>
        @endif
        <p><a href="{{ url('/') }}">{{ __('ui.umum.kembali_beranda') }}</a></p>
    </div>
</main>
<footer class="kaki">© {{ date('Y') }} {{ $branding['nama'] }} | <a href="{{ route('legal.tentang') }}">{{ __('ui.kaki.tentang') }}</a> | <a href="{{ route('legal.syarat') }}">{{ __('ui.kaki.syarat') }}</a> | <a href="{{ route('legal.privasi') }}">{{ __('ui.kaki.privasi') }}</a></footer>
</body>
</html>
