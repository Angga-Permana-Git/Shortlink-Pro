{{--
    Panel QR satu tautan pada kartu Link Saya.

    Variabel $tautan berasal dari perulangan kartu pemanggil. Gambarnya diambil
    dari rute publik /qr/{slug} (SVG), rute yang sama dengan QR pada kartu
    Pemendek Tautan, sehingga tidak ada berkas yang perlu disimpan.
--}}
<div class="qr-tautan">
    <img class="qr-tautan__gambar" src="{{ route('qr.show', $tautan['slug']) }}"
         alt="QR Code {{ $tautan['slug'] }}" width="128" height="128">

    <div class="qr-tautan__isi">
        <p class="qr-tautan__judul">QR Code {{ $tautan['slug'] }}</p>
        <p class="panel-opsi__catatan">Pindai untuk membuka {{ $tautan['url'] }}.</p>

        <div class="qr-tautan__aksi">
            <a class="tombol-aksi" href="{{ route('qr.show', $tautan['slug']) }}"
               download="qr-{{ $tautan['slug'] }}.svg">
                <x-ikon nama="unduh" />
                <span>Unduh QR</span>
            </a>
            <button type="button" class="tombol-aksi" wire:click="sembunyikanQr">
                <span>Tutup</span>
            </button>
        </div>
    </div>
</div>
