/**
 * Modul tampilan beranda.
 *
 * Tugasnya hanya memasang interaksi dan memperbarui DOM dari data yang sudah
 * disiapkan modul lain: endpoint dipanggil lewat layanan-tautan.js, sedangkan
 * pengolahan teks dan waktu ada di utilitas-teks.js.
 *
 * Setiap pengambilan data menangani tiga kondisi: memuat, berhasil, dan gagal.
 */

import { DURASI_UMPAN_BALIK_SALIN, KELAS, PESAN, SELEKTOR } from './konstanta.js';
import { kirimTautanPendek } from './layanan-tautan.js';
import { formatWaktu, isiPenanda } from './utilitas-teks.js';

const el = (selektor) => document.querySelector(selektor);

const formulir = el(SELEKTOR.formulir);
const tombolPendek = el(SELEKTOR.tombolPendek);
const labelTombol = el(SELEKTOR.labelTombol);
const pilihanQr = el(SELEKTOR.pilihanQr);
const panelHasil = el(SELEKTOR.panelHasil);
const hasilTautan = el(SELEKTOR.hasilTautan);
const hasilQr = el(SELEKTOR.hasilQr);
const gambarQr = el(SELEKTOR.gambarQr);
const unduhQr = el(SELEKTOR.unduhQr);
const catatanHasil = el(SELEKTOR.catatanHasil);
const pesanGalat = el(SELEKTOR.pesanGalat);
const tombolSalin = el(SELEKTOR.tombolSalin);
const labelSalin = el(SELEKTOR.labelSalin);

let timerUmpanBalikSalin;

/**
 * Chip fitur membuka panel isiannya. Panel disinkronkan saat halaman dimuat
 * supaya keadaan awal tetap benar setelah tombol kembali browser dipakai.
 */
function pasangPanelFitur() {
    document.querySelectorAll(SELEKTOR.panelFitur).forEach((kotak) => {
        const panel = document.getElementById(kotak.dataset.panel);

        if (!panel) {
            return;
        }

        const sinkronkan = () => {
            panel.hidden = !kotak.checked;

            if (kotak.checked) {
                panel.querySelector('input')?.focus();
            }
        };

        kotak.addEventListener('change', sinkronkan);
        sinkronkan();
    });
}

function sembunyikanPesan() {
    pesanGalat.hidden = true;
}

function tampilkanGalat(pesan) {
    pesanGalat.textContent = pesan;
    pesanGalat.hidden = false;
    panelHasil.hidden = true;
}

function susunCatatan(tautan) {
    const catatan = [];

    if (tautan.protected) {
        catatan.push(PESAN.catatanSandi);
    }

    const waktuBerakhir = formatWaktu(tautan.expires_at);

    if (waktuBerakhir) {
        catatan.push(isiPenanda(PESAN.catatanKedaluwarsa, { waktu: waktuBerakhir }));
    }

    catatanHasil.textContent = catatan.join(' ');
    catatanHasil.hidden = catatan.length === 0;
}

function tampilkanHasil(tautan) {
    sembunyikanPesan();

    hasilTautan.textContent = tautan.url;
    hasilTautan.href = tautan.url;
    gambarQr.src = tautan.qr;
    unduhQr.href = tautan.qr;
    hasilQr.hidden = !pilihanQr?.checked;
    susunCatatan(tautan);

    panelHasil.hidden = false;
    panelHasil.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function tandaiMemuat(sedangMemuat) {
    tombolPendek.disabled = sedangMemuat;
    tombolPendek.classList.toggle(KELAS.tombolMemuat, sedangMemuat);
    tombolPendek.setAttribute('aria-busy', String(sedangMemuat));
    labelTombol.textContent = sedangMemuat ? PESAN.tombolMemuat : PESAN.tombolKirim;
}

async function salinKePapanKlip(teks) {
    try {
        await navigator.clipboard.writeText(teks);

        return true;
    } catch {
        return false;
    }
}

function tampilkanUmpanBalikSalin(pesan) {
    labelSalin.textContent = pesan;
    clearTimeout(timerUmpanBalikSalin);
    timerUmpanBalikSalin = setTimeout(() => {
        labelSalin.textContent = PESAN.salin;
    }, DURASI_UMPAN_BALIK_SALIN);
}

pasangPanelFitur();

formulir?.addEventListener('submit', async (peristiwa) => {
    peristiwa.preventDefault();
    tandaiMemuat(true);

    const { berhasil, tautan, pesan } = await kirimTautanPendek(formulir.action, new FormData(formulir));

    tandaiMemuat(false);

    if (berhasil) {
        tampilkanHasil(tautan);

        return;
    }

    tampilkanGalat(pesan);
});

pilihanQr?.addEventListener('change', () => {
    if (gambarQr.getAttribute('src')) {
        hasilQr.hidden = !pilihanQr.checked;
    }
});

tombolSalin?.addEventListener('click', async () => {
    const teks = hasilTautan.textContent;

    if (! teks) {
        return;
    }

    const berhasil = await salinKePapanKlip(teks);
    tampilkanUmpanBalikSalin(berhasil ? PESAN.salinBerhasil : PESAN.salinGagal);
});

/*
    Timer umpan balik dilepas saat halaman ditinggalkan agar tidak ada timer
    yatim yang masih berjalan.
*/
window.addEventListener('pagehide', () => clearTimeout(timerUmpanBalikSalin));
