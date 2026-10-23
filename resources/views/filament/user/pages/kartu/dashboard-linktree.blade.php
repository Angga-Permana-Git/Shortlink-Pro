{{-- Statistik halaman Linktree publik. --}}
<div class="panel-analitik" data-hook="statistik-linktree">
    <div class="panel-analitik__kepala">
        <h3 class="panel-analitik__judul">Statistik Linktree</h3>
        @if ($statistik['alamat'])
            <a class="panel-analitik__tautan" href="{{ $statistik['alamat'] }}" target="_blank" rel="noopener noreferrer">Buka halaman</a>
        @endif
    </div>

    <dl class="statistik statistik--kecil">
        <div class="statistik__item">
            <dt class="statistik__label">Kunjungan</dt>
            <dd class="statistik__angka">{{ $statistik['kunjungan'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">Pengunjung Unik</dt>
            <dd class="statistik__angka">{{ $statistik['pengunjung'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">Klik Tautan</dt>
            <dd class="statistik__angka">{{ $statistik['klik'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">Rasio Klik</dt>
            <dd class="statistik__angka">{{ $statistik['ctr'] }}%</dd>
        </div>
    </dl>

    @forelse ($statistik['tautan'] as $tautan)
        <div class="sumber">
            <span class="sumber__label">{{ $tautan['label'] }}</span>
            <span class="sumber__bar"><span style="width: {{ $statistik['klik'] > 0 ? ($tautan['klik'] / $statistik['klik']) * 100 : 0 }}%"></span></span>
            <span class="sumber__angka">{{ $tautan['klik'] }}</span>
        </div>
    @empty
        <p class="panel-analitik__kosong">Belum ada tautan Linktree yang diklik.</p>
    @endforelse
</div>
