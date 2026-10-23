{{-- Ringkasan hal yang perlu ditindaklanjuti pemilik tautan. --}}
<div class="panel-analitik" data-hook="kesehatan-tautan">
    <div class="panel-analitik__kepala">
        <h3 class="panel-analitik__judul">Kesehatan Tautan</h3>
        <span class="panel-analitik__catatan">Klik untuk melihat daftarnya</span>
    </div>

    @if ($daftarKesehatan === [])
        <p class="panel-analitik__kosong">Semua tautan Anda dalam kondisi baik.</p>
    @else
        <ul class="kesehatan">
            @foreach ($daftarKesehatan as $item)
                <li>
                    <button type="button" class="kesehatan__item"
                            x-data
                            @click="$wire.terapkanFilter('{{ $item['filter'] }}'); document.getElementById('{{ $item['target'] === 'linktree' ? 'kartu-profil-linktree' : 'kartu-linktree' }}')?.scrollIntoView({behavior: 'smooth'})">
                        <span class="kesehatan__jumlah">{{ $item['jumlah'] }}</span>
                        <span>
                            <strong class="kesehatan__judul">{{ $item['judul'] }}</strong>
                            <span class="kesehatan__pesan">{{ $item['pesan'] }}</span>
                        </span>
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
