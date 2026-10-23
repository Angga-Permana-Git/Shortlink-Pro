name: disiplin-coding
description: Pedoman perilaku untuk mengurangi kesalahan coding umum LLM: asumsi tersurat, traceability, blast radius, dan kesederhanaan.

# Disiplin Coding
Pedoman perilaku untuk mengurangi kesalahan coding yang umum dilakukan LLM. Fokus pada kehati-hatian dan kejelasan konteks. Struktur teknis dijelaskan di `disiplin_struktur.md`.

## 1. Berpikir Sebelum Menulis Kode
Jangan berasumsi. Ungkapkan tradeoff.
- Nyatakan asumsi secara eksplisit. Jika ragu, tanyakan.
- Jika ada lebih dari satu tafsir, sajikan semuanya.
- Jika ada pendekatan yang lebih sederhana, sebutkan.

## 2. Analisis Dampak (Blast Radius)
Sebelum mengubah fungsi, skema, atau modul yang sudah ada, AI wajib mengevaluasi:
- **Siapa yang memanggil ini?** (Cari semua referensi).
- **Apa risikonya jika saya ubah?** 
- Jika risikonya tinggi, tanyakan persetujuan sebelum mengeksekusi.

## 3. Jejak Digital (Traceability)
Sistem harus bisa dilacak. 
- Setiap log, error, dan event wajib menyertakan `request_id` atau `user_id` (jika ada). 
- Log tanpa konteks adalah sampah. Pastikan setiap jejak eksekusi bisa ditarik benang merahnya.

## 4. Idempotensi
Script, migrasi, atau fungsi yang memproses data harus *idempotent* (aman dijalankan ulang tanpa menghasilkan efek samping ganda). 
- Pastikan ada pengecekan state (misal: `if not exists`) sebelum mengeksekusi aksi destruktif atau insert data.

## 5. Kesederhanaan Dahulu
Kode minimum yang menyelesaikan masalah.
- Tidak ada fitur di luar yang diminta.
- Tidak ada abstraksi untuk kode yang hanya dipakai sekali.
- Tidak ada penanganan error untuk skenario yang mustahil terjadi.
- Tanyakan pada diri sendiri: "Apakah ini berlebihan?" Jika ya, sederhanakan.

## 6. Perubahan Tepat Sasaran
Sentuh hanya yang perlu disentuh.
- Jangan memperbaiki kode/komentar di sekitarnya yang tidak berkaitan dengan tugas.
- Hapus sisa (import/variabel) yang dibuat oleh perubahan Anda sendiri.
- Ujiannya: setiap baris yang diubah harus bisa ditelusuri langsung ke permintaan pengguna.

## 7. Pelaksanaan Berbasis Tujuan
Ubah tugas menjadi tujuan yang bisa diverifikasi.
- "Perbaiki bug ini" menjadi "tulis tes yang mereproduksi bug, lalu buat tes itu lulus".
- Untuk tugas bertahap, nyatakan rencana singkat: `[Langkah] → verifikasi: [pات pemeriksaan]`.

## 8. Tampilan (CSS dan JavaScript)
- Ubah gaya hanya pada komponen yang diminta.
- Gunakan token yang sudah ada. Jangan menulis nilai mentah.
- Cari penyebab konflik gaya, jangan menutupinya dengan `!important`.
- Verifikasi tampilan harus nyata (dijalankan di browser), bukan hanya dibaca dari kode.