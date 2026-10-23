name: aicontext
description: Spesifikasi folder .aicontext/ sebagai peta lokasi kode lokal untuk AI: tujuan, struktur, isolasi Git, dan format impact graph.

# Spesifikasi `.aicontext/`

## 1. Tujuan
Folder `.aicontext/` adalah peta lokal untuk AI assistant. 
Prinsip: Bukan bagian dari aplikasi (produksi tidak boleh meng-import ini). Terisolasi dari Git. Peta, bukan salinan kode. Diperbarui bersama kode.

## 2. Struktur
```text
.aicontext/
├── README.md             Ringkasan singkat dan petunjuk untuk AI
├── folderstructure.md    Peta level 1: struktur folder utama
├── coredependencies.md   Peta level 2: alur request, modul kritis, dan impact graph
├── activetodo.md         Tugas yang masih terbuka
├── apimap.md             (opsional) daftar endpoint dan lokasi handler
└── flows/                (opsional) alur per domain bisnis

3. Isolasi dari Git
Folder ini wajib ada di .gitignore:
gitignore
# AI context (lokal, jangan di-commit)
.aicontext/
# AI context (lokal, jangan di-commit).aicontext/

## 4. Inisialisasi (pertama kali)
Inisialisasi dipicu ketika AI pertama kali bekerja di proyek ini dan mendeteksi bahwa folder `.aicontext/` belum ada.
Langkah:
1. Buat folder `.aicontext/` dan berkas dasar: `README.md`, `folderstructure.md`, `coredependencies.md`, dan `activetodo.md`.
2. Pastikan `.gitignore` memuat `.aicontext/` (jika belum, beri tahu pengguna untuk menambahkannya).
3. Baca struktur proyek satu kali, lalu isi `folderstructure.md` dan `coredependencies.md`.
4. Isi `activetodo.md` dengan tugas terbuka yang diketahui dari `PRDapp.md`, atau biarkan kosong.
5. Beri tahu pengguna dengan kalimat seperti ini:
   *"Folder `.aicontext/` sudah dibuat dan diisi peta awal. Folder ini terisolasi dari Git dan akan saya perbarui setiap ada perubahan kode."*
**Catatan:** AI tidak boleh membuat `PRDapp.md`. File ini adalah sumber kebenaran produk yang harus dibuat manual oleh pengguna.

5. Format Berkas folderstructure.md
# Struktur Folder
## Root
- `[folder]/`: [fungsi]
## Catatan
- Area yang paling sering berubah: [folder]
coredependencies.md (Wajib memuat Impact Graph)
# Dependensi Inti & Dampak
## Alur request utama
1. Request masuk → `[lokasi route]`
2. Handler memanggil → `[lokasi service]`
3. Service memakai → `[lokasi model]`
## Grafik Dampak (Impact Graph)
*Gunakan ini untuk memetakan efek domino. Jika Modul A diubah, apa yang harus dicek?*
- Jika `[Modul A / File A]` diubah → Wajib cek `[Modul B]` dan `[Modul C]`.
- Jika `[Skema Tabel Users]` diubah → Wajib cek `[Service Auth]`, `[Service Profile]`, dan `[API Client]`.
## Modul Kritis & Depresiasi
- `[Nama modul]`: `[lokasi]`, dipakai oleh [daftar pemakai].
- `@deprecated [Nama fungsi/file]`: [lokasi]. Jangan dipakai untuk fitur baru. Gunakan `[Alternatif]` sebagai gantinya.
activetodo.md 
# Tugas Aktif
Fase: [pengembangan / pemeliharaan]
Terakhir diperbarui: YYYY-MM-DD
## Tugas terbuka
- [ ] [tugas]

6. Aturan Pemeliharaan
Setiap perubahan kode harus diikuti pembaruan peta:
Menambah/memindah folder → Update folderstructure.md.
Mengubah alur/dependensi → Update coredependencies.md (terutama Impact Graph).
Menemukan fungsi lama yang tidak boleh dipakai lagi → Tandai @deprecated di coredependencies.md.

7. Validasi
Untuk memastikan peta berfungsi, lakukan tiga tes:
Isolasi Git: Jalankan git status. .aicontext/ tidak boleh muncul.
Navigasi: AI dapat menunjuk berkas yang tepat setelah membuka paling banyak dua berkas peta.
Pemeliharaan Otomatis: Saat AI menambah endpoint baru, apimap.md atau coredependencies.md ikut diperbarui secara otomatis.
8. Larangan
Jangan meng-import berkas dari .aicontext/ ke kode produksi.
Jangan commit folder .aicontext/.
Jangan menyalin baris kode panjang ke dalam peta. Cukup tunjuk lokasinya.