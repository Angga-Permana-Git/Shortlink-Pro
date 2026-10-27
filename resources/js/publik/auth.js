/**
 * Interaksi halaman daftar akun.
 *
 * Satu-satunya tugas modul ini adalah tombol lihat/sembunyikan kata sandi.
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
