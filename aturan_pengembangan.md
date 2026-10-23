name: aturan-pengembangan
description: Pintu masuk dan alur kerja untuk semua pengembangan proyek; urutan baca dokumen, verifikasi wajib, format pelaporan AI, dan larangan umum.

# Aturan Pengembangan
Dokumen ini adalah titik awal setiap pekerjaan pada proyek apa pun. Dokumen ini mengatur alur kerja dan larangan umum. Struktur kode ada di `disiplin_struktur.md`, cara berpikir saat menulis kode ada di `disiplin_coding.md`, dan hal spesifik produk ada di `PRDapp.md`.

## 0. Isolasi Proyek (Wajib)
- Semua fakta spesifik proyek (nama stack, nama tabel, fitur, status migrasi) HANYA boleh diambil dari `PRDapp.md` dan folder `.aicontext/` di workspace saat ini.
- Dilarang keras membawa asumsi, history chat, atau detail teknis dari proyek lain ke dalam sesi ini.
- Jika informasi yang dibutuhkan tidak ditemukan di workspace saat ini, AI wajib bertanya kepada pengguna. Dilarang mengarang atau menebak berdasarkan pola proyek sebelumnya.

## 1. Urutan Membaca
Sebelum menulis kode, baca dokumen berikut secara berurutan:
1. `PRDapp.md`: tujuan produk, fitur, stack, dan keputusan.
2. `aturan_pengembangan.md`: dokumen ini.
3. `disiplin_struktur.md`: layer, struktur folder, routing, database, konfigurasi, error, dan logging.
4. `disiplin_coding.md`: cara berpikir dan batas perubahan.
5. `aicontext.md`: aturan folder `.aicontext/` (peta lokasi kode).
6. `changelog.md`: riwayat perubahan dan status terakhir.
7. `uji_e2e.md`: dibaca hanya saat aplikasi sudah selesai atau pengguna meminta verifikasi penuh.

Jika dua dokumen berbeda pendapat, ikuti instruksi langsung dari pengguna terlebih dahulu, lalu `PRDapp.md`, lalu dokumen yang memiliki topik tersebut.

## 2. Satu Topik, Satu Pemilik
| Topik | Dokumen Pemilik |
|---|---|
| Tujuan produk, fitur, stack, keputusan produk | `PRDapp.md` |
| Alur kerja, urutan baca, format pelaporan AI, larangan umum | `aturan_pengembangan.md` |
| Layer, struktur folder, routing, database, konfigurasi, error, logging | `disiplin_struktur.md` |
| Asumsi, kesederhanaan, blast radius, kriteria keberhasilan | `disiplin_coding.md` |
| Peta lokasi kode, dependensi, impact graph | `aicontext.md` |
| Riwayat perubahan | `changelog.md` |

## 3. Format Pelaporan AI (Wajib)
Setiap kali AI menyelesaikan tugas, AI wajib menyajikan jawaban dengan struktur ini:
1. **Ringkasan Perubahan:** (1-2 kalimat maksimal).
2. **Berkas yang Diubah:** (List path file).
3. **Dampak (Blast Radius):** (Apa yang mungkin terpengaruh oleh perubahan ini).
4. **Langkah Verifikasi:** (Perintah eksak yang harus Angga jalankan untuk memastikan ini berhasil).

## 4. Definition of Done (DoD)
Tugas tidak selesai hanya karena kode berjalan. Tugas dinyatakan selesai jika:
- Kode lolos lint dan pemeriksaan tipe.
- Test unit/feature hijau.
- `.aicontext/` dan `changelog.md` telah diperbarui.
- Format pelaporan AI (Poin 3) telah disajikan.

## 5. Sebelum Mengerjakan Tugas
1. Nyatakan asumsi dan tradeoff. Jika ada yang tidak jelas, tanyakan sebelum menulis kode.
2. Baca status terakhir di `changelog.md` dan tugas terbuka di `.aicontext/activetodo.md`.
3. Cari lokasi kode lewat `.aicontext/` lebih dulu. Buka berkas satu per satu hanya jika peta tidak cukup.
## 5.1 Inisialisasi Proyek Baru (Otomatis)
Jika `changelog.md` belum ada atau folder `.aicontext/` belum ada, AI wajib membuatnya secara otomatis sebelum mulai coding:
1. Buat `changelog.md` dari template di dokumen ini (bagian "Format Entri").
2. Buat folder `.aicontext/` dan isi berkas dasarnya (`README.md`, `folderstructure.md`, `coredependencies.md`, `activetodo.md`) sesuai spesifikasi di `aicontext.md`.
3. Beri tahu pengguna: *"File changelog.md dan folder .aicontext/ sudah dibuat otomatis. Saya siap mulai bekerja."*
4. Jangan membuat `PRDapp.md`. File ini harus dibuat manual oleh pengguna sebagai sumber kebenaran produk.

## 6. Verifikasi Setelah Setiap Perubahan Kode
Setiap perubahan kode wajib lolos perintah verifikasi yang tercatat di `PRDapp.md`. Urutan umumnya: Format/lint -> Pemeriksaan tipe -> Uji unit/feature -> Build. Jangan menonaktifkan pemeriksaan.

## 7. Perubahan Skema Database
Buat migrasi baru untuk setiap perubahan. Jangan mengedit migrasi yang sudah pernah dijalankan. Kolom baru harus didukung di setiap lapisan dan diberi nilai default.

## 8. Dokumen Tetap Sinkron
Dalam sesi yang sama dengan perubahan: Perbarui `changelog.md`, `.aicontext/`, dan `PRDapp.md` (jika keputusan produk berubah).

## 9. Larangan Umum
- Jangan commit atau push kecuali diminta.
- Jangan menambah/mengubah versi dependensi tanpa alasan tertulis.
- Jangan menyimpan rahasia di kode atau log.
- Jangan mengarang data yang seharusnya berasal dari sumber resmi.
- Jika tugas buntu, berhenti dan tanyakan. Jangan membuat asumsi baru.

## Lampiran A. Bahasa Tulisan
Tulis semua teks dalam Bahasa Indonesia baku.
| Jangan | Gunakan |
|---|---|
| cuba | coba |
| teruskan | lanjutkan |
| berjaya | berhasil |
| mencipta | membuat |
| boleh | bisa |
| hendak | ingin |
| semasa | saat |
| seterusnya | berikutnya |
| sahaja | saja |
| kerana | karena |
| guna | gunakan |
| tiada | tidak ada |
| butang | tombol |
| lalai | bawaan |
| baharu | baru |