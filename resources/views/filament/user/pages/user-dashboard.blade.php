{{--
    Halaman tunggal pengguna: empat kartu berurutan pada satu lembar.

    01 Dashboard      : angka ringkas tautan milik pengguna.
    02 Pemendek Tautan: formulir pembuatan tautan pendek beserta QR-nya.
    03 Link Saya      : aksi salin/QR/sunting/hapus tiap tautan dan pemilihan
                        tautan yang tampil di halaman Linktree (bisa dilipat).
    04 Profil Linktree: identitas halaman publik (logo, nama, keterangan, alamat
                        publik) dan pratinjaunya; bagian identitas bisa dilipat.
                        Nama login tidak ditampilkan ke pengunjung.

    Isi tiap kartu ada di berkas terpisah pada folder kartu/ supaya tidak ada
    berkas tampilan yang menumpuk banyak tanggung jawab. Seluruh isian dikirim ke
    aksi Livewire halaman ini; tidak ada handler sebaris maupun fungsi global.
    Interaksi tampilan (buka panel fitur, salin, gulir) ada di
    resources/js/publik/panel.js.

    Pembungkus terluar diperlukan karena Livewire hanya menerima satu elemen
    akar per komponen, sekaligus menjadi tempat jarak antarkartu di panel.css.
--}}
<div class="lembar-kartu">
    @include('filament.user.pages.kartu.dashboard')
    @include('filament.user.pages.kartu.pemendek-tautan')
    @include('filament.user.pages.kartu.link-saya')
    @include('filament.user.pages.kartu.profil-linktree')
</div>
