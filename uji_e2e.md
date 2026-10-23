name: uji-e2e
description: Pedoman penulisan, eksekusi, dan pemeliharaan uji End-to-End (E2E). Fokus pada pembuktian fungsional, penutupan celah keamanan (Critical/High), isolasi data, dan kemudahan troubleshooting.

# Uji End-to-End (E2E)
Uji E2E memvalidasi alur pengguna kritis dari antarmuka hingga database. Dokumen ini bukan pengganti unit test, melainkan jaring pengaman terakhir untuk memastikan fitur inti berfungsi dengan benar dan aman dari penyalahgunaan.

## 1. Filosofi & Prinsip Utama (The Mantra)
1. **Tidak Ada Fitur Tanpa Bukti:** Setiap alur kritis (login, checkout, registrasi, ubah hak akses) wajib memiliki skenario E2E yang menghasilkan bukti eksekusi (screenshot, trace, atau video).
2. **Mindset Pentester (Negative Testing):** Jangan hanya menguji "happy path". Wajib uji skenario keamanan untuk menutup celah Critical/High:
   - **IDOR:** Pastikan User A tidak bisa membaca/mengubah data User B hanya dengan mengganti ID di URL atau payload.
   - **Privilege Escalation:** Pastikan akun *user* biasa ditolak (403 Forbidden) saat mencoba mengakses endpoint atau halaman `/admin`.
   - **Session Management:** Pastikan token/session benar-benar invalid di server setelah logout, bukan hanya dihapus dari browser.
3. **Isolasi Data Mutlak:** Setiap test harus membuat datanya sendiri (via API atau UI) dan membersihkannya setelah selesai (*teardown*). Jangan berasumsi database dalam keadaan kosong.
4. **Dilarang Keras `sleep()` atau `wait(waktu)`:** Selalu tunggu kondisi eksplisit (elemen muncul, network idle, atau teks berubah). Waktu tunggu statis adalah sumber utama *flaky test*.
5. **Selector yang Tangguh:** Cari elemen berdasarkan peran (`getByRole`), label, atau `data-testid`. Dilarang mengandalkan kelas CSS atau struktur DOM yang rapuh.

## 2. Kapan Dijalankan
- **Perubahan Biasa (Refactor, UI minor):** Tidak perlu E2E. Cukup verifikasi standar (lint, unit, feature test).
- **Fitur Baru / Aplikasi Selesai:** Wajib jalankan seluruh *suite* E2E. Target: 100% lulus.
- **Pre-Deployment:** Wajib dijalankan di pipeline CI/CD sebelum *merge* ke branch utama.

## 3. Struktur dan Penulisan Test
Letakkan berkas uji di `tests/e2e/` dengan nama `[fitur].e2e.[ekstensi]`.

### Aturan Penulisan
- **Satu test, satu alur:** Jangan menggabungkan skenario berbeda dalam satu fungsi test.
- **Setup & Teardown:** 
  - *Setup:* Buat data yang dibutuhkan via API langsung (lebih cepat dan stabil) atau UI (jika API tidak tersedia).
  - *Teardown:* Hapus data yang dibuat. Gunakan blok `afterEach` atau `afterAll` untuk memastikan database bersih.
- **Fungsi Helper:** Jika ada langkah yang diulang (misal: "login sebagai admin" atau "buat user dummy"), buat fungsi helper di folder `tests/e2e/helpers/`. Jangan menulis ulang kode yang sama.

### Contoh Pola (Gaya Playwright/Cypress)
```javascript
test("pengguna biasa TIDAK bisa mengakses halaman admin (Privilege Escalation)", async ({ page, request }) => {
  // 1. Setup: Buat user biasa via API
  const user = await api.buatUser({ role: 'user' });
  await halamanLogin.login(page, user);

  // 2. Aksi: Coba akses endpoint atau halaman admin secara langsung
  await page.goto("/admin/settings");
  
  // 3. Assert: Harus ditolak (redirect ke login atau halaman 403)
  await expect(page).toHaveURL("/login"); // atau expect status 403 jika API call
  
  // 4. Teardown: Bersihkan data
  await api.hapusUser(user.id);
});
4. Penanganan Kegagalan (Troubleshooting)
Ketika E2E gagal, jangan langsung menaikkan timeout atau mengulang test secara membabi buta. Lakukan diagnosis read-only:
Baca Artefak: Periksa screenshot kegagalan, trace (jika ada), dan log browser/server.
Cocokkan Timestamp: Gunakan trace_id atau request_id dari test untuk mencari log error yang persis di server.
Tentukan Akar Masalah:
Bug Aplikasi: Perbaiki kode aplikasi.
Test yang Salah: Perbaiki selector atau logika assert.
Flaky Test (Gagal acak): Cari race condition (misal: test mengklik tombol sebelum animasi selesai). Isolasi test ini dan perbaiki logika tunggunya.
Dilarang Masking: Jangan "memperbaiki" test yang gagal dengan mengubah expect(A).toBeVisible() menjadi expect(A).not.toBeVisible() atau menambah sleep(5000).
5. Konfigurasi dan Lingkungan
Headless Mode: Test wajib bisa berjalan di mode headless (tanpa UI browser) untuk CI/CD.
Environment Terpisah: E2E wajib dijalankan di database dan URL yang terpisah dari development/production.
Variabel Lingkungan: URL base, kredensial test, dan API key test wajib diambil dari .env.test, bukan di-hardcode di dalam berkas test.
6. Jebakan Umum & Solusi
Jebakan
Solusi
Test gagal karena elemen belum muncul
Gunakan await expect(lokator).toBeVisible() atau tunggu network idle. Jangan pakai sleep.
Test gagal acak (flaky)
Cek race condition. Pastikan semua aksi asinkron (fetch, animasi) sudah selesai sebelum assert.
Data sisa dari test sebelumnya merusak test berikutnya
Perbaiki logika teardown. Pastikan setiap test mandiri (independent).
Selector rusak karena UI di-redesign
Gunakan data-testid="submit-btn" atau getByRole('button', { name: 'Kirim' }).
Test sangat lambat
Kurangi interaksi UI untuk setup data. Gunakan API call langsung untuk membuat data dummy.
Celah keamanan terlewat
Wajib tambahkan skenario Negative Testing (IDOR, Privilege Escalation) untuk setiap fitur yang memanipulasi data atau hak akses.
7. Integrasi dengan Alur Kerja AI
Saat AI menulis atau memperbaiki test E2E, AI wajib:
Memastikan tidak ada sleep() atau hardcoded wait.
Memastikan ada mekanisme teardown data.
Menyertakan minimal satu skenario Negative Testing (keamanan) jika fitur melibatkan hak akses atau data sensitif.
Melaporkan hasil eksekusi test di format pelaporan standar (lihat aturan_pengembangan.md), termasuk jumlah test yang lulus/gagal dan lokasi artefak jika ada yang gagal.
