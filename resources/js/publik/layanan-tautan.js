/**
 * Service API halaman beranda.
 *
 * Satu-satunya berkas yang memanggil endpoint pemendek tautan. Modul tampilan
 * hanya menerima hasil yang sudah seragam: { berhasil, tautan } atau
 * { berhasil: false, pesan }.
 */

import { PESAN } from './konstanta.js';

export async function kirimTautanPendek(endpoint, dataFormulir) {
    try {
        const tanggapan = await fetch(endpoint, {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: dataFormulir,
        });

        const muatan = await tanggapan.json().catch(() => ({}));

        if (! tanggapan.ok) {
            return { berhasil: false, pesan: susunPesanGalat(muatan) };
        }

        return { berhasil: true, tautan: muatan.data };
    } catch {
        return { berhasil: false, pesan: PESAN.gagalJaringan };
    }
}

function susunPesanGalat(muatan) {
    const daftarGalat = Object.values(muatan?.errors ?? {}).flat();

    if (daftarGalat.length > 0) {
        return daftarGalat.join(' ');
    }

    return muatan?.message ?? PESAN.gagalUmum;
}
