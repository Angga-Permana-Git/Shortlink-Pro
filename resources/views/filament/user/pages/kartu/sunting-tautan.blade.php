{{--
    Panel sunting satu tautan, ditampilkan di dalam barisnya pada kartu Link Saya.

    Variabel $tautan berasal dari perulangan kartu pemanggil. Chip "Kata sandi"
    membuka panel kata sandi lewat data-panel seperti chip pada kartu Pemendek
    Tautan, supaya tidak perlu perjalanan bolak-balik ke server hanya untuk
    menampilkan isian.
--}}
<div class="sunting">
    <p class="sunting__judul">{{ __('ui.panel.sunting_judul', ['slug' => $tautan['slug']]) }}</p>

    <div class="panel-opsi">
        <label class="panel-opsi__label" for="sunting-tujuan">{{ __('ui.panel.tautan_panjang') }}</label>
        <input class="panel-opsi__kontrol" id="sunting-tujuan" type="url"
               wire:model="suntingan.destination_url" required autocomplete="off">
    </div>

    <div class="panel-opsi">
        <span class="panel-opsi__label">{{ __('ui.panel.keadaan_tautan') }}</span>
        <div class="pilihan__daftar">
            <label class="pilihan__chip">
                <input type="checkbox" class="pilihan__kotak" wire:model="suntingan.is_active">
                <x-ikon nama="cek" />
                <span>{{ __('ui.panel.aktif') }}</span>
            </label>
            <label class="pilihan__chip">
                <input type="checkbox" class="pilihan__kotak" data-panel="panelSuntingSandi"
                       wire:model="suntingan.protected" @checked($tautan['bersandi'])>
                <x-ikon nama="kunci" />
                <span>{{ __('ui.panel.kata_sandi') }}</span>
            </label>
        </div>
    </div>

    <div class="panel-opsi" id="panelSuntingSandi" @if (! $tautan['bersandi']) hidden @endif>
        <label class="panel-opsi__label" for="sunting-sandi">{{ __('ui.panel.sandi_baru') }}</label>
        <input class="panel-opsi__kontrol" id="sunting-sandi" type="password"
               wire:model="suntingan.password" minlength="8" maxlength="255"
               placeholder="{{ __('ui.panel.minimal_8') }}" autocomplete="new-password">
        <p class="panel-opsi__catatan">{{ __('ui.panel.kosongkan_sandi_lama') }}</p>
    </div>

    <div class="panel-opsi">
        <label class="panel-opsi__label" for="sunting-berakhir">{{ __('ui.panel.berakhir_pada') }}</label>
        <input class="panel-opsi__kontrol" id="sunting-berakhir" type="datetime-local"
               wire:model="suntingan.expires_at">
    </div>

    <div class="sunting__aksi">
        <button type="button" class="tombol-utama" wire:click="simpanSunting({{ $tautan['id'] }})"
                wire:loading.attr="disabled" wire:target="simpanSunting">
            <span wire:loading.remove wire:target="simpanSunting">{{ __('ui.panel.simpan') }}</span>
            <span wire:loading wire:target="simpanSunting">{{ __('ui.panel.menyimpan') }}</span>
            <x-ikon nama="cek" />
        </button>
        <button type="button" class="tombol-aksi" wire:click="batalSunting">{{ __('ui.panel.batal') }}</button>
    </div>
</div>
