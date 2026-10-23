{{-- Sumber, waktu, dan kualitas klik. --}}
<div class="panel-analitik" data-hook="aktivitas-klik">
    <div class="panel-analitik__kepala">
        <h3 class="panel-analitik__judul">Aktivitas Klik</h3>
        <span class="panel-analitik__catatan">Puncak: {{ $aktivitas['jam']['puncak'] }}</span>
    </div>

    <div class="aktivitas">
        <div class="aktivitas__kolom">
            <h4 class="aktivitas__subjudul">Sumber Teratas</h4>
            @forelse ($aktivitas['sumber'] as $sumber)
                <div class="sumber">
                    <span class="sumber__label">{{ $sumber['label'] }}</span>
                    <span class="sumber__bar"><span style="width: {{ $sumber['persen'] }}%"></span></span>
                    <span class="sumber__angka">{{ $sumber['jumlah'] }}</span>
                </div>
            @empty
                <p class="panel-analitik__kosong">Belum ada data sumber klik.</p>
            @endforelse
        </div>

        <div class="aktivitas__kolom">
            <h4 class="aktivitas__subjudul">Kualitas Klik</h4>
            <div class="kualitas">
                <div class="kualitas__bar">
                    <span style="width: {{ $aktivitas['kualitas']['persen'] }}%"></span>
                </div>
                <p class="kualitas__teks">
                    {{ $aktivitas['kualitas']['berhasil'] }} berhasil dari {{ $aktivitas['kualitas']['total'] }} klik
                    ({{ $aktivitas['kualitas']['persen'] }}%).
                </p>
                @foreach ($aktivitas['kualitas']['rincian'] as $rincian)
                    @if ($rincian['label'] !== 'Berhasil')
                        <div class="kualitas__rincian"><span>{{ $rincian['label'] }}</span><strong>{{ $rincian['jumlah'] }}</strong></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <h4 class="aktivitas__subjudul">Jam Sibuk (24 jam)</h4>
    <div class="jam" role="img" aria-label="Klik per jam">
        @foreach ($aktivitas['jam']['jam'] as $jam => $jumlah)
            @php $tertinggi = max(1, max($aktivitas['jam']['jam'])); @endphp
            <span class="jam__batang" title="{{ sprintf('%02d.00', $jam) }}: {{ $jumlah }} klik"
                  style="height: {{ max(4, ($jumlah / $tertinggi) * 100) }}%"></span>
        @endforeach
    </div>
    <div class="jam__sumbu"><span>00</span><span>06</span><span>12</span><span>18</span><span>23</span></div>

    @if ($aktivitas['terbaru'] !== [])
        <h4 class="aktivitas__subjudul" style="margin-top: var(--jarak-4)">Klik Terbaru</h4>
        <ul class="aktivitas-terbaru">
            @foreach ($aktivitas['terbaru'] as $klik)
                <li class="aktivitas-terbaru__item">
                    <span>{{ $klik['slug'] }}</span>
                    <span class="aktivitas-terbaru__status">{{ $klik['status'] }} · {{ $klik['waktu'] }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
