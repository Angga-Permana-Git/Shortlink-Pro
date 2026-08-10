# Setup SSO Keycloak — Aplikasi Shortlink

Dokumen ini menjelaskan cara mengonfigurasi dan menjalankan login SSO melalui Keycloak di aplikasi Shortlink.

## Arsitektur

```
User
  ↓ Login via Keycloak SSO
Keycloak (verifikasi identitas, termasuk user LDAP)
  ↓ token OIDC
Aplikasi Shortlink (validasi token, provisioning user, role management)
  ↓
MySQL (role aplikasi, akun lokal, audit log)
```

- **Keycloak** = Authentication (hanya memastikan user valid)
- **Aplikasi** = Authorization (menentukan role user/admin dari database lokal)

## 1. Prasyarat

- Keycloak sudah berjalan dan bisa diakses aplikasi
- Keycloak sudah terhubung dengan LDAP internal (jika dipakai)
- MySQL running
- Aplikasi memakai HTTPS di production

## 2. Membuat Client di Keycloak

1. Buka konsol admin Keycloak (Realm Anda, misal `internal`).
2. **Clients → Create client**.
3. Isi:
   - **Client ID**: `shortlink-app`
   - **Client type**: `OpenID Connect (OIDC)`
   - **Client authentication**: `ON` (confidential)
4. Pada tab **Settings**, atur:
   - **Valid redirect URIs**: `https://shortlink.internal.local/auth/keycloak/callback`
   - **Valid post logout redirect URIs**: `https://shortlink.internal.local`
5. **Scopes**: pastikan `openid`, `profile`, `email` aktif.
6. Catat **Client secret** di tab **Credentials**.

### Claim yang dibutuhkan aplikasi

- `sub`
- `preferred_username`
- `name`
- `email`

## 3. Konfigurasi `.env`

Buka file `.env` dan isi nilai berikut (contoh):

```env
KEYCLOAK_BASE_URL=https://keycloak.internal.local
KEYCLOAK_REALM=internal
KEYCLOAK_CLIENT_ID=shortlink-app
KEYCLOAK_CLIENT_SECRET=change-this-secret
KEYCLOAK_REDIRECT_URI=https://shortlink.app/auth/keycloak/callback
KEYCLOAK_LOGOUT_REDIRECT_URI=https://shortlink.app
```

Jangan pernah menyimpan secret ini di source code / commit.

## 4. Install Dependensi & Migrasi

Di terminal dari folder project:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

## 5. Membuat Local Super Admin (Bootstrap)

Akun lokal digunakan untuk bootstrap awal dan pemulihan saat Keycloak down.

```bash
php artisan app:create-local-admin
```

Prompt yang diminta:
- Username
- Nama
- Password (minimal 12 karakter, tidak ditampilkan di console)
- Konfirmasi password

User ini tersimpan dengan `auth_provider=local`, `role=admin`, `is_protected=true`, `is_active=true`.

## 6. Alur Login

### Login normal (SSO)
1. User membuka aplikasi.
2. Aplikasi mengalihkan ke Keycloak.
3. User login di Keycloak.
4. Keycloak kembali ke aplikasi dengan authorization code.
5. Aplikasi menukar code → token → validasi → login.
6. Aplikasi membuat/update user di MySQL, tentukan role.
7. User masuk ke panel.

### Login darurat / lokal
1. Buka `/login/local`.
2. Masukkan username & password akun lokal.
3. Semua aktivitas tercatat di audit log.

## 7. Catatan Desain

- User baru via Keycloak default ber-role `user`.
- Perubahan role dilakukan admin melalui menu **Admin → Users**.
- Local super admin hanya dibutuhkan saat Keycloak down.

## 8. Troubleshooting

| Masalah | Solusi |
| --- | --- |
| Tombol SSO tidak muncul | `KEYCLOAK_BASE_URL`/`KEYCLOAK_REALM` kosong di `.env`; halaman login menampilkan form lokal & pesan "SSO belum dikonfigurasi" |
| Error `redirect_uri mismatch` | Pastikan `KEYCLOAK_REDIRECT_URI` sama persis dengan "Valid redirect URIs" di Keycloak |
| Error `state tidak valid` saat callback | Refresh halaman atau login ulang (state hanya sekali pakai) |
| SSO tidak tersedia | Gunakan login lokal `/login/local` |
| User baru tidak jadi admin | Sesuai desain: default `user`, ubah via **Admin → Users** |

## 9. Keamanan

- Tidak menyimpan password LDAP/Keycloak di DB aplikasi
- Password akun lokal di-hash (Argon2/bcrypt)
- Login lokal dilindungi rate limiting
- Perubahan role dicatat di `role_change_logs`
- Akun protected hanya bisa diubah oleh local admin

## 10. Menonaktifkan SSO (Rollback)

- Kosongkan `KEYCLOAK_BASE_URL` & `KEYCLOAK_REALM` di `.env`
- Jalankan `php artisan config:clear`
- Form login lokal akan kembali tampil sebagai jalur masuk