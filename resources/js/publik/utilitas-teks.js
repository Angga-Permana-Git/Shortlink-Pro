/**
 * Utilitas murni: pengolahan teks dan waktu tanpa efek samping.
 */

/**
 * Ubah waktu ISO dari server menjadi teks tanggal dan jam Indonesia.
 * Mengembalikan null bila nilainya tidak bisa dibaca.
 */
export function formatWaktu(iso) {
    if (! iso) {
        return null;
    }

    const waktu = new Date(iso);

    if (Number.isNaN(waktu.getTime())) {
        return null;
    }

    return waktu.toLocaleString('id-ID');
}

/**
 * Isi penanda {nama} di dalam teks dengan nilainya.
 */
export function isiPenanda(teks, nilai) {
    return Object.entries(nilai).reduce(
        (hasil, [kunci, isian]) => hasil.replaceAll(`{${kunci}}`, isian),
        teks,
    );
}
