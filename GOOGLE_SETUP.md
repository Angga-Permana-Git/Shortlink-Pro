# Setup Masuk dengan Google - Aplikasi Shortlink

Dokumen ini menjelaskan cara mengaktifkan tombol **Masuk dengan Google** pada halaman masuk aplikasi Shortlink. Fitur ini memakai driver Google bawaan `laravel/socialite`, jadi tidak ada paket tambahan yang perlu dipasang.

## 1. Prasyarat

- Punya akses ke Google Cloud Console untuk membuat OAuth Client.
- Domain aplikasi sudah pasti dan bisa diakses Google (produksi memakai HTTPS).
- Aplikasi sudah bisa dijalankan (`php artisan serve` atau web server lain).

## 2. Membuat OAuth Client di Google Cloud

1. Buka [Google Cloud Console](https://console.cloud.google.com/) lalu pilih atau buat proyek.
2. **APIs & Services → OAuth consent screen**: pilih **External**, isi nama aplikasi, email dukungan, dan domain. Tambahkan scope dasar (`email`, `profile`, `openid` sudah dipakai aplikasi).
3. **APIs & Services → Credentials → Create credentials → OAuth client ID**.
   - **Application type**: `Web application`
   - **Authorized JavaScript origins**: `https://shortlink.example.com`
   - **Authorized redirect URIs**: `https://shortlink.example.com/auth/google/callback`
4. Simpan, lalu salin **Client ID** dan **Client secret**.

Redirect URI harus sama persis dengan `APP_URL` + `/auth/google/callback`; perbedaan satu karakter saja membuat Google menolak dengan galat `redirect_uri_mismatch`.

## 3. Konfigurasi `.env`

```dotenv
APP_URL=https://shortlink.example.com

GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=rahasia-dari-google
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

- Tombol Google **hanya tampil** bila `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` terisi. Tanpa keduanya, halaman masuk berjalan seperti biasa.
- `GOOGLE_REDIRECT_URI` wajib URL absolut. Bila dikosongkan, aplikasi memakai `APP_URL` + `/auth/google/callback`.
- Setelah mengubah `.env`, jalankan `php artisan config:clear` bila konfigurasi sedang di-cache.

## 4. Alur Masuk

1. Tamu menekan **Masuk dengan Google** (tombol ini juga tampil saat mode wajib SSO aktif).
2. `GET /auth/google/redirect` mengarahkan tamu ke halaman persetujuan Google.
3. `GET /auth/google/callback` memeriksa state sesi yang dipasang Socialite, lalu mengambil klaim pengguna.
4. Akun disiapkan oleh `GoogleAccountService`:
   - cocok dengan `google_sub` → akun itu yang dipakai;
   - email terverifikasi sama dengan akun **lokal** → akun itu ditautkan (kata sandi lama tetap berlaku);
   - email sudah dipakai akun Keycloak → ditolak;
   - email belum terverifikasi atau akun nonaktif → ditolak;
   - selain itu akun baru dibuat (peran `user`, tanpa kata sandi).
5. Pengguna diarahkan ke panel sesuai perannya (`/admin` atau `/user`).

Setiap percobaan dicatat di `login_audit_logs` dengan provider `google`.

## 5. Kata Sandi Akun Google

Akun yang dibuat lewat Google tidak punya kata sandi. Karena itu:

- Login lokal (`/login/local`) tidak bisa dipakai akun tersebut.
- Fitur lupa kata sandi menampilkan penjelasan bahwa kata sandinya diatur di akun Google, bukan mengirim tautan atur ulang.

Akun lokal yang **ditautkan** ke Google tetap punya kata sandi dan tetap boleh memakai kedua jalur masuk.

## 6. Troubleshooting

| Gejala | Penyebab dan tindakan |
|---|---|
| Tombol tidak muncul | `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` kosong, atau konfigurasi masih di-cache (`php artisan config:clear`). |
| `redirect_uri_mismatch` di Google | `GOOGLE_REDIRECT_URI` berbeda dari Authorized redirect URI di Google Cloud. |
| Pesan "Sesi masuk Google tidak valid" | State sesi hilang (cookie sesi tidak tersimpan atau halaman dibuka ulang). Coba lagi dari halaman masuk. |
| Pesan "Masuk dengan Google ditolak: email Google tidak terverifikasi" | Akun Google belum memverifikasi emailnya. |
| Pesan "email sudah dipakai penyedia masuk lain" | Email itu milik akun SSO Keycloak. Masuk lewat SSO. |
| Pesan "Masuk dengan Google sedang tidak tersedia" | Klaim gagal diambil dari Google (jaringan, kredensial salah, atau kredensial dicabut). Periksa `storage/logs/laravel.log`. |

## 7. Menonaktifkan Google (Rollback)

Kosongkan `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET`, lalu jalankan `php artisan config:clear`. Tombol Google hilang; akun yang sudah dibuat lewat Google tetap ada dan kolom `google_sub` tetap terisi (dapat dikosongkan manual bila akunnya ingin dihapus tautannya).
