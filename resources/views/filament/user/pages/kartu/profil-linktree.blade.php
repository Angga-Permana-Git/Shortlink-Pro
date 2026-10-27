{{--
    Kartu 04: identitas halaman Linktree publik (logo, nama tampilan, keterangan,
    alamat publik) beserta pratinjau halaman yang akan dilihat pengunjung.

    Hanya bagian identitas yang bisa dilipat; pratinjaunya selalu tampil. Nama
    login tidak ditampilkan di pratinjau maupun di halaman publik, karena alamat
    publiknya dipilih sendiri oleh pengguna (kolom public_slug).

    Pratinjau memakai kelas profil dari linktree-publik.css, yaitu gaya yang sama
    dengan halaman /u/{alamat}, supaya hasilnya benar-benar sama.
--}}
<section class="kartu" id="kartu-profil-linktree">
    <header class="kartu__kepala">
        <h2 class="kartu__judul">{{ __('ui.panel.profil_linktree') }}</h2>
        <span class="kartu__urut">04</span>
        <button type="button" class="kartu__lipat" wire:click="lipatProfil"
                aria-expanded="{{ $bukaProfil ? 'true' : 'false' }}" aria-controls="isi-profil-linktree">
            <x-ikon nama="panah-bawah" />
            <span>{{ $bukaProfil ? __('ui.panel.sembunyikan') : __('ui.panel.tampilkan') }}</span>
        </button>
    </header>

    <p class="kartu__catatan">{{ __('ui.panel.profil_catatan') }}</p>

    @if ($pesanProfil)
        <p class="kartu__status" role="status">
            <x-ikon nama="cek" />
            {{ $pesanProfil }}
        </p>
    @endif

    @if ($errors->hasAny(['profil.display_name', 'profil.bio', 'profil.public_slug', 'profil.logo']))
        <div class="pesan-galat" role="alert">{{ $errors->first() }}</div>
    @endif

    <div id="isi-profil-linktree" @if (! $bukaProfil) hidden @endif>
        <form class="profil-form" wire:submit="simpanProfil">
            <div class="panel-opsi">
                <label class="panel-opsi__label" for="display_name">{{ __('ui.panel.nama_tampilan') }}</label>
                <input class="panel-opsi__kontrol" id="display_name" type="text" wire:model="profil.display_name"
                       maxlength="255" placeholder="{{ __('ui.panel.placeholder_nama_tampilan') }}" required autocomplete="off">
            </div>

            <div class="panel-opsi">
                <label class="panel-opsi__label" for="bio">{{ __('ui.panel.keterangan') }}</label>
                <textarea class="panel-opsi__kontrol" id="bio" wire:model="profil.bio" rows="2" maxlength="500"
                          placeholder="{{ __('ui.panel.placeholder_keterangan') }}"></textarea>
            </div>

            <div class="panel-opsi">
                <label class="panel-opsi__label" for="public_slug">{{ __('ui.panel.alamat_publik') }}</label>
                <div class="panel-opsi__awalan">
                    <span class="panel-opsi__domain">{{ $domainProfil }}/</span>
                    <input class="panel-opsi__kontrol" id="public_slug" type="text"
                           wire:model="profil.public_slug" maxlength="60"
                           pattern="[a-z0-9][a-z0-9\-]*" placeholder="{{ __('ui.panel.placeholder_alamat_publik') }}" required autocomplete="off">
                </div>
                <p class="panel-opsi__catatan">{{ __('ui.panel.alamat_publik_catatan') }}</p>
            </div>

            <div class="panel-opsi">
                <label class="panel-opsi__label" for="logo">{{ __('ui.panel.logo_profil') }}</label>
                <input class="panel-opsi__kontrol" id="logo" type="file" wire:model="profil.logo"
                       accept="image/svg+xml,image/png,image/jpeg">
                <p class="panel-opsi__catatan" wire:loading wire:target="profil.logo">{{ __('ui.panel.mengunggah') }}</p>
                <p class="panel-opsi__catatan">{{ __('ui.panel.logo_catatan') }}</p>
            </div>

            <div class="profil-form__aksi">
                <button type="submit" class="tombol-utama" wire:loading.attr="disabled" wire:target="simpanProfil">
                    <span wire:loading.remove wire:target="simpanProfil">{{ __('ui.panel.simpan_profil') }}</span>
                    <span wire:loading wire:target="simpanProfil">{{ __('ui.panel.menyimpan') }}</span>
                    <x-ikon nama="cek" />
                </button>

                @if ($tinjauan['logo'])
                    <button type="button" class="tombol-aksi tombol-aksi--bahaya" wire:click="hapusLogo"
                            wire:confirm="{{ __('ui.panel.hapus_logo_konfirmasi') }}">
                        <x-ikon nama="tempat-sampah" />
                        <span>{{ __('ui.panel.hapus_logo') }}</span>
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="tinjauan">
        <div class="profil">
            <div class="profil__lambang" aria-hidden="true">
                @if ($profil['logo'] && $profil['logo']->isPreviewable())
                    <img class="profil__lambang-gambar" src="{{ $profil['logo']->temporaryUrl() }}" alt="">
                @elseif ($tinjauan['logo'])
                    <img class="profil__lambang-gambar" src="{{ $tinjauan['logo'] }}" alt="">
                @else
                    {{ mb_strtoupper(mb_substr($tinjauan['nama'], 0, 1)) }}
                @endif
            </div>
            <p class="profil__nama">{{ $tinjauan['nama'] }}</p>
            @if ($tinjauan['bio'])
                <p class="profil__bio">{{ $tinjauan['bio'] }}</p>
            @endif

            <ul class="profil__daftar">
                @forelse ($tinjauan['tautan'] as $tautan)
                    <li>
                        <a class="profil__tombol" href="{{ $tautan['url'] }}" target="_blank" rel="noopener noreferrer nofollow">
                            <x-ikon nama="tautan" />
                            <span>{{ $tautan['label'] }}</span>
                        </a>
                    </li>
                @empty
                    <li class="profil__kosong">{{ __('ui.panel.belum_ada_tautan') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if ($tinjauan['alamat'])
        <div class="tinjauan__alamat">
            <a class="tinjauan__url" data-hook="alamat-linktree" href="{{ $tinjauan['alamat'] }}"
               target="_blank" rel="noopener noreferrer">{{ $tinjauan['alamat'] }}</a>
            <button type="button" class="tombol-kecil" data-salin="alamat-linktree">
                <x-ikon nama="salin" />
                <span data-hook="label-salin">{{ __('ui.panel.salin') }}</span>
            </button>
        </div>
    @else
        <p class="kartu__catatan">{{ __('ui.panel.alamat_belum') }}</p>
    @endif
</section>
