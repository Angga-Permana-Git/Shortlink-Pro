<div align="center">

# Shortlink Pro

**Pemendek tautan self-hosted: pemakaian publik, panel pengguna, panel admin, dan Linktree dalam satu aplikasi.**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-5-FDAE4B)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2.35%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![License](https://img.shields.io/badge/Lisensi-MIT-3DA639)](LICENSE)

[Fitur](#fitur) · [Instalasi](#mulai-cepat) · [Konfigurasi](#konfigurasi) · [Deployment](#deployment) · [Pengujian](#pengujian) · [Status](#status-pengembangan)

</div>

---

## Tentang

Shortlink Pro adalah aplikasi pemendek tautan yang dapat dipakai untuk dua kebutuhan sekaligus:

- **Publik** — pengunjung dapat memendekkan tautan tanpa membuat akun melalui beranda atau `POST /api/v1/links`.
- **Terkelola** — pengguna dengan akun dapat mengelola tautan, melihat statistik klik, mengekspor data, dan memiliki halaman Linktree pribadi. Administrator memegang kendali penuh lewat panel Filament.

Administrator juga dapat mengaktifkan **wajib SSO**, sehingga tamu diarahkan ke Keycloak sebelum dapat memakai aplikasi. Setiap tautan memiliki pemilik yang jelas, masa berlaku opsional, proteksi kata sandi, dan analitik klik.

Antarmuka tersedia dalam **dua bahasa** (Bahasa Indonesia dan English) dan mengikuti pilihan setiap pengguna: tamu memakai sesi sendiri, sedangkan pengguna yang masuk memakai kolom `users.locale`. Pilihan bahasa yang dibuat di halaman daftar atau masuk ikut tersimpan ke akun, jadi tetap berlaku pada kunjungan berikutnya.

## Fitur

### Tautan pendek

- Slug otomatis atau kustom (huruf, angka, `-`, `_`; maksimal 100 karakter).
- Validasi tujuan `http`/`https` maksimal 2048 karakter.
- Masa berlaku, status aktif/nonaktif, dan penghapusan lunak (soft delete).
- Proteksi kata sandi dengan hash dan penguncian sementara setelah percobaan gagal berulang.
- Halaman pembuka kata sandi sebelum pengalihan.
- Kode QR per tautan dengan cache yang dapat diatur.
- Pemeriksaan tujuan ke **Google Safe Browsing** sebelum tautan disimpan (dapat dimatikan atau fail-open bila kunci API kosong).
- Slug yang dicadangkan administrator tidak dapat dipakai pengguna.

### Analitik

- Jumlah klik, waktu klik terakhir, status pengalihan, referrer, dan user agent.
- Alamat IP pengunjung disimpan sebagai hash, bukan alamat asli.
- Widget panel: tren pertumbuhan, tautan populer, domain tujuan, peta panas klik, pengguna aktif, dan kesehatan akun.
- Filter periode dashboard.

### Linktree

- Profil publik di `/u/{alamat}` dengan slug publik yang dapat diubah.
- Daftar tautan yang dapat diurutkan, masing-masing dengan penghitung klik.
- Pencatatan event klik Linktree untuk analitik.

### Akun dan bahasa

- Registrasi lokal di `/register` dengan contoh isian (nama, nama pengguna, email) agar pola pengisian langsung terlihat.
- Petunjuk nama pengguna (`pattern="[A-Za-z0-9_-]+"`) dan syarat kata sandi ("Minimal 12 karakter.") tampil sebelum formulir dikirim.
- Tombol **lihat/sembunyikan kata sandi** pada kolom kata sandi dan ulang kata sandi, lengkap dengan `aria-pressed`, `aria-controls`, dan ikon mata.
- Pilihan bahasa dapat diganti dari beranda maupun bilah atas panel; pilihan tamu disimpan di sesi, pilihan pengguna masuk di kolom `users.locale`.
- Pilihan bahasa pada halaman daftar/masuk ikut tersimpan ke akun setelah autentikasi berhasil (`LocaleService::simpanDariSesi()`), kecuali saat admin melakukan impersonasi.
- Bahasa antarmuka: Bahasa Indonesia (bawaan) dan English; pesan validasi, autentikasi, dan atur ulang kata sandi juga sudah diterjemahkan.

### Panel dan operasional

- **Panel pengguna**: dashboard, CRUD tautan, pengaturan Linktree, ekspor CSV.
- **Panel admin**: pengguna, tautan, profil Linktree, audit log, audit login, riwayat perubahan peran, impor CSV, ekspor log, impersonasi pengguna.
- Pengaturan admin: branding (logo/nama), keamanan akun (2FA, IP admin yang diizinkan, wajib SSO), operasional (slug dicadangkan, kedaluwarsa bawaan, retensi log, batas laju, pengumuman), dan zona waktu.
- Notifikasi dalam aplikasi untuk admin (mis. tautan bermasalah atau antrean perhatian).
- Perintah terjadwal: pembersihan log harian, pengingat operasional per jam, dan denyut penjadwal untuk widget status.

### API

| Metode | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/api/v1/links` | Membuat tautan publik. Body: `destination_url` (wajib), `slug`, `expires_at`, `password` (opsional). |
| `GET` | `/api/v1/linktree` | Profil Linktree milik pengguna yang sedang login. |
| `PUT` | `/api/v1/linktree` | Memperbarui profil/tautan Linktree. |

API memakai sesi login (cookie) untuk endpoint Linktree; belum ada autentikasi API key. Galat validasi dikembalikan sebagai `422` dengan `error_code`, `message`, `trace_id`, dan `errors`.

## Teknologi

| Lapisan | Teknologi |
|---------|-----------|
| Backend | Laravel 12, PHP 8.2.35+ |
| Panel | Filament 5, Livewire 4 |
| Frontend | Blade, Tailwind CSS 4, Vite 7 |
| Basis data | MySQL 8 (produksi) atau SQLite (pengembangan/uji) |
| Sesi/cache/antrean | Driver `database` (tanpa Redis) |
| Deployment | Docker (PHP 8.3.36 + Apache), nginx host, Cloudflare Tunnel |

## Kebutuhan

- **PHP 8.2.35 atau lebih baru** — versi ini memuat perbaikan PHP2Shell (CVE-2026-109618/109619). Image Docker memakai 8.3.36.
- Composer 2.
- Node.js 22 dan npm 10 (lockfile `package-lock.json`).
- MySQL 8 untuk produksi; SQLite cukup untuk pengembangan dan pengujian.
- Ekstensi PHP: `bcmath`, `curl`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `zip`.

## Mulai Cepat

```bash
git clone <url-repository> Shortlink-Pro
cd Shortlink-Pro

composer install
npm ci

cp .env.example .env
php artisan key:generate

# SQLite (paling cepat untuk mencoba)
touch database/database.sqlite
php artisan migrate --seed

npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`. Panel admin ada di `/admin`, panel pengguna di `/user`.

### Akun awal

```bash
# Akun admin lokal (break-glass), ditanyakan secara interaktif
php artisan app:create-local-admin

# Akun demo untuk pengembangan (admin@company.local, user@company.local)
php artisan app:create-demo-accounts
```

Kata sandi akun demo diambil dari `DEMO_ACCOUNT_PASSWORD`; bila kosong, aplikasi membuat kata sandi acak. Jangan mengaktifkan akun demo di produksi.

### Perintah harian

```bash
php artisan test                 # seluruh test fitur/unit
vendor/bin/pint --test           # cek gaya kode
vendor/bin/pint                  # perbaiki otomatis
npm run build                    # aset produksi
php artisan schedule:work        # penjadwal saat pengembangan
php artisan logs:purge           # bersihkan log melewati batas retensi
```

## Konfigurasi

Salin `.env.example` lalu sesuaikan. Kunci yang paling penting:

| Variabel | Keterangan |
|----------|------------|
| `APP_URL` | URL publik aplikasi (HTTPS di produksi). Dipakai untuk tautan pendek, QR, dan callback OAuth. |
| `APP_KEY` | Wajib; samakan nilainya bila menjalankan beberapa container. |
| `APP_ENV`, `APP_DEBUG` | Produksi: `production` dan `false`. |
| `APP_LOCALE` | `id` (bawaan) atau `en`. |
| `DB_*` | MySQL produksi; `DB_CONNECTION=sqlite` untuk pengembangan. |
| `SESSION_SECURE_COOKIE` | `true` bila publik diakses lewat HTTPS, termasuk di balik Cloudflare Tunnel. |
| `PEMENDEK_SAFE_BROWSING` | `true` untuk mengaktifkan pemeriksaan tujuan. |
| `PEMENDEK_SAFE_BROWSING_KEY` | Kunci Google Safe Browsing API. Bila kosong, pemeriksaan dilewati (fail-open). |
| `PEMENDEK_QR_CACHE_SECONDS` | Lama cache QR, bawaan `86400`. |
| `PEMENDEK_PASSWORD_MAX_ATTEMPTS`, `PEMENDEK_PASSWORD_LOCK_SECONDS` | Batas percobaan dan durasi penguncian kata sandi tautan. |
| `KEYCLOAK_*` | Kosongkan bila tidak memakai SSO. Panduan: [KEYCLOAK_SETUP.md](KEYCLOAK_SETUP.md). |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` | Kosongkan bila tidak memakai login Google. Panduan: [GOOGLE_SETUP.md](GOOGLE_SETUP.md). |
| `MAIL_*` | Bawaan `MAIL_MAILER=log`; isi SMTP untuk mengirim email atur ulang kata sandi. |
| `DEMO_ACCOUNT_PASSWORD` | Hanya untuk pengembangan; kosongkan di produksi. |

Pengaturan yang dapat diubah tanpa deploy ada di panel admin: **Branding**, **Keamanan Akun**, **Pengaturan Operasional**, dan **Zona Waktu**.

## Keamanan

- PHP sudah dipatch untuk PHP2Shell; lihat [SECURITY.md](SECURITY.md) untuk prosedur upgrade, mitigasi, dan rollback.
- Kata sandi pengguna di-hash (bcrypt 12); kata sandi tautan memakai hash dan penguncian percobaan.
- 2FA tersedia untuk admin, ditambah daftar IP admin yang diizinkan.
- Rate limit pada login lokal, registrasi, reset kata sandi, pembuatan tautan, dan klik Linktree.
- Header keamanan dipasang middleware: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`.
- Cookie sesi mendukung `Secure`, `HttpOnly`, dan `SameSite`.
- IP pengunjung disimpan sebagai hash; log memiliki batas retensi.
- Tautan tujuan diperiksa ke Google Safe Browsing (kecuali impor CSV, yang sengaja melewati pemeriksaan agar tidak memanggil layanan luar ribuan kali).
- CSP tidak dipasang di aplikasi karena panel Filament/Livewire memakai skrip inline; bila menambahkan CSP di reverse proxy, izinkan `'unsafe-inline'` atau gunakan nonce agar panel tidak rusak.
- `.env`, kunci privat, dan Docker secret tidak boleh di-commit. `.gitignore` sudah mengecualikannya.

## Deployment

### Docker all-in-one (app + MySQL)

```bash
cp .env.example .env       # isi APP_KEY, DB_PASSWORD, APP_URL
docker compose up -d --build
```

Aplikasi tersedia di `http://localhost:8080`. Rincian varian compose ada di [Cara_Pakai_Docker.md](Cara_Pakai_Docker.md).

### Produksi: nginx host + Cloudflare Tunnel

Folder [shortlink-pro-dockerapp-nginxhost](shortlink-pro-dockerapp-nginxhost/) berisi paket produksi:

- `docker-compose.fulldb.yaml` — app + MySQL + scheduler.
- `docker-compose.app.yaml` — app + scheduler dengan MySQL eksternal.
- `Dockerfile` — build 4 tahap (base → vendor → assets → runtime), PHP 8.3.36.
- `nginx/shortlink-pro.conf` — reverse proxy HTTP di `127.0.0.1:80`.
- `install.sh` — memasang konfigurasi nginx.
- `README.md` — langkah lengkap, hardening, dan operasi.

Alurnya: **Cloudflare (HTTPS) → cloudflared → nginx `127.0.0.1:80` → container app `127.0.0.1:9000`**. Tidak ada TLS di server; Cloudflare menangani sertifikat publik.

```bash
cd shortlink-pro-dockerapp-nginxhost
cp .env.example.fulldb .env
# isi APP_KEY, DB_PASSWORD, APP_URL (domain tunnel), kunci Safe Browsing

mkdir -p secrets
openssl rand -base64 24 > secrets/db_root_password.txt
chmod 600 secrets/db_root_password.txt

docker compose -f docker-compose.fulldb.yaml up -d --build
sudo ./install.sh --domain short.example.com
```

### Tanpa Docker

Arahkan document root nginx ke `public/`, jalankan PHP-FPM, lalu tambahkan cron penjadwal:

```cron
* * * * * cd /path/Shortlink-Pro && php artisan schedule:run >> /dev/null 2>&1
```

## Struktur Proyek

```text
app/
├── Console/Commands/        # Perintah operasional & penjadwal
├── Dto/                     # Objek transfer data
├── Filament/
│   ├── Admin/               # Resource & pengaturan admin
│   ├── User/                # Panel pengguna
│   └── Widgets/             # Widget dashboard
├── Http/
│   ├── Controllers/         # Publik, auth, API, redirect
│   └── Middleware/          # Keamanan, mode akses, trace ID
├── Models/                  # Eloquent
├── Services/                # ShortUrl, Linktree, Analytics, Auth, Setting, ...
├── Support/                 # Enum, validasi, helper
└── Exceptions/              # Format galat seragam (error_code, trace_id)

bootstrap/                   # app.php, providers, cache
config/                      # Termasuk pemendek.php (QR, kata sandi, Safe Browsing)
database/migrations/         # Skema pengguna, tautan, klik, Linktree, audit, 2FA
docker/                      # Konfigurasi image Docker
lang/{id,en}/                # ui.php, validation, auth, passwords, en.json
resources/
├── css/publik/              # dasar, beranda, auth, panel, tautan, linktree
├── js/publik/               # beranda.js, panel.js, auth.js
└── views/                   # Blade publik, auth, panel Filament
routes/                      # web.php, api.php, console.php
shortlink-pro-dockerapp-nginxhost/  # Paket deploy produksi
storage/                     # Log, cache, unggahan (runtime, tidak di-commit)
tests/                       # Feature, unit, dan skenario E2E (tests/e2e)
```

## Pengujian

```bash
php artisan test                 # 218 test, 863 assertion (terakhir diverifikasi 2026-10-23)
vendor/bin/pint --test           # cek gaya kode
npm run build                    # 71 modul, ±133 kB aset (±37 kB gzip)
```

Pedoman dan skenario E2E ada di [uji_e2e.md](uji_e2e.md); konfigurasi di `e2e.config.ts`. Uji E2E memakai basis data dan URL terpisah dari pengembangan/produksi.

## Status Pengembangan

Keadaan per 2026-10-23, setelah 218 test dan `pint` lolos:

**Sudah selesai dan dikunci test**

- Pemendek tautan: slug otomatis/kustom, masa berlaku, soft delete, proteksi kata sandi dengan penguncian percobaan, kode QR bercache.
- Pemeriksaan tujuan ke Google Safe Browsing di lapisan validasi, dengan fail-open bila kunci kosong; impor CSV sengaja melewatinya.
- Analitik: tren, domain tujuan, jam/hari tersibuk, kualitas klik, kesehatan tautan, dan statistik Linktree lewat `linktree_events`.
- Panel pengguna satu lembar (`/user`) berisi empat kartu: Dashboard, Pemendek Tautan, Link Saya, Profil Linktree.
- Panel admin: pengguna, tautan, moderasi Linktree, empat halaman log dengan ekspor CSV, impor CSV, 2FA, allowlist IP, impersonasi, dan pengaturan branding/operasional.
- Dua bahasa penuh di sisi pengguna dan publik; label kustom panel admin juga sudah diterjemahkan (`lang/en.json`, 332 kunci), meski sebagian teks bawaan Filament masih mengikuti paket.
- Registrasi dengan contoh isian dan tombol lihat/sembunyikan kata sandi.
- Paket deploy produksi `shortlink-pro-dockerapp-nginxhost/` (nginx host + Cloudflare Tunnel) dan `Dockerfile` empat tahap.
- PHP sudah di-patch untuk PHP2Shell; prosedur ada di [SECURITY.md](SECURITY.md).

**Tindak lanjut yang masih terbuka**

- Panel admin masih menyisakan teks Indonesia tetap; penerjemahan bertahap bila ingin dua bahasa penuh di sisi admin.
- Verifikasi tampilan pada lebar layar 768 px (360 px sudah diperiksa).
- Uji coba build dan jalankan image Docker di mesin ber-Docker, termasuk `/up`, unggah logo, dan `queue:work`.
- Isi `PEMENDEK_SAFE_BROWSING_KEY` di produksi; tanpa kunci, tautan berbahaya lolos tanpa pemeriksaan.
- Server produksi belum ada; seluruh node wajib PHP 8.2.35/8.3.36/8.4.27/8.5.12 dengan `session.upload_progress.enabled=Off`.

Daftar tugas berjalan lengkap ada di `.aicontext/activetodo.md` (tidak ikut ter-commit).

## Dokumentasi

| Berkas | Isi |
|--------|-----|
| [SECURITY.md](SECURITY.md) | Prosedur patch PHP, CVE PHP2Shell, rollback, checklist produksi. |
| [Cara_Pakai_Docker.md](Cara_Pakai_Docker.md) | Build image, varian compose, variabel lingkungan. |
| [shortlink-pro-dockerapp-nginxhost/README.md](shortlink-pro-dockerapp-nginxhost/README.md) | Deploy produksi: nginx host, Cloudflare Tunnel, hardening, backup. |
| [KEYCLOAK_SETUP.md](KEYCLOAK_SETUP.md) | Konfigurasi SSO OIDC. |
| [GOOGLE_SETUP.md](GOOGLE_SETUP.md) | Login Google dan Safe Browsing API. |
| [PRDapp.md](PRDapp.md) | Keputusan produk dan aturan bisnis. |
| [changelog.md](changelog.md) | Riwayat perubahan. |

## Kontribusi

1. Buat branch dari `main`.
2. Tulis perubahan beserta test yang sesuai.
3. Jalankan `vendor/bin/pint` dan `php artisan test`.
4. Perbarui `changelog.md` bila perilaku berubah.
5. Ajukan merge request.

## Lisensi

Dirilis di bawah [MIT License](LICENSE). © 2026 Angga Permana.
