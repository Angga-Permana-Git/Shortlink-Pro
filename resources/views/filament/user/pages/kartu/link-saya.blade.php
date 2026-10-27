{{--
    Kartu 03: tautan milik pengguna.

    Kartu ini bisa dilipat dan keadaannya disimpan lewat aksi lipatTautan supaya
    tetap terbuka atau tertutup setelah aksi Livewire berikutnya. Tiap baris ada
    di partial kartu/baris-tautan.blade.php: memuat centang "tampil di Linktree"
    serta aksi salin, QR, sunting, dan hapus. Semua tombol di dalam formulir ini
    bertipe `button` supaya klik aksi tidak ikut mengirim formulir pemilihan
    Linktree.
--}}
<section class="kartu" id="kartu-linktree" data-hook="kartu-linktree">
    <header class="kartu__kepala">
        <h2 class="kartu__judul">{{ __('ui.panel.link_saya') }}</h2>
        <span class="kartu__urut">03</span>
        <button type="button" class="kartu__lipat" wire:click="lipatTautan"
                aria-expanded="{{ $bukaTautan ? 'true' : 'false' }}" aria-controls="isi-link-saya">
            <x-ikon nama="panah-bawah" />
            <span>{{ $bukaTautan ? __('ui.panel.sembunyikan') : __('ui.panel.tampilkan') }}</span>
        </button>
    </header>

    <div id="isi-link-saya" @if (! $bukaTautan) hidden @endif>
        <p class="kartu__catatan">{{ __('ui.panel.link_saya_catatan') }}</p>

        @if ($pesanTautan)
            <p class="kartu__status" role="status">
                <x-ikon nama="cek" />
                {{ $pesanTautan }}
            </p>
        @endif

        @if ($pesanPilihan)
            <p class="kartu__status" role="status">
                <x-ikon nama="cek" />
                {{ $pesanPilihan }}
            </p>
        @endif

        @if ($errors->hasAny(['suntingan.destination_url', 'suntingan.expires_at', 'suntingan.password']))
            <div class="pesan-galat" role="alert">{{ $errors->first() }}</div>
        @endif

        {{-- Baris pencarian dan filter --}}
        <div class="daftar-tautan__kontrol">
            <div class="pencarian">
                <x-ikon nama="cari" kelas="pencarian__ikon" />
                <input class="pencarian__input" type="search" placeholder="{{ __('ui.panel.cari') }}"
                       wire:model.live="pencarian" />
            </div>
            <select class="pemilih-status" wire:model.live="filterStatus">
                <option value="">{{ __('ui.panel.semua_status') }}</option>
                <option value="active">{{ __('ui.panel.filter_aktif') }}</option>
                <option value="inactive">{{ __('ui.panel.filter_nonaktif') }}</option>
                <option value="protected">{{ __('ui.panel.filter_bersandi') }}</option>
                <option value="expired">{{ __('ui.panel.filter_kedaluwarsa') }}</option>
                <option value="expiring">{{ __('ui.panel.filter_expiring') }}</option>
                <option value="scheduled">{{ __('ui.panel.filter_terjadwal') }}</option>
                <option value="never">{{ __('ui.panel.filter_belum_diklik') }}</option>
                <option value="failed">{{ __('ui.panel.filter_gagal') }}</option>
            </select>
            <select class="pemilih-urut" wire:model.live="urutkan">
                <option value="terbaru">{{ __('ui.panel.urut_terbaru') }}</option>
                <option value="klik">{{ __('ui.panel.urut_klik') }}</option>
                <option value="abjad">{{ __('ui.panel.urut_abjad') }}</option>
            </select>
            <button type="button" class="tombol-kecil" wire:click="eksporCsv"
                    wire:loading.attr="disabled" wire:target="eksporCsv">
                <x-ikon nama="unduh" />
                <span>{{ __('ui.panel.ekspor_csv') }}</span>
            </button>
        </div>

        @if ($daftarTautan === [])
            <p class="daftar-tautan__kosong">{{ __('ui.panel.kosong_filter') }}</p>
        @else
            <ul class="daftar-tautan">
                @foreach ($daftarTautan as $tautan)
                    @include('filament.user.pages.kartu.baris-tautan')
                @endforeach
            </ul>
        @endif

        {{-- Formulir status terbit dengan toggle switch --}}
        <form wire:submit="simpanLinktree">
            <div class="toggle-status">
                <span class="toggle-status__label">{{ __('ui.panel.status_halaman_publik') }}</span>
                <button type="button" class="toggle-status__switch"
                        wire:click="ubahStatusTerbit"
                        aria-pressed="{{ $terbitkan ? 'true' : 'false' }}"
                        aria-label="{{ $terbitkan ? __('ui.panel.nonaktifkan_halaman') : __('ui.panel.aktifkan_halaman') }}"
                        data-hidup="{{ $terbitkan ? 'true' : 'false' }}">
                    <span class="toggle-status__lintas"></span>
                </button>
                <span class="toggle-status__teks">{{ $terbitkan ? __('ui.panel.diterbitkan') : __('ui.panel.tidak_diterbitkan') }}</span>
            </div>

            <button type="submit" class="tombol-utama" wire:loading.attr="disabled"
                    wire:target="simpanLinktree">
                <span wire:loading.remove wire:target="simpanLinktree">{{ __('ui.panel.simpan_pilihan') }}</span>
                <span wire:loading wire:target="simpanLinktree">{{ __('ui.panel.menyimpan') }}</span>
                <x-ikon nama="cek" />
            </button>
        </form>
    </div>
</section>
