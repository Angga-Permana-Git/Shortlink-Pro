/**
 * Konstanta bersama halaman tunggal pengguna.
 *
 * Semua selektor, nama acara, durasi, dan teks yang dipakai modul tampilan
 * dikumpulkan di sini supaya tidak ada nilai ajaib yang tersebar.
 */

export const SELEKTOR = {
    panelFitur: '[data-panel]',
    tombolSalin: '[data-salin]',
    labelSalin: '[data-hook="label-salin"]',
    kartuLinktree: '[data-hook="kartu-linktree"]',
};

export const ACARA = {
    tautanDibuat: 'tautan-dibuat',
};

export const DURASI_UMPAN_BALIK_SALIN = 2000;

export const PESAN = {
    salinBerhasil: 'Tersalin!',
    salinGagal: 'Gagal menyalin',
};
