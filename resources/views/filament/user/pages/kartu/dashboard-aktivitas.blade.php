{{-- Sumber, waktu, dan kualitas klik. --}}
<div class="panel-analitik" data-hook="aktivitas-klik">
    <div class="panel-analitik__kepala">
        <h3 class="panel-analitik__judul">{{ __('ui.panel.aktivitas_judul') }}</h3>
        <span class="panel-analitik__catatan">{{ __('ui.panel.puncak', ['jam' => $aktivitas['jam']['puncak']]) }}</span>
    </div>

    <div class="aktivitas">
        <div class="aktivitas__kolom">
            <h4 class="aktivitas__subjudul">{{ __('ui.panel.sumber_teratas') }}</h4>
            @forelse ($aktivitas['sumber'] as $sumber)
                <div class="sumber">
                    <span class="sumber__label">{{ $sumber['label'] }}</span>
                    <span class="sumber__bar"><span style="width: {{ $sumber['persen'] }}%"></span></span>
                    <span class="sumber__angka">{{ $sumber['jumlah'] }}</span>
                </div>
            @empty
                <p class="panel-analitik__kosong">{{ __('ui.panel.belum_ada_sumber') }}</p>
            @endforelse
        </div>

        <div class="aktivitas__kolom">
            <h4 class="aktivitas__subjudul">{{ __('ui.panel.kualitas_klik') }}</h4>
            <div class="kualitas">
                <div class="kualitas__bar">
                    <span style="width: {{ $aktivitas['kualitas']['persen'] }}%"></span>
                </div>
                <p class="kualitas__teks">
                    {{ __('ui.panel.kualitas_teks', [
                        'berhasil' => $aktivitas['kualitas']['berhasil'],
                        'total' => $aktivitas['kualitas']['total'],
                        'persen' => $aktivitas['kualitas']['persen'],
                    ]) }}
                </p>
                @foreach ($aktivitas['kualitas']['rincian'] as $rincian)
                    @if ($rincian['status'] !== 'success')
                        <div class="kualitas__rincian"><span>{{ $rincian['label'] }}</span><strong>{{ $rincian['jumlah'] }}</strong></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <h4 class="aktivitas__subjudul">{{ __('ui.panel.jam_sibuk') }}</h4>
    <div class="jam" role="img" aria-label="{{ __('ui.panel.klik_per_jam') }}">
        @foreach ($aktivitas['jam']['jam'] as $jam => $jumlah)
            @php $tertinggi = max(1, max($aktivitas['jam']['jam'])); @endphp
            <span class="jam__batang"
                  title="{{ __('ui.panel.klik_jam', ['jam' => sprintf('%02d.00', $jam), 'jumlah' => $jumlah]) }}"
                  style="height: {{ max(4, ($jumlah / $tertinggi) * 100) }}%"></span>
        @endforeach
    </div>
    <div class="jam__sumbu"><span>00</span><span>06</span><span>12</span><span>18</span><span>23</span></div>

    @if ($aktivitas['terbaru'] !== [])
        <h4 class="aktivitas__subjudul" style="margin-top: var(--jarak-4)">{{ __('ui.panel.klik_terbaru') }}</h4>
        <ul class="aktivitas-terbaru">
            @foreach ($aktivitas['terbaru'] as $klik)
                <li class="aktivitas-terbaru__item">
                    <span>{{ $klik['slug'] }}</span>
                    <span class="aktivitas-terbaru__status">{{ $klik['status'] }} • {{ $klik['waktu'] }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
