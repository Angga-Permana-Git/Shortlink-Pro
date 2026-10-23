{{-- Kartu 01: ringkasan dan analitik tautan milik pengguna. --}}
@php
    $harian = $trenKlik['harian'];
    $maks = max(1, max(array_column($harian, 'klik')));
    $jumlahHari = count($harian);
    $titik = collect($harian)->map(fn ($item, $i) => ($jumlahHari > 1 ? ($i / ($jumlahHari - 1)) * 100 : 0) . ',' . (29 - (($item['klik'] / $maks) * 26)))->implode(' ');
@endphp
<section class="kartu">
    <header class="kartu__kepala">
        <h1 class="kartu__judul">Dashboard</h1>
        <span class="kartu__urut">01</span>
    </header>

    <p class="kartu__sapaan">Selamat datang kembali, {{ $pengguna->name }}.</p>

    <div class="periode" role="group" aria-label="Rentang analitik">
        @foreach ([7 => '7 hari', 30 => '30 hari', 90 => '90 hari'] as $nilai => $label)
            <button type="button" class="periode__tombol {{ $periode === $nilai ? 'periode__tombol--aktif' : '' }}"
                    wire:click="ubahPeriode({{ $nilai }})">{{ $label }}</button>
        @endforeach
    </div>

    <dl class="statistik">
        <div class="statistik__item">
            <dt class="statistik__label">Total Tautan</dt>
            <dd class="statistik__angka">{{ $ringkasan['total'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">Tautan Aktif</dt>
            <dd class="statistik__angka">{{ $ringkasan['aktif'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">Klik {{ $periode }} Hari</dt>
            <dd class="statistik__angka">{{ $trenKlik['total'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">Klik Berhasil</dt>
            <dd class="statistik__angka">{{ $aktivitas['kualitas']['persen'] }}%</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">Dilindungi Sandi</dt>
            <dd class="statistik__angka">{{ $ringkasan['terproteksi'] }}</dd>
        </div>
    </dl>

    <div class="tren" data-hook="tren-klik">
        <div class="tren__kepala">
            <div>
                <span class="statistik__label">Tren Klik {{ $periode }} Hari</span>
                <strong class="tren__total">{{ $trenKlik['total'] }} klik</strong>
            </div>
            <span class="statistik__tren" data-trend="{{ ($trenKlik['delta'] ?? 0) >= 0 ? 'naik' : 'turun' }}">
                @if ($trenKlik['delta'] === null)
                    Belum ada pembanding
                @else
                    {{ $trenKlik['delta'] >= 0 ? '↑' : '↓' }} {{ abs($trenKlik['delta']) }}% dari {{ $periode }} hari sebelumnya ({{ $trenKlik['sebelum'] }} klik)
                @endif
            </span>
        </div>
        <svg class="tren__svg" viewBox="0 0 100 32" preserveAspectRatio="none" role="img"
             aria-label="Grafik klik {{ $periode }} hari terakhir">
            <polyline class="tren__garis" points="{{ $titik }}" />
        </svg>
        <div class="tren__kaki">
            <span>{{ $harian[0]['tanggal'] ?? '-' }}</span>
            <span>Rata-rata {{ $trenKlik['rata'] }}/hari</span>
            <span>{{ $harian[$jumlahHari - 1]['tanggal'] ?? '-' }}</span>
        </div>
        <p class="kartu__catatan">Hari terbaik: {{ $trenKlik['terbaik']['tanggal'] }} ({{ $trenKlik['terbaik']['klik'] }} klik) · Hari ini: {{ $trenKlik['hari_ini'] }} klik.</p>
    </div>

    <div class="zona-waktu"
         x-data
         x-init="if (! @js($pengguna->timezone)) {
             const zona = Intl.DateTimeFormat().resolvedOptions().timeZone;
             if (zona) $wire.deteksiZonaWaktu(zona);
         }">
        <div class="zona-waktu__kepala">
            <span class="statistik__label">Zona Waktu Anda</span>
            <strong class="zona-waktu__sekarang">
                {{ $zonaSekarang->format('d/m/Y H:i') }} {{ $zonaWaktuLabel }}
            </strong>
        </div>
        <form class="zona-waktu__form" wire:submit="simpanZonaWaktu">
            <label class="sr-only" for="zona-waktu">Zona waktu</label>
            <select class="panel-opsi__kontrol" id="zona-waktu" wire:model="zonaWaktu">
                @foreach ($daftarZonaWaktu as $nilai => $label)
                    <option value="{{ $nilai }}">{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="tombol-kecil">Simpan</button>
        </form>
        <p class="kartu__catatan">Tanggal dan jam tautan mengikuti zona ini. Waktu server tetap dipakai sebagai acuan penyimpanan.</p>
        @if ($pesanZonaWaktu)
            <p class="pesan-berhasil" role="status">{{ $pesanZonaWaktu }}</p>
        @endif
    </div>

    @include('filament.user.pages.kartu.dashboard-kesehatan', ['daftarKesehatan' => $kesehatan['daftar']])
    @include('filament.user.pages.kartu.dashboard-aktivitas', ['aktivitas' => $aktivitas])
    @include('filament.user.pages.kartu.dashboard-linktree', ['statistik' => $linktreeStatistik])

    @if ($tautanTerpopuler)
        <div class="statistik__populer">
            <span class="statistik__label">Tautan Terpopuler</span>
            <span class="statistik__angka-kecil">{{ $tautanTerpopuler['slug'] }} ({{ $tautanTerpopuler['klik'] }} klik)</span>
        </div>
    @endif
</section>
