{{-- Statistik halaman Linktree publik. --}}
<div class="panel-analitik" data-hook="statistik-linktree">
    <div class="panel-analitik__kepala">
        <h3 class="panel-analitik__judul">{{ __('ui.panel.statistik_linktree') }}</h3>
        @if ($statistik['alamat'])
            <a class="panel-analitik__tautan" href="{{ $statistik['alamat'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.panel.buka_halaman') }}</a>
        @endif
    </div>

    <dl class="statistik statistik--kecil">
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.kunjungan') }}</dt>
            <dd class="statistik__angka">{{ $statistik['kunjungan'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.pengunjung_unik') }}</dt>
            <dd class="statistik__angka">{{ $statistik['pengunjung'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.klik_tautan') }}</dt>
            <dd class="statistik__angka">{{ $statistik['klik'] }}</dd>
        </div>
        <div class="statistik__item">
            <dt class="statistik__label">{{ __('ui.panel.rasio_klik') }}</dt>
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
        <p class="panel-analitik__kosong">{{ __('ui.panel.belum_ada_klik_linktree') }}</p>
    @endforelse
</div>
