---
name: security
description: Status patch keamanan PHP, prosedur upgrade PHP pada XAMPP/Apache, rollback, dan checklist produksi. Dibaca saat menangani advisory PHP atau menyiapkan server baru.
---

# Keamanan

## Status Patch PHP2Shell (2026-10-23)

| CVE | Komponen | Dampak | Status di mesin lokal |
| --- | --- | --- | --- |
| CVE-2026-109618 | Core PHP (memory corruption saat OOM) | RCE tanpa autentikasi, CVSS v4.0 9.2 | Terpatch (PHP 8.2.35) |
| CVE-2026-109619 | `session.upload_progress` (use-after-free) | RCE tanpa autentikasi, CVSS v4.0 9.2 | Terpatch + fitur dimatikan |

- Advisory: GHSA-w8hw-w9fx-j79w (CVE-2026-109618), GHSA-6wmf-4rv5-p3rp (CVE-2026-109619).
- Eksploitasi dilaporkan di alam liar sejak 17 Oktober 2026.
- Rilis perbaikan: **PHP 8.2.35, 8.3.36, 8.4.27, 8.5.12** (21 Oktober 2026).
- Kedua celah dieksekusi di core PHP sebelum kode aplikasi berjalan. Middleware, login, rate limit, dan validasi Laravel **tidak** melindungi; patch adalah satu-satunya perbaikan penuh.

## Perubahan pada mesin lokal

- PHP `8.2.12` → **`8.2.35`** (ZTS VS2019 x64), diambil dari build resmi `windows.php.net` dan diverifikasi SHA256 `cfa6000be835931961e40740bd3b51111700061d6655ddbfe5a2125807947457`.
- `C:\xampp\php\php.ini`:
  - `session.upload_progress.enabled=Off` — menutup vektor CVE-2026-109619.
  - `post_max_size` dan `upload_max_filesize` `50M` → `8M` — unggahan aplikasi terbesar 2 MB (logo), sisanya memperkecil permukaan serangan.
  - `memory_limit` tetap `512M` (terbatas, bukan `-1`, karena tidak berjalan di container dengan batas cgroup).
- `C:\xampp\apache\bin` menerima `libssh2.dll`, `nghttp2.dll`, dan `brotlidec.dll` dari PHP 8.2.35. Proses Apache sudah memuat `libssh2.dll` lama; tanpa penyalinan ini `php_curl.dll` gagal dimuat dengan pesan `The specified procedure could not be found` dan ekstensi curl mati di web.
- Backup: `C:\xampp\php-8.2.12-backup-20261023` dan `C:\xampp\apache\bin\*.bak-20261023`.

### Hasil verifikasi

- `php -v` → `PHP 8.2.35 (cli) (ZTS Visual C++ 2019 x64)`.
- `php -m` identik dengan sebelum upgrade (bcmath, bz2, curl, exif, fileinfo, ftp, gd, gettext, imap, intl, ldap, mbstring, mysqli, openssl, pdo_mysql, pdo_pgsql, pdo_sqlite, pgsql, soap, sockets, sodium, xsl, zip).
- Halaman uji melalui Apache: PHP 8.2.35 / `apache2handler`, `curl`, `openssl`, `intl`, `zip`, `mbstring`, `pdo_mysql` termuat.
- `php artisan test` → 213 passed (829 assertions); aplikasi merespons HTTP 200; `C:\xampp\apache\logs\error.log` bersih dari `PHP Warning`.

## Prosedur patch di mesin Windows/XAMPP lain

1. Unduh build resmi:
   ```powershell
   curl.exe -L --fail -o php-8.2.35.zip https://windows.php.net/downloads/releases/php-8.2.35-Win32-vs16-x64.zip
   ```
2. Verifikasi checksum terhadap `sha256sum.txt` di halaman unduhan resmi. Jangan lanjut bila berbeda.
3. Hentikan Apache dan seluruh `php.exe`. Di mesin ini Apache bukan service (`apache_stop.bat` masih template `@@BITROCK_INSTALLDIR@@`), jadi:
   ```powershell
   Stop-Process -Name httpd,php -Force
   ```
   Proses `php.exe` yang tertinggal mengunci `icu*.dll` dan membuat penyalinan gagal dengan `ERROR 32 (0x00000020)`.
