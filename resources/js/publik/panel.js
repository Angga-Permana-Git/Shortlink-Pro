/**
 * Modul tampilan halaman tunggal pengguna.
 *
 * Tugasnya hanya memasang interaksi tampilan: membuka panel fitur pemendek,
 * menyalin tautan ke papan klip, dan menggulir ke kartu Linktree setelah tautan
 * dibuat. Penyimpanan data ditangani aksi Livewire pada halaman, bukan modul
 * ini.
 *
 * Seluruh pemasangan memakai pendengar di document, bukan pada tiap elemen,
 * karena Livewire mengganti sebagian DOM setelah aksi dijalankan dan pengikat
 * pada elemen akan ikut hilang.
 */

import { ACARA, DURASI_UMPAN_BALIK_SALIN, PESAN, SELEKTOR } from './panel-konstanta.js';

let timerUmpanBalik;
let labelUmpanBalik = null;
let teksAsliUmpanBalik = '';

/**
 * Chip fitur membuka panel isiannya.
 */
function tanganiPanelFitur(kotak) {
    const panel = document.getElementById(kotak.dataset.panel);

    if (! panel) {
        return;
    }

    panel.hidden = ! kotak.checked;

    if (kotak.checked) {
        panel.querySelector('input')?.focus();
    }
}

async function salinKePapanKlip(teks) {
    try {
        await navigator.clipboard.writeText(teks);

        return true;
    } catch {
        return false;
    }
}

/**
 * Tampilkan pesan singkat pada label tombol, lalu kembalikan teks aslinya.
 */
function tampilkanUmpanBalik(label, pesan) {
    clearTimeout(timerUmpanBalik);

    if (labelUmpanBalik && labelUmpanBalik !== label) {
        labelUmpanBalik.textContent = teksAsliUmpanBalik;
    }

    labelUmpanBalik = label;
    teksAsliUmpanBalik = label.textContent;
    label.textContent = pesan;

    timerUmpanBalik = setTimeout(() => {
        label.textContent = teksAsliUmpanBalik;
        labelUmpanBalik = null;
    }, DURASI_UMPAN_BALIK_SALIN);
}

/**
 * Tombol salin menunjuk sumbernya lewat data-salin, yaitu nilai data-hook milik
 * elemen yang teksnya disalin.
 */
async function tanganiSalin(tombol) {
    const label = tombol.querySelector(SELEKTOR.labelSalin);
    const sumber = document.querySelector(`[data-hook="${tombol.dataset.salin}"]`);

    if (! label || ! sumber) {
        return;
    }

    const teks = sumber.textContent.trim();

    if (! teks) {
        return;
    }

    const berhasil = await salinKePapanKlip(teks);

    tampilkanUmpanBalik(label, berhasil ? PESAN.salinBerhasil : PESAN.salinGagal);
}

document.addEventListener('change', (peristiwa) => {
    const kotak = peristiwa.target.closest(SELEKTOR.panelFitur);

    if (kotak) {
        tanganiPanelFitur(kotak);
    }
});

document.addEventListener('click', (peristiwa) => {
    const tombol = peristiwa.target.closest(SELEKTOR.tombolSalin);

    if (tombol) {
        tanganiSalin(tombol);
    }
});

/*
    Halaman mengirim acara ini setelah tautan berhasil dibuat supaya pengguna
    langsung dibawa ke kartu Linktree.
*/
window.addEventListener(ACARA.tautanDibuat, () => {
    document.querySelector(SELEKTOR.kartuLinktree)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

/*
    Timer umpan balik dilepas saat halaman ditinggalkan agar tidak ada timer
    yatim yang masih berjalan.
*/
window.addEventListener('pagehide', () => clearTimeout(timerUmpanBalik));
