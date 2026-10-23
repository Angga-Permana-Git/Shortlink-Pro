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
        <h2 class="kartu__judul">Link Saya</h2>
        <span class="kartu__urut">03</span>
        <button type="button" class="kartu__lipat" wire:click="lipatTautan"
                aria-expanded="{{ $bukaTautan ? 'true' : 'false' }}" aria-controls="isi-link-saya">
            <x-ikon nama="panah-bawah" />
            <span>{{ $bukaTautan ? 'Sembunyikan' : 'Tampilkan' }}</span>
        </button>
    </header>

    <div id="isi-link-saya" @if (! $bukaTautan) hidden @endif>
        <p class="kartu__catatan">
            Centang tautan yang tampil di halaman publik, atau salin, lihat QR-nya, sunting, dan hapus tiap tautan.
        </p>

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
                <input class="pencarian__input" type="search" placeholder="Cari nama pendek atau URL..."
                       wire:model.live="pencarian" />
            </div>
            <select class="pemilih-status" wire:model.live="filterStatus">
                <option value="">Semua status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
                <option value="protected">Bersandi</option>
                <option value="expired">Kedaluwarsa</option>
                <option value="expiring">Kedaluwarsa ≤7 hari</option>
                <option value="scheduled">Terjadwal</option>
                <option value="never">Belum pernah diklik</option>
                <option value="failed">Klik gagal</option>
            </select>
            <select class="pemilih-urut" wire:model.live="urutkan">
                <option value="terbaru">Terbaru</option>
                <option value="klik">Klik terbanyak</option>
                <option value="abjad">A-Z</option>
            </select>
            <button type="button" class="tombol-kecil" wire:click="eksporCsv"
                    wire:loading.attr="disabled" wire:target="eksporCsv">
                <x-ikon nama="unduh" />
                <span>Ekspor CSV</span>
            </button>
        </div>

        @if ($daftarTautan === [])
            <p class="daftar-tautan__kosong">Tidak ada tautan yang cocok dengan filter yang dipilih.</p>
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
                <span class="toggle-status__label">Status halaman publik:</span>
                <button type="button" class="toggle-status__switch"
                        wire:click="ubahStatusTerbit"
                        aria-pressed="{{ $terbitkan ? 'true' : 'false' }}"
                        aria-label="{{ $terbitkan ? 'Nonaktifkan halaman publik' : 'Aktifkan halaman publik' }}"
                        data-hidup="{{ $terbitkan ? 'true' : 'false' }}">
                    <span class="toggle-status__lintas"></span>
                </button>
                <span class="toggle-status__teks">{{ $terbitkan ? 'Diterbitkan' : 'Tidak diterbitkan' }}</span>
            </div>

            <button type="submit" class="tombol-utama" wire:loading.attr="disabled"
                    wire:target="simpanLinktree">
                <span wire:loading.remove wire:target="simpanLinktree">Simpan Pilihan Linktree</span>
                <span wire:loading wire:target="simpanLinktree">Menyimpan...</span>
                <x-ikon nama="cek" />
            </button>
        </form>
    </div>
</section>
