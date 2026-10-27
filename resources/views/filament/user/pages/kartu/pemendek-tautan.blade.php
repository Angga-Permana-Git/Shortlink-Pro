{{-- Kartu 02: pembuatan tautan pendek milik pengguna, beserta QR hasilnya. --}}
<section class="kartu">
    <header class="kartu__kepala">
        <h2 class="kartu__judul">{{ __('ui.panel.pemendek_judul') }}</h2>
        <span class="kartu__urut">02</span>
    </header>

    <p class="kartu__catatan">{{ __('ui.panel.pemendek_catatan') }}</p>

    @if ($errors->hasAny(['data.destination_url', 'data.slug', 'data.expires_at', 'data.password']))
        <div class="pesan-galat" role="alert">{{ $errors->first() }}</div>
    @endif

    <form wire:submit="buatTautan">
        <div class="kolom-tautan">
            <x-ikon nama="tautan" kelas="ikon kolom-tautan__ikon" />
            <label class="sr-only" for="destination_url">{{ __('ui.panel.tautan_panjang') }}</label>
            <input class="kolom-tautan__input" id="destination_url" type="url"
                   wire:model="data.destination_url"
                   placeholder="https://contoh.com/halaman-panjang" required autocomplete="off">
        </div>

        <fieldset class="pilihan">
            <legend class="pilihan__judul">{{ __('ui.panel.tambah_fitur') }}</legend>
            <div class="pilihan__daftar">
                <label class="pilihan__chip">
                    <input type="checkbox" class="pilihan__kotak" data-panel="panelKustom">
                    <x-ikon nama="tautan" />
                    <span>{{ __('ui.panel.kustom') }}</span>
                </label>
                <label class="pilihan__chip">
                    <input type="checkbox" class="pilihan__kotak" data-panel="panelSandi">
                    <x-ikon nama="kunci" />
                    <span>{{ __('ui.panel.kata_sandi') }}</span>
                </label>
                <label class="pilihan__chip">
                    <input type="checkbox" class="pilihan__kotak" data-panel="panelKedaluwarsa">
                    <x-ikon nama="jam" />
                    <span>{{ __('ui.panel.kedaluwarsa') }}</span>
                </label>
            </div>
        </fieldset>

        <div class="panel-opsi" id="panelKustom" hidden>
            <label class="panel-opsi__label" for="slug">{{ __('ui.panel.nama_pendek_kustom') }}</label>
            <div class="panel-opsi__awalan">
                <span class="panel-opsi__domain">{{ $domainPendek }}/</span>
                <input class="panel-opsi__kontrol" id="slug" type="text" wire:model="data.slug"
                       placeholder="{{ __('ui.panel.placeholder_slug_pendek') }}" maxlength="100"
                       pattern="[a-zA-Z0-9][a-zA-Z0-9\-_]*" autocomplete="off">
            </div>
        </div>

        <div class="panel-opsi" id="panelSandi" hidden>
            <label class="panel-opsi__label" for="password">{{ __('ui.panel.kata_sandi_tautan') }}</label>
            <input class="panel-opsi__kontrol" id="password" type="password" wire:model="data.password"
                   minlength="8" maxlength="255" placeholder="{{ __('ui.panel.minimal_8') }}" autocomplete="new-password">
        </div>

        <div class="panel-opsi" id="panelKedaluwarsa" hidden>
            <label class="panel-opsi__label" for="expires_at">{{ __('ui.panel.berakhir_pada') }}</label>
            <input class="panel-opsi__kontrol" id="expires_at" type="datetime-local" wire:model="data.expires_at">
        </div>

        <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="buatTautan">
            <span wire:loading.remove wire:target="buatTautan">{{ __('ui.panel.pendekkan_tautan') }}</span>
            <span wire:loading wire:target="buatTautan">{{ __('ui.panel.memproses') }}</span>
            <x-ikon nama="panah" />
        </button>
    </form>

    @if ($hasilUrl)
        <div class="hasil" role="status">
            <p class="hasil__label">
                <x-ikon nama="cek" />
                {{ __('ui.panel.tautan_siap') }}
            </p>
            <div class="hasil__baris">
                <a class="hasil__tautan" data-hook="hasil-tautan" href="{{ $hasilUrl }}"
                   target="_blank" rel="noopener">{{ $hasilUrl }}</a>
                <button type="button" class="tombol-kecil" data-salin="hasil-tautan">
                    <x-ikon nama="salin" />
                    <span data-hook="label-salin">{{ __('ui.panel.salin') }}</span>
                </button>
            </div>
            <div class="hasil__qr">
                <img class="hasil__qr-gambar" src="{{ route('qr.show', $hasilSlug) }}"
                     alt="{{ __('ui.panel.qr_alt', ['slug' => $hasilSlug]) }}" width="128" height="128">
                <a class="tombol-kecil" href="{{ route('qr.show', $hasilSlug) }}"
                   download="qr-{{ $hasilSlug }}.svg">
                    <x-ikon nama="unduh" />
                    {{ __('ui.panel.unduh_qr') }}
                </a>
            </div>
            <p class="lanjut-linktree">
                <a href="#kartu-linktree">{{ __('ui.panel.pilih_untuk_linktree') }}</a>
            </p>
        </div>
    @endif
</section>
