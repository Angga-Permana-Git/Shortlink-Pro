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
            <span class="sr-only">Tampilkan {{ $tautan['slug'] }} di Linktree</span>
        </label>

        <div class="daftar-tautan__isi">
            <a class="daftar-tautan__slug" data-hook="tautan-{{ $tautan['id'] }}"
               href="{{ $tautan['url'] }}" target="_blank" rel="noopener">{{ $tautan['url'] }}</a>
            <span class="daftar-tautan__tujuan">{{ $tautan['tujuan'] }}</span>

            <span class="daftar-tautan__tanda">
                <span class="tanda">{{ $tautan['klik'] }} klik</span>
                @if ($tautan['status_teks'] === 'Nonaktif')
                    <span class="tanda tanda--mati">
                        <x-ikon nama="jam" />
                        Nonaktif
                    </span>
                @elseif ($tautan['status_teks'] === 'Kedaluwarsa')
                    <span class="tanda tanda--mati">
                        <x-ikon nama="jam" />
                        Kedaluwarsa
                    </span>
                @elseif ($tautan['status_teks'] === 'Akan Mulai')
                    <span class="tanda">
                        <x-ikon nama="jam" />
                        Akan Mulai
                    </span>
                @endif
                @if ($tautan['bersandi'])
                    <span class="tanda">
                        <x-ikon nama="kunci" />
                        Bersandi
                    </span>
                @endif
                @if ($tautan['kedaluwarsa'] && $tautan['status_teks'] !== 'Kedaluwarsa')
                    <span class="tanda">
                        <x-ikon nama="jam" />
                        <span class="tanda__tooltip" data-tooltip="Tautan akan otomatis nonaktif pada tanggal ini">
                            Sampai {{ $tautan['kedaluwarsa']->format('d/m/Y H:i') }}
                        </span>
                    </span>
                @elseif ($tautan['kedaluwarsa'])
                    <span class="tanda">
                        <x-ikon nama="jam" />
                        <span class="tanda__tooltip" data-tooltip="Tautan kedaluwarsa pada tanggal ini">
                            Sampai {{ $tautan['kedaluwarsa']->format('d/m/Y H:i') }}
                        </span>
                    </span>
                @elseif ($tautan['aktif_selamanya'])
                    <span class="tanda tanda--aktif">
                        <x-ikon nama="cek" />
                        Aktif Selamanya
                    </span>
                @endif
                @if ($tautan['dipilih'])
                    <span class="tanda tanda--pilih">
                        <x-ikon nama="cek" />
                        Di Linktree
                    </span>
                @endif
            </span>
        </div>

        <div class="daftar-tautan__aksi">
            <button type="button" class="tombol-aksi" data-salin="tautan-{{ $tautan['id'] }}">
                <x-ikon nama="salin" />
                <span data-hook="label-salin">Salin</span>
            </button>
            <button type="button" class="tombol-aksi" wire:click="tampilkanQr({{ $tautan['id'] }})">
                <x-ikon nama="kotak" />
                <span>QR</span>
            </button>
            <button type="button" class="tombol-aksi" wire:click="mulaiSunting({{ $tautan['id'] }})">
                <x-ikon nama="pensil" />
                <span>Sunting</span>
            </button>
            <button type="button" class="tombol-aksi tombol-aksi--bahaya"
                    wire:click="hapusTautan({{ $tautan['id'] }})"
                    wire:confirm="Hapus {{ $tautan['url'] }}? Tautan yang dihapus tidak bisa dikembalikan.">
                <x-ikon nama="tempat-sampah" />
                <span>Hapus</span>
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
