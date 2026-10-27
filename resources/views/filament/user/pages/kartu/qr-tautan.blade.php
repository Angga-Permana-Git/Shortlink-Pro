{{--
    Panel QR satu tautan pada kartu Link Saya.

    Variabel $tautan berasal dari perulangan kartu pemanggil. Gambarnya diambil
    dari rute publik /qr/{slug} (SVG), rute yang sama dengan QR pada kartu
    Pemendek Tautan, sehingga tidak ada berkas yang perlu disimpan.
--}}
<div class="qr-tautan">
    <img class="qr-tautan__gambar" src="{{ route('qr.show', $tautan['slug']) }}"
         alt="{{ __('ui.panel.qr_alt', ['slug' => $tautan['slug']]) }}" width="128" height="128">

    <div class="qr-tautan__isi">
        <p class="qr-tautan__judul">{{ __('ui.panel.qr_judul', ['slug' => $tautan['slug']]) }}</p>
        <p class="panel-opsi__catatan">{{ __('ui.panel.qr_pindai', ['url' => $tautan['url']]) }}</p>

        <div class="qr-tautan__aksi">
            <a class="tombol-aksi" href="{{ route('qr.show', $tautan['slug']) }}"
               download="qr-{{ $tautan['slug'] }}.svg">
                <x-ikon nama="unduh" />
                <span>{{ __('ui.panel.unduh_qr') }}</span>
            </a>
            <button type="button" class="tombol-aksi" wire:click="sembunyikanQr">
                <span>{{ __('ui.panel.tutup') }}</span>
            </button>
        </div>
    </div>
</div>