4. Cadangkan instalasi lama, lalu timpa dengan build baru:
   ```powershell
   robocopy C:\xampp\php C:\xampp\php-8.2.12-backup-20261023 /E
   robocopy <folder-ekstrak> C:\xampp\php /E /R:2 /W:2
   ```
   `robocopy` menimpa berkas program tanpa menghapus `php.ini`, `extras`, `logs`, dan `pear`. Kode keluar `0–7` berarti sukses.
5. Terapkan blok hardening pada `php.ini` (lihat bagian sebelumnya).
6. Salin DLL pendukung curl ke folder Apache:
   ```powershell
   Copy-Item C:\xampp\php\libssh2.dll,C:\xampp\php\nghttp2.dll,C:\xampp\php\brotlidec.dll C:\xampp\apache\bin -Force
   ```
7. Jalankan Apache dan verifikasi:
   ```powershell
   Start-Process C:\xampp\apache\bin\httpd.exe -WorkingDirectory C:\xampp\apache\bin -WindowStyle Hidden
   C:\xampp\php\php.exe -v
   C:\xampp\php\php.exe artisan test
   ```
   Buka aplikasi, pastikan HTTP 200, lalu periksa `C:\xampp\apache\logs\error.log`.

Catatan: pada upgrade PHP berikutnya, ulangi langkah 6 karena Apache memuat `libssh2.dll` lebih dulu daripada versi milik PHP.

## Prosedur patch di produksi (Linux)

```bash
# Debian/Ubuntu
sudo apt update && sudo apt install --only-upgrade php8.2-fpm php8.2-cli
# RHEL/Alma/Rocky
sudo dnf upgrade php-fpm php-cli
php -v   # pastikan 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12
sudo systemctl restart php8.2-fpm   # lalu reload Apache/nginx bila memakai mod_php
```

- Patch **semua** node (web, worker queue, scheduler) dan semua versi PHP yang terpasang.
- Bila memakai image Docker, ganti tag dasar ke versi patch lalu bangun ulang; jangan hanya menambah paket di runtime.
- Setelah patch, jalankan `php artisan test` dan uji unggah berkas (logo, impor CSV).

## Mitigasi sementara (bila patch belum bisa dilakukan)

Mitigasi ini hanya mengurangi peluang, bukan menutup celah:

- `session.upload_progress.enabled=0` di semua SAPI (FPM, Apache, CLI).
- `post_max_size` dan `upload_max_filesize` sekecil kebutuhan aplikasi.
- `pm.max_requests=1` agar proses FPM tidak bertahan lama.
- `memory_limit=-1` **hanya** bila proses dibatasi container/cgroup (bukan `RLIMIT_AS`/`RLIMIT_DATA`).
- PHP 8.5: `fatal_error_backtraces=0`.
- Blokir di WAF/reverse proxy: request `multipart/form-data` yang memuat field `PHP_SESSION_UPLOAD_PROGRESS`, dan rate-limit ketat untuk `POST /shorten` serta `POST /api/v1/links`.

## Rollback

1. Hentikan Apache dan `php.exe`.
2. Pulihkan folder PHP: hapus/rename `C:\xampp\php`, lalu salin kembali `C:\xampp\php-8.2.12-backup-20261023`.
3. Pulihkan `C:\xampp\apache\bin\libssh2.dll.bak-20261023`, `libssl-3-x64.dll.bak-20261023`, `libcrypto-3-x64.dll.bak-20261023`, dan `libsasl.dll.bak-20261023` bila pernah diubah.
4. Jalankan Apache dan `php -v`.

Rollback mengembalikan kerentanan CVE-2026-109618/109619; gunakan hanya bila upgrade menimbulkan kegagalan yang tidak bisa diselesaikan.

## Checklist produksi

- [ ] Semua node memakai PHP 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12 (cek `php -v` per node).
- [ ] `session.upload_progress.enabled=Off` pada setiap SAPI.
- [ ] `post_max_size` dan `upload_max_filesize` sesuai kebutuhan (8M cukup untuk aplikasi ini).
- [ ] `display_errors=Off`, `expose_php=Off`.
- [ ] Rate limit `POST /shorten` dan `POST /api/v1/links` aktif.
- [ ] WAF memblokir field `PHP_SESSION_UPLOAD_PROGRESS`.
- [ ] `PEMENDEK_SAFE_BROWSING_KEY` terisi agar tautan berbahaya ditolak saat dibuat (lihat `changelog.md` 2026-10-23).
- [ ] Log dipantau untuk pola `PHP_SESSION_UPLOAD_PROGRESS`, OOM berulang, dan request multipart mencurigakan.
