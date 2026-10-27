{{-- Kartu 01: ringkasan dan analitik tautan milik pengguna. --}}
@php
    $harian = $trenKlik['harian'];
    $maks = max(1, max(array_column($harian, 'klik')));
    $jumlahHari = count($harian);
    $titik = collect($harian)->map(fn ($item, $i) => ($jumlahHari > 1 ? ($i / ($jumlahHari - 1)) * 100 : 0) . ',' . (29 - (($item['klik'] / $maks) * 26)))->implode(' ');
@endphp
<section class="kartu">
    <header class="kartu__kepala">
        <h1 class="kartu__judul">{{ __('ui.panel.dashboard') }}</h1>
        <span class="kartu__urut">01</span>
    </header>

    <p class="kartu__sapaan">{{ __('ui.panel.selamat_datang', ['nama' => $pengguna->name]) }}</p>

    <div class="periode" role="group" aria-label="{{ __('ui.panel.rentang_analitik') }}">
        @foreach ([7, 30, 90] as $nilai)
            <button type="button" class="periode__tombol {{ $periode === $nilai ? 'periode__tombol--aktif' : '' }}"
                    wire:click="ubahPeriode({{ $nilai }})">{{ __('ui.panel.hari', ['jumlah' => $nilai]) }}</button>
        @endforeach
    </div>

    <dl class="statistik">
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.total_tautan') }}</dt>
            <dd class="statistik__angka">{{ $ringkasan['total'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.tautan_aktif') }}</dt>
            <dd class="statistik__angka">{{ $ringkasan['aktif'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.klik_periode', ['jumlah' => $periode]) }}</dt>
            <dd class="statistik__angka">{{ $trenKlik['total'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.klik_berhasil') }}</dt>
            <dd class="statistik__angka">{{ $aktivitas['kualitas']['persen'] }}%</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.dilindungi_sandi') }}</dt>
            <dd class="statistik__angka">{{ $ringkasan['terproteksi'] }}</dd>
        </div>
    </dl>

    <div class="tren" data-hook="tren-klik">
        <div class="tren__kepala">
            <div>
                <span class="statistik__label">{{ __('ui.panel.tren_klik_periode', ['jumlah' => $periode]) }}</span>
                <strong class="tren__total">{{ __('ui.panel.klik', ['jumlah' => $trenKlik['total']]) }}</strong>
            </div>
            <span class="statistik__tren" data-trend="{{ ($trenKlik['delta'] ?? 0) >= 0 ? 'naik' : 'turun' }}">
                @if ($trenKlik['delta'] === null)
                    {{ __('ui.panel.belum_pembanding') }}
                @else
                    {{ __($trenKlik['delta'] >= 0 ? 'ui.panel.delta_naik' : 'ui.panel.delta_turun', [
                        'persen' => abs($trenKlik['delta']),
                        'hari' => $periode,
                        'klik' => $trenKlik['sebelum'],
                    ]) }}
                @endif
            </span>
        </div>
        <svg class="tren__svg" viewBox="0 0 100 32" preserveAspectRatio="none" role="img"
             aria-label="{{ __('ui.panel.grafik_klik', ['jumlah' => $periode]) }}">
            <polyline class="tren__garis" points="{{ $titik }}" />
        </svg>
        <div class="tren__kaki">
            <span>{{ $harian[0]['tanggal'] ?? '-' }}</span>
            <span>{{ __('ui.panel.rata_rata', ['jumlah' => $trenKlik['rata']]) }}</span>
            <span>{{ $harian[$jumlahHari - 1]['tanggal'] ?? '-' }}</span>
        </div>
        <p class="kartu__catatan">
            {{ __('ui.panel.hari_terbaik', [
                'tanggal' => $trenKlik['terbaik']['tanggal'],
                'klik' => $trenKlik['terbaik']['klik'],
                'hari_ini' => $trenKlik['hari_ini'],
            ]) }}
        </p>
    </div>

    <div class="zona-waktu"
         x-data
         x-init="if (! @js($pengguna->timezone)) {
             const zona = Intl.DateTimeFormat().resolvedOptions().timeZone;
             if (zona) $wire.deteksiZonaWaktu(zona);
         }">
        <div class="zona-waktu__kepala">
            <span class="statistik__label">{{ __('ui.panel.zona_waktu_anda') }}</span>
            <strong class="zona-waktu__sekarang">
                {{ $zonaSekarang->format('d/m/Y H:i') }} {{ $zonaWaktuLabel }}
            </strong>
        </div>
        <form class="zona-waktu__form" wire:submit="simpanZonaWaktu">
            <label class="sr-only" for="zona-waktu">{{ __('ui.panel.zona_waktu') }}</label>
            <select class="panel-opsi__kontrol" id="zona-waktu" wire:model="zonaWaktu">
                @foreach ($daftarZonaWaktu as $nilai => $label)
                    <option value="{{ $nilai }}">{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="tombol-kecil">{{ __('ui.panel.simpan') }}</button>
        </form>
        <p class="kartu__catatan">{{ __('ui.panel.zona_waktu_catatan') }}</p>
        @if ($pesanZonaWaktu)
            <p class="pesan-berhasil" role="status">{{ $pesanZonaWaktu }}</p>
        @endif
    </div>

    @include('filament.user.pages.kartu.dashboard-kesehatan', ['daftarKesehatan' => $kesehatan['daftar']])
    @include('filament.user.pages.kartu.dashboard-aktivitas', ['aktivitas' => $aktivitas])
    @include('filament.user.pages.kartu.dashboard-linktree', ['statistik' => $linktreeStatistik])

    @if ($tautanTerpopuler)
        <div class="statistik__populer">
            <span class="statistik__label">{{ __('ui.panel.tautan_terpopuler') }}</span>
            <span class="statistik__angka-kecil">
                {{ $tautanTerpopuler['slug'] }} ({{ __('ui.panel.klik', ['jumlah' => $tautanTerpopuler['klik']]) }})
            </span>
        </div>
    @endif
</section>
