/**
 * Interaksi halaman daftar akun.
 *
 * Dua tugas modul ini: tombol lihat/sembunyikan kata sandi, dan kunci tombol
 * daftar sampai checkbox persetujuan S&K + privasi dicentang (DFB Bagian 4.2).
 * Pemasangan memakai `document.querySelectorAll` sekali saat halaman dimuat
 * karena formulir daftar adalah halaman Blade biasa (bukan Livewire), sehingga
 * elemen tidak diganti setelah interaksi.
 */

document.querySelectorAll('[data-lihat-sandi]').forEach((tombol) => {
    const idKolom = tombol.getAttribute('data-lihat-sandi');
    const kolom = idKolom ? document.getElementById(idKolom) : null;

    if (!(kolom instanceof HTMLInputElement)) {
        return;
    }

    const labelLihat = tombol.dataset.labelLihat ?? 'Tampilkan kata sandi';
    const labelSembunyikan = tombol.dataset.labelSembunyikan ?? 'Sembunyikan kata sandi';

    tombol.addEventListener('click', () => {
        const akanTampil = kolom.type === 'password';

        kolom.type = akanTampil ? 'text' : 'password';
        tombol.setAttribute('aria-pressed', String(akanTampil));
        tombol.setAttribute('aria-label', akanTampil ? labelSembunyikan : labelLihat);

        // Kembalikan fokus ke kolom agar pengguna bisa langsung mengetik.
        kolom.focus({ preventScroll: true });
    });
});

// Kunci tombol daftar sampai checkbox persetujuan dicentang; tombol abu-abu
// (nonaktif) mencegah kirim formulir tanpa persetujuan eksplisit.
const kotakSetuju = document.querySelector('input[name="setuju"]');
const tombolDaftar = document.querySelector('form.form-auth button.tombol-auth[type="submit"]');

if (kotakSetuju instanceof HTMLInputElement && tombolDaftar instanceof HTMLButtonElement) {
    const sinkron = () => {
        tombolDaftar.disabled = !kotakSetuju.checked;
    };

    kotakSetuju.addEventListener('change', sinkron);
    sinkron();
}
