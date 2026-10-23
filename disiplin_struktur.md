name: disiplin-struktur
description: Pakem struktur kode: pemisahan layer, routing, database, konfigurasi, error handling, logging, penamaan, dan checklist sebelum selesai.

# Disiplin Struktur
Prinsip: kode yang baik mudah dibaca, diuji, dan dilacak. Satu file memiliki satu tujuan.

## 1. Layer dan Tanggung Jawab
| Layer | Tugas | Dilarang |
|---|---|---|
| Handler / Controller | Terima request, validasi, panggil service, return response | Query DB langsung; logika bisnis; > 30 baris |
| Service / Action | Logika bisnis; koordinasi model & API eksternal | Mengetahui detail HTTP; > 100 baris |
| Model / Repository | Representasi data dan query database | Logika bisnis kompleks; > 50 baris |
| Validasi / Schema | Memeriksa bentuk dan nilai input | Logika bisnis |
| Komponen UI (frontend) | Menampilkan data | Query DB; memanggil API langsung; > 150 baris |

## 2. Batas Ukuran (Bawaan)
- Handler: 30 baris | Service: 100 baris | File Routing: 100 baris | Komponen UI: 150 baris.
- Jika melewati batas, pecah berdasarkan tanggung jawab.

## 3. Routing & Database
- Pecah routing per domain dan versi (`/api/v1/users`).
- Jangan mengedit migrasi yang sudah dijalankan. Buat migrasi baru untuk setiap perubahan logis.

## 4. Konfigurasi dan Rahasia
- Semua konfigurasi berasal dari environment variable (`.env`).
- **Larangan Magic Numbers:** Jangan *hardcode* batas nilai, durasi, atau angka bisnis di dalam logika. Pindahkan ke konfigurasi atau konstanta terpusat.
- `.env` wajib masuk `.gitignore`.

## 5. Error Handling (Wajib untuk Troubleshoot)
- Buat exception khusus yang mewarisi satu base exception.
- Dilarang blok catch kosong.
- Format error wajib menyertakan `error_code` (untuk mesin) dan `message` (untuk manusia).
- **Trace ID:** Sertakan `trace_id` atau `request_id` di response header dan body error, agar mudah dicocokkan dengan log server.
- Jangan mengirim stack trace ke client.

## 6. Logging (Konsolidasi Teknis)
- **Dilarang** menggunakan `console.log`, `echo`, atau `print` untuk produksi. Gunakan logger resmi.
- **Wajib Konteks:** Setiap baris log wajib menyertakan `request_id` dan `user_id` (jika ada).
- Gunakan level yang tepat: `debug`, `info`, `warning`, `error`.
- **Sanitasi:** Dilarang mencatat password, token, nomor kartu, atau API key dalam bentuk apa pun.
- Log error wajib menyertakan `stack trace` di sisi server, dan `trace_id` agar bisa di-*join* dengan log client.

## 7. Penamaan
Ikuti konvensi bahasa/framework:
- Controller: `PascalCase + Controller`
- Service/Action: `PascalCase + Action`
- Model: `PascalCase`, tunggal
- DTO: `PascalCase + DTO`
- Migrasi: `snake_case` dengan timestamp

## 8. Antipattern yang Dilarang
- Fat controller / God model / Smart component.
- Catch kosong / Rahasia di kode.
- Gaya inline (`style="..."`) dan handler inline (`onclick="..."`).
- Magic numbers di logika bisnis.

## 9. Checklist Sebelum Selesai
- [ ] Handler < 30 baris, Service < 100 baris.
- [ ] Tidak ada secret di kode.
- [ ] Error handling menggunakan format seragam dan menyertakan `trace_id`.
- [ ] Log menggunakan logger resmi, menyertakan `request_id`, dan tidak memuat data sensitif.
- [ ] Tidak ada magic numbers (semua masuk config/konstanta).
- [ ] Test unit/feature lolos.