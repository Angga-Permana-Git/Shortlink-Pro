@php
    $teks = match ($status) {
        \App\Support\Enums\LinkAccessStatus::Inactive => [
            'judul' => __('ui.tautan.nonaktif_judul'),
            'pesan' => __('ui.tautan.nonaktif_pesan'),
            'ikon' => 'kunci',
        ],
        \App\Support\Enums\LinkAccessStatus::Expired => [
            'judul' => __('ui.tautan.kedaluwarsa_judul'),
            'pesan' => __('ui.tautan.kedaluwarsa_pesan'),
            'ikon' => 'jam',
        ],
        \App\Support\Enums\LinkAccessStatus::Scheduled => [
            'judul' => __('ui.tautan.belum_aktif_judul'),
            'pesan' => __('ui.tautan.belum_aktif_pesan'),
            'ikon' => 'jam',
        ],
        \App\Support\Enums\LinkAccessStatus::TooManyAttempts => [
            'judul' => __('ui.tautan.terlalu_banyak_judul'),
            'pesan' => __('ui.tautan.terlalu_banyak_pesan'),
            'ikon' => 'kunci',
        ],
        default => [
            'judul' => __('ui.tautan.umum_judul'),
            'pesan' => __('ui.tautan.umum_pesan'),
            'ikon' => 'kotak',
        ],
    };
@endphp

<x-halaman-tautan :judul="$teks['judul']" :pesan="$teks['pesan']" :nama-ikon="$teks['ikon']">
    <a class="tautan__aksi" href="{{ url('/') }}">{{ __('ui.umum.kembali_beranda') }}</a>
</x-halaman-tautan>
