{{--
    Satu baris tautan pada kartu Link Saya.

    Variabel $tautan berasal dari perulangan kartu pemanggil. Panel QR dan panel
    sunting hanya muncul untuk baris yang dipilih, dan pilihannya disimpan di
    keadaan server (idQr/idSunting) supaya panel tetap terbuka setelah aksi
    berikutnya.
--}}
<li class="daftar-tautan__item" wire:key="tautan-{{ $tautan['id'] }}">
    <div class="daftar-tautan__baris">
        <label class="daftar-tautan__pilih">
            <input class="daftar-tautan__kotak" type="checkbox"
                   wire:model="pilihanLinktree" value="{{ $tautan['id'] }}">
            <span class="sr-only">{{ __('ui.panel.tampilkan_di_linktree', ['slug' => $tautan['slug']]) }}</span>
        </label>

        <div class="daftar-tautan__isi">
            <a class="daftar-tautan__slug" data-hook="tautan-{{ $tautan['id'] }}"
               href="{{ $tautan['url'] }}" target="_blank" rel="noopener">{{ $tautan['url'] }}</a>
            <span class="daftar-tautan__tujuan">{{ $tautan['tujuan'] }}</span>

            <span class="daftar-tautan__tanda">
                <span class="tanda">{{ __('ui.panel.klik', ['jumlah' => $tautan['klik']]) }}</span>
                @if ($tautan['status'] === 'inactive')
                    <span class="tanda tanda--mati">
                        <x-ikon nama="jam" />
                        {{ __('ui.panel.nonaktif') }}
                    </span>
                @elseif ($tautan['status'] === 'expired')
                    <span class="tanda tanda--mati">
                        <x-ikon nama="jam" />
                        {{ __('ui.panel.kedaluwarsa') }}
                    </span>
                @elseif ($tautan['status'] === 'scheduled')
                    <span class="tanda">
                        <x-ikon nama="jam" />
                        {{ __('ui.panel.akan_mulai') }}
                    </span>
                @endif
                @if ($tautan['bersandi'])
                    <span class="tanda">
                        <x-ikon nama="kunci" />
                        {{ __('ui.panel.bersandi') }}
                    </span>
                @endif
                @if ($tautan['kedaluwarsa'] && $tautan['status'] !== 'expired')
                    <span class="tanda">
                        <x-ikon nama="jam" />
                        <span class="tanda__tooltip" data-tooltip="{{ __('ui.panel.tooltip_nonaktif') }}">
                            {{ __('ui.panel.sampai', ['tanggal' => $tautan['kedaluwarsa']->format('d/m/Y H:i')]) }}
                        </span>
                    </span>
                @elseif ($tautan['kedaluwarsa'])
                    <span class="tanda">
                        <x-ikon nama="jam" />
                        <span class="tanda__tooltip" data-tooltip="{{ __('ui.panel.tooltip_kedaluwarsa') }}">
                            {{ __('ui.panel.sampai', ['tanggal' => $tautan['kedaluwarsa']->format('d/m/Y H:i')]) }}
                        </span>
                    </span>
                @elseif ($tautan['aktif_selamanya'])
                    <span class="tanda tanda--aktif">
                        <x-ikon nama="cek" />
                        {{ __('ui.panel.aktif_selamanya') }}
                    </span>
                @endif
                @if ($tautan['dipilih'])
                    <span class="tanda tanda--pilih">
                        <x-ikon nama="cek" />
                        {{ __('ui.panel.di_linktree') }}
                    </span>
                @endif
            </span>
        </div>

        <div class="daftar-tautan__aksi">
            <button type="button" class="tombol-aksi" data-salin="tautan-{{ $tautan['id'] }}">
                <x-ikon nama="salin" />
                <span data-hook="label-salin">{{ __('ui.panel.salin') }}</span>
            </button>
            <button type="button" class="tombol-aksi" wire:click="tampilkanQr({{ $tautan['id'] }})">
                <x-ikon nama="kotak" />
                <span>{{ __('ui.panel.qr') }}</span>
            </button>
            <button type="button" class="tombol-aksi" wire:click="mulaiSunting({{ $tautan['id'] }})">
                <x-ikon nama="pensil" />
                <span>{{ __('ui.panel.sunting') }}</span>
            </button>
            <button type="button" class="tombol-aksi tombol-aksi--bahaya"
                    wire:click="hapusTautan({{ $tautan['id'] }})"
                    wire:confirm="{{ __('ui.panel.hapus_konfirmasi', ['url' => $tautan['url']]) }}">
                <x-ikon nama="tempat-sampah" />
                <span>{{ __('ui.panel.hapus') }}</span>
            </button>
        </div>
    </div>

    @if ($idQr === $tautan['id'])
        @include('filament.user.pages.kartu.qr-tautan')
    @endif

    @if ($idSunting === $tautan['id'])
        @include('filament.user.pages.kartu.sunting-tautan')
    @endif
</li>
