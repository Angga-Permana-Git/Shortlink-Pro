/**
 * Konstanta bersama halaman beranda.
 *
 * Semua selektor, kelas, durasi, dan teks yang dipakai modul lain dikumpulkan di
 * sini supaya tidak ada angka atau teks ajaib yang tersebar.
 */

export const SELEKTOR = {
    formulir: '[data-hook="formulir"]',
    tombolPendek: '[data-hook="tombol-pendek"]',
    labelTombol: '[data-hook="label-tombol"]',
    pilihanQr: '[data-hook="pilihan-qr"]',
    panelHasil: '[data-hook="panel-hasil"]',
    hasilTautan: '[data-hook="hasil-tautan"]',
    hasilQr: '[data-hook="hasil-qr"]',
    gambarQr: '[data-hook="gambar-qr"]',
    unduhQr: '[data-hook="unduh-qr"]',
    catatanHasil: '[data-hook="catatan-hasil"]',
    pesanGalat: '[data-hook="pesan-galat"]',
    tombolSalin: '[data-hook="tombol-salin"]',
    labelSalin: '[data-hook="label-salin"]',
    panelFitur: '[data-panel]',
};

export const KELAS = {
    tombolMemuat: 'tombol-utama--memuat',
};

export const DURASI_UMPAN_BALIK_SALIN = 2000;

export const PESAN = {
    tombolKirim: 'Pendekkan Tautan',
    tombolMemuat: 'Memproses…',
    gagalUmum: 'Tautan gagal dipendekkan. Silakan coba lagi.',
    gagalJaringan: 'Tidak dapat menghubungi server. Periksa koneksi Anda lalu coba lagi.',
    catatanSandi: 'Tautan ini dilindungi kata sandi.',
    catatanKedaluwarsa: 'Berlaku sampai {waktu}.',
    salin: 'Salin',
    salinBerhasil: 'Tersalin!',
    salinGagal: 'Gagal menyalin',
};
