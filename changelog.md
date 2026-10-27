name: changelog
description: Riwayat perubahan proyek: aturan penulisan entri, format, bagian status migrasi opsional, dan contoh entri. Dibaca untuk mengetahui status terakhir.

# Changelog
Dokumen ini mencatat apa yang sudah berubah. Pekerjaan yang masih berjalan dicatat di `.aicontext/activetodo.md`. Perbarui dokumen ini setiap ada perubahan besar pada kode atau basis data.

## Aturan Penulisan
1. Entri terbaru ditulis paling atas.
2. Tanggal ditulis dengan format `YYYY-MM-DD`.
3. Kategori yang dipakai: ⚠️ Perlu Perhatian, Ditambahkan, Diubah, Diperbaiki, Dihapus, dan Migrasi.
4. Setiap entri memuat ringkasan, **alasan/konteks**, berkas yang terlibat, dan hasil verifikasi.
5. Jangan menghapus entri lama. Jika ada kesalahan, tambahkan entri koreksi.

## Status Migrasi
*(Bagian ini hanya diisi jika proyek sedang berpindah stack. Hapus jika tidak relevan)*
- Stack lama: [nama dan versi]
- Stack baru: [nama dan versi]
- Progres: [yang sudah dipindahkan] / [total]
- Yang tersisa: [daftar singkat]

## Format Entri
[YYYY-MM-DD] [Judul singkat perubahan]

⚠️ Perlu Perhatian / Breaking Changes
- [Perubahan yang membutuhkan tindakan manual, update env, atau mengubah format API]

Ditambahkan
- [Hal yang baru]

Diubah
- [Hal yang berubah]
- **Alasan:** [Mengapa ini diubah? Ref: Ticket/Issue jika ada]

Diperbaiki
- [Bug yang diperbaiki]
- **Akar masalah:** [Penyebab asli bug]

Migrasi
- [Perubahan skema, dengan nama berkas migrasi]

**Berkas:** `[path/berkas-1]`, `[path/berkas-2]`
**Verifikasi:** [format], [test], [build], dan [uji lain jika ada], beserta hasilnya.

---

[2026-10-27] Paket deploy Docker disatukan di root repo

Perlu Perhatian
- Folder `shortlink-pro-dockerapp-nginxhost/` dihapus. `Dockerfile`, `docker/`, `docker-compose.fulldb.yaml`, `nginx/`, `install.sh`, dan `secrets/` sekarang berada di root repo; semua perintah `docker compose` dijalankan dari root.
- `docker-compose.yaml`, `docker-compose.db.yaml`, dan `docker-compose.app.yaml` dihapus. Satu-satunya compose adalah `docker-compose.fulldb.yaml` (app + MySQL + scheduler; nginx tetap di host sebagai reverse proxy).
- `.env.example.fulldb` dan `.env.example.app` digabung menjadi satu `.env.example` di root yang berisi nilai produksi; `.env` di root berisi nilai pengembangan XAMPP.
- Siapa pun yang sudah deploy dengan layout lama harus memindahkan `.env` dan `secrets/db_root_password.txt` ke root repo, lalu `docker compose -f docker-compose.fulldb.yaml up -d --build`.

Diubah
- `Dockerfile`: `COPY` diarahkan ke layout root (`docker/apache.conf`, `docker/php.ini`, `docker/entrypoint.sh`).
- `docker-compose.fulldb.yaml`: `build` dipindah ke anchor `x-app-common` sehingga `scheduler` ikut memakai image lokal; `build.context` menjadi `.`.
- `.dockerignore` dan `.gitignore` disesuaikan (pengecualian `secrets/`, konteks build root).
- `README.md`, `Cara_Pakai_Docker.md`, `install.sh`, `.aicontext/activetodo.md`, dan `.aicontext/coredependencies.md` mengikuti layout baru.
- `.env` dibersihkan: key mati `AUTH_MODE`, `KEYCLOAK_LOGOUT_REDIRECT_URI`, `PHP_CLI_SERVER_WORKERS`, serta blok AWS/REDIS/MEMCACHED dihapus.
- **Alasan:** aplikasi berjalan sebagai container di belakang nginx milik host, sehingga berkas deploy tidak perlu dipisah ke subfolder. Pemisahan itu membuat konteks build harus `..` dan menyisakan tiga compose yang saling bertentangan.

Diperbaiki
- `docker compose up -d --build` gagal pada service `scheduler` dengan `pull access denied for shortlink-pro-app, repository does not exist or may require 'docker login'`.
- **Akar masalah:** service `scheduler` hanya punya `image:` tanpa `build:`, sehingga compose mencoba menarik image dari registry sebelum image lokal selesai dibangun. Dengan `build` di anchor bersama, kedua service membangun/memakai image yang sama.

**Berkas:** `Dockerfile`, `.dockerignore`, `docker-compose.fulldb.yaml`, `docker/{apache.conf,php.ini,entrypoint.sh}`, `nginx/shortlink-pro.conf`, `install.sh`, `secrets/.gitignore`, `.env`, `.env.example`, `.gitignore`, `README.md`, `Cara_Pakai_Docker.md`, `.aicontext/activetodo.md`, `.aicontext/coredependencies.md`
**Verifikasi:** `python -c "yaml.safe_load(...)"` membaca `docker-compose.fulldb.yaml` tanpa galat dan menghasilkan service `app`, `scheduler`, `db` dengan blok `build` pada `app` dan `scheduler`; `bash -n install.sh` dan `bash -n docker/entrypoint.sh` lolos; semua berkas shell/YAML memakai LF (0 CR); `grep` memastikan tidak ada referensi `shortlink-pro-dockerapp-nginxhost` yang tersisa di kode/dokumen aktif. Di server lab: `docker compose -f docker-compose.fulldb.yaml config --quiet` OK, `up -d --build` sukses dari root (tanpa galat `pull access denied`), ketiga container `Up`/`healthy`, `GET /up` 200, `GET /` 200, `GET /login` 302 → `/login/local` 200, `POST /shorten` 201, `GET /<slug>` 302 ke tujuan, `GET /qr/<slug>` 200 `image/svg+xml`, dan 19 tabel tetap utuh setelah volume `db-data` dipakai ulang.

---

[2026-10-27] Penyesuaian dokumentasi: `aturan_pengembangan.md` keluar, `README.md` masuk

Perlu Perhatian
- `aturan_pengembangan.md` tidak lagi dilacak Git. Berkas tetap ada di disk. Akibatnya, repo tidak lagi memuat aturan main; pengembang baru harus mendapat berkas ini terpisah. Tujuh dokumen alur lain tetap dilacak.
- `README.md` kembali dilacak dan isinya identik dengan versi terakhir (hash blob `c1f13e4`), yaitu versi yang sudah mencantumkan bagian "Akun dan bahasa", "Status Pengembangan", dan struktur proyek terkini.

Ditambahkan
- Pengecualian `!README.md` di `.gitignore`, supaya panduan repo tetap terlihat di halaman GitHub.

Dihapus
- `aturan_pengembangan.md` dari pelacakan Git.

Diubah
- **Alasan:** pengguna menilai aturan pengembangan tidak perlu ada di repo, tetapi panduan penggunaan perlu tampil sebagai halaman depan repository.

**Berkas:** `.gitignore`, `changelog.md`, `README.md`
**Verifikasi:** `git ls-files "*.md"` menyisakan 7 berkas (`README.md`, `PRDapp.md`, `disiplin_struktur.md`, `disiplin_coding.md`, `aicontext.md`, `changelog.md`, `uji_e2e.md`); `git check-ignore --no-index -v` mengonfirmasi `aturan_pengembangan.md` kena `*.md` tanpa pengecualian; `git hash-object README.md` sama dengan `defbe7c:README.md` (`c1f13e4`); `pint --test` passed; `php artisan test` 218 passed (863 assertions).

---

[2026-10-27] Dokumentasi dipilih: hanya dokumen alur pengembangan yang dilacak Git

Perlu Perhatian
- Repo GitHub sekarang tidak lagi memuat `README.md`, `SECURITY.md`, `GOOGLE_SETUP.md`, `KEYCLOAK_SETUP.md`, `Cara_Pakai_Docker.md`, dan `shortlink-pro-dockerapp-nginxhost/README.md`. Berkasnya **tetap ada di disk** dan tidak dihapus; cukup `git add` kembali bila suatu saat perlu dilacak.
- Ini menyalahi §8 aturan_pengembangan.md (dokumen harus tetap sinkron di repo). Diputuskan mengikuti instruksi langsung pengguna, yang menurut §1 aturan_pengembangan.md didahulukan atas dokumen.
- Kode aplikasi tidak tersentuh sama sekali. Perubahan murni pada pelacakan Git dan `.gitignore`.

Ditambahkan
- Blok abaikan di `.gitignore`: `*.md` lalu pengecualian `!PRDapp.md`, `!aturan_pengembangan.md`, `!disiplin_struktur.md`, `!disiplin_coding.md`, `!aicontext.md`, `!changelog.md`, `!uji_e2e.md`. Berkas `.md` baru apa pun tidak akan masuk repo kecuali sengaja ditambahkan.

Dihapus
- Enam berkas dokumentasi dari pelacakan Git (`git rm --cached`): `README.md`, `SECURITY.md`, `GOOGLE_SETUP.md`, `KEYCLOAK_SETUP.md`, `Cara_Pakai_Docker.md`, `shortlink-pro-dockerapp-nginxhost/README.md`.

Diubah
- **Alasan:** pengguna ingin repo hanya berisi aplikasi beserta aturan main; panduan setup pihak ketiga dan panduan deploy dinilai cukup disimpan lokal.

**Berkas:** `.gitignore`, `changelog.md`
**Verifikasi:** `git ls-files "*.md"` menyisakan tepat 7 berkas (7 dokumen alur); `git check-ignore --no-index -v` mengonfirmasi 6 berkas buangan kena `.gitignore:39 *.md` dan 7 dokumen alur dikembalikan oleh aturan negasi (baris 40-46); pemeriksaan seluruh berkas terlacak tidak menemukan berkas yang jadi terabaikan; `pint --test` passed; `php artisan test` 218 passed (863 assertions).

---

[2026-10-27] Persiapan push ke GitLab: `.gitignore` + README keadaan terkini

Perlu Perhatian
- Lingkungan sudah diverifikasi ulang sebelum di-push: `pint --test` passed, `php artisan test` 218 passed (863 assertions), `npm run build` 71 modul (±133 kB, ±37 kB gzip).

Ditambahkan
- Pola `.gitignore` untuk artefak operasional yang belum tercakup: cadangan basis data dan arsip unggahan (`/backup-*.sql`, `/backup-*.sql.gz`, `/storage-*.tar.gz`, `*.sql.gz`, `*.tar.gz`), log npm/yarn (`npm-debug.log*`, `yarn-error.log*`, `.pnpm-debug.log*`), direktori data Docker lokal (`/.docker/data`), dan `/.vagrant`.
- Pola `**/secrets/` sebagai generalisasi dari `shortlink-pro-dockerapp-nginxhost/secrets/`, supaya folder rahasia di subfolder mana pun juga terabaikan.
- Bagian **Status Pengembangan** di `README.md`: apa yang sudah selesai dan dikunci test, serta tindak lanjut yang masih terbuka (penerjemahan panel admin, uji lebar 768 px, build Docker di mesin ber-Docker, isi `PEMENDEK_SAFE_BROWSING_KEY`, dan belum ada server produksi).

Diubah
- **Alasan:** README sebelumnya belum menyebut halaman daftar (contoh isian + tombol lihat sandi) dan belum mencatat bahwa pilihan bahasa ikut tersimpan ke akun saat daftar/masuk.
- `README.md`: bagian "Tentang" menjelaskan bahasa per pengguna; bagian Fitur mendapat subbagian baru "Akun dan bahasa" (registrasi, petunjuk pola, tombol lihat/sembunyikan sandi, `LocaleService::simpanDariSesi()`); duplikat baris "dua bahasa" dihapus dari "Panel dan operasional".
- `README.md`: "Struktur Proyek" diselaraskan dengan struktur nyata (`app/Exceptions/`, `app/Console/Commands/`, `bootstrap/`, `lang/{id,en}/`, `resources/{css,js}/publik/`, `routes/console.php`), dan "Pengujian" memuat angka hasil verifikasi terakhir.

**Berkas:** `.gitignore`, `README.md`, `changelog.md`
**Verifikasi:** `git check-ignore` mengonfirmasi pola baru menangkap `backup-*.sql`, `storage-*.tar.gz`, `yarn-error.log`, dan `.docker/data/mysql` tanpa mengabaikan berkas terlacak; `pint --test` passed; `artisan test` 218 passed (863 assertions); `npm run build` 71 modul; tidak ada berkas sensitif (`.env`, kunci, secret) yang terlacak Git.

---

[2026-10-23] Panel pengguna mengikuti bahasa akun

Perlu Perhatian
- Aset tidak berubah, jadi `npm run build` tidak diperlukan untuk perubahan ini. Setelah deploy, cukup muat ulang halaman panel agar teks baru tampil.

Diperbaiki
- Panel pengguna tetap berbahasa Indonesia meskipun akun sudah memilih English. Ternyata pilihan bahasa **sudah tersimpan benar** (`users.locale = en`); masalahnya teks panel ditulis langsung dalam Bahasa Indonesia di Blade, trait, dan resource Filament, sehingga tidak pernah mengikuti locale aplikasi.
- Filter status pada daftar tautan membandingkan teks Indonesia; kini memakai kode status (`active`, `inactive`, `expired`, `scheduled`, `protected`, `forever`) agar tetap benar di kedua bahasa.

Diubah
- Seluruh teks panel pengguna dialihkan ke `__('ui.panel.*')`: dashboard (statistik, tren, zona waktu), kesehatan tautan, aktivitas klik, statistik Linktree, kartu pemendek, daftar tautan, baris tautan, sunting tautan, QR, profil Linktree, pengaturan Linktree, banner impersonasi, serta notifikasi aksi.
- `UserDaftarTautanService` menambah kunci `status` berupa kode; `UserAktivitasService` menambah `status` per rincian kualitas klik; `UserKesehatanService` memakai kunci terjemahan untuk judul dan pesan.
- `ShortUrlResource`, halaman Create/Edit/View, dan widget `MyLinksOverview` memakai label terjemahan.
- `lang/id/ui.php` dan `lang/en/ui.php` dilengkapi kunci panel yang sebelumnya belum ada (status, placeholder alamat publik, tooltip salin, dan lain-lain).
- `tests/Feature/PanelBahasaTest.php` baru: memastikan panel berbahasa Inggris untuk akun `locale=en` dan berbahasa Indonesia untuk `locale=id`.
- Uji sementara `tests/Feature/TmpLocalePanelTest.php` dihapus.

**Berkas:** `app/Filament/User/Resources/ShortUrlResource.php`, `app/Filament/User/Resources/ShortUrlResource/Pages/{Create,Edit,View}ShortUrl.php`, `app/Filament/User/Widgets/MyLinksOverview.php`, `app/Filament/User/Pages/LinktreeSettings.php`, `app/Filament/User/Pages/Concerns/{MengelolaTautan,MengelolaLinktree,MengelolaZonaWaktu}.php`, `app/Services/Dashboard/{UserDaftarTautanService,UserKesehatanService,UserAktivitasService}.php`, `resources/views/filament/user/**`, `lang/{id,en}/ui.php`, `tests/Feature/PanelBahasaTest.php`
**Verifikasi:** `pint` passed; `artisan test` 218 passed (863 assertions), termasuk `PanelBahasaTest`.

---

[2026-10-23] Pilihan bahasa tamu disimpan ke akun saat daftar dan masuk

Diperbaiki
- Bahasa yang dipilih di halaman masuk/daftar kembali ke bahasa bawaan setelah pengguna keluar lalu masuk lagi, karena pilihan tamu hanya tersimpan di sesi sementara kolom `users.locale` akun baru masih kosong.
- **Akar masalah:** `RegisterController` dan service masuk tidak pernah menyalin `session('locale')` ke akun; `SetLocale` memang memprioritaskan `users.locale`, sehingga sesi baru jatuh ke `config('bahasa.bawaan')`.

Diubah
- `LocaleService::simpanDariSesi(User $pengguna)` baru: membaca `session('locale')`, memvalidasinya lewat `sah()`, lalu memakai `simpan()` (tetap mencatat audit `locale.update`).
- Dipanggil setelah autentikasi berhasil di `LocalAuthService`, `GoogleAuthService`, `KeycloakAuthService`, dan `RegisterController::store`.
- Impersonasi admin sengaja **tidak** memakai cara ini agar bahasa target tidak tertimpa bahasa admin.
- `tests/Feature/BahasaTest.php`: dua test baru — pilihan tamu tersimpan saat daftar dan saat masuk lokal.

**Berkas:** `app/Services/Setting/LocaleService.php`, `app/Services/Auth/LocalAuthService.php`, `app/Services/Auth/GoogleAuthService.php`, `app/Services/Auth/KeycloakAuthService.php`, `app/Http/Controllers/RegisterController.php`, `tests/Feature/BahasaTest.php`
**Verifikasi:** `pint` passed, `artisan test` 216 passed (848 assertions).

---

[2026-10-23] Halaman daftar: contoh isian dan tombol lihat kata sandi

Perlu Perhatian
- Aset publik berubah (`resources/js/publik/auth.js`, `resources/css/publik/auth.css`), jadi jalankan `npm run build` sebelum deploy.
- Modul `auth.js` hanya dimuat di halaman daftar; halaman auth lain tidak terpengaruh.

Ditambahkan
- Placeholder contoh isian: "Mis. Budi Santoso", "mis. budi_santoso", dan "nama@contoh.com".
- Tombol lihat/sembunyikan kata sandi pada kolom kata sandi dan ulangi kata sandi, lengkap dengan `aria-pressed`, `aria-controls`, label yang berubah, serta ikon mata buka/tercoret.
- `tests/Feature/RegisterPageTest.php` yang memastikan contoh isian, petunjuk, dan dua tombol sandi tampil.

Diubah
- **Alasan:** pengguna baru tidak tahu pola nama pengguna dan tidak bisa memeriksa salah ketik kata sandi; keduanya memperbesar peluang gagal daftar.
- Petunjuk kata sandi tidak lagi menyalin label, melainkan "Minimal 12 karakter.".
- Kolom nama pengguna mendapat `pattern="[A-Za-z0-9_-]+"`, `title`, serta petunjuk "Huruf, angka, tanda hubung, dan garis bawah." agar aturan `alpha_dash` terlihat sebelum submit.
- `lang/id/ui.php` dan `lang/en/ui.php` mendapat kunci `contoh_nama`, `contoh_username`, `contoh_email`, `petunjuk_username`, `petunjuk_sandi`, `lihat_sandi`, dan `sembunyikan_sandi`.

**Berkas:** `resources/views/auth/register.blade.php`, `resources/js/publik/auth.js`, `resources/css/publik/auth.css`, `vite.config.js`, `lang/id/ui.php`, `lang/en/ui.php`, `tests/Feature/RegisterPageTest.php`
**Verifikasi:** `pint` passed, `artisan test` 214 passed (837 assertions), `npm run build` berhasil (auth.css 4,77 kB; auth.js 0,49 kB).

---

[2026-10-23] Repo siap publik: pembersihan Git + README ditulis ulang

Perlu Perhatian
- `.env` lokal, Docker secret, kunci privat, dan berkas cadangan kini diabaikan di seluruh subfolder. Jangan pernah memaksa `git add -f` untuk berkas tersebut.
- Nilai contoh Keycloak di `.env.example` dikosongkan supaya aplikasi baru tidak mencoba SSO ke server fiktif. Isi hanya bila benar-benar memakai SSO.

Ditambahkan
- `LICENSE` (MIT) dan bagian lisensi/kontribusi di README.
- Pola `.gitignore`: `**/.env`, `**/.env.*` (kecuali `.env.example*`), `shortlink-pro-dockerapp-nginxhost/secrets/`, `*.pem`, `*.key`, `*.p12`, `*.pfx`, `*.bak*`, `*.orig`, dan liputan pengujian.
- `.env.example`: `APP_SEED=false`, `SESSION_SECURE_COOKIE=false`, `SESSION_SAME_SITE=lax`.

Diubah
- `README.md` ditulis ulang: fitur terkini (publik + terkelola, Linktree, Safe Browsing, QR, API v1, impor CSV, 2FA, SSO), kebutuhan PHP 8.2.35+, instalasi lokal, konfigurasi, keamanan, deployment Docker/nginx/Cloudflare, pengujian, dan daftar dokumentasi.
- `composer.json`: nama paket `angga-permana/shortlink-pro` dan deskripsi proyek menggantikan placeholder Laravel.

**Berkas:** `.gitignore`, `.env.example`, `composer.json`, `LICENSE`, `README.md`
**Verifikasi:** `git check-ignore` memastikan `.env` dan secret terabaikan sementara `.env.example*` tetap terlacak; `git diff --check` bersih; `pint --test` passed; `artisan test` 213 passed (829 assertions).

---

[2026-10-23] Deploy produksi: Docker + nginx host + Cloudflare Tunnel (tanpa SSL)

⚠️ Perlu Perhatian
- Folder `shortlink-pro-dockerapp-nginxhost/` harus tetap berada di dalam root repo: konteks build compose adalah `..` karena sumber aplikasi ada di root.
- Tidak ada TLS di server. nginx hanya `listen 127.0.0.1:80`, container hanya dipublikasikan ke `127.0.0.1:9000`, dan satu-satunya akses publik adalah Cloudflare Tunnel. Jangan membuka port 80 ke internet.
- Isi `APP_KEY` dan `DB_PASSWORD` di `.env`, lalu buat `secrets/db_root_password.txt` (`openssl rand -base64 24`, `chmod 600`). Entrypoint menolak start bila `APP_KEY` kosong.

Ditambahkan
- `.env.example.fulldb` dan `.env.example.app`: template environment produksi lengkap (MySQL, sesi secure cookie, log stderr, Safe Browsing, SSO/Google nonaktif, akun demo kosong).
- Docker secret `db_root_password` untuk MySQL; kata sandi root tidak lagi masuk environment aplikasi.
- `bootstrap/app.php`: `trustProxies` untuk loopback dan jaringan Docker agar `X-Forwarded-Proto: https` dihormati (URL https, secure cookie, redirect benar di balik nginx/Cloudflare).
- `secrets/.gitignore`, pembaruan `.gitignore` folder (`.env.example.*` tetap terlacak), dan `.dockerignore` root untuk mencegah `.env` subfolder masuk image.

Diubah
- `Dockerfile` produksi ditulis ulang mengikuti urutan `base` → `vendor` → `assets` → `runtime`: PHP dikunci `8.3.36` (patch PHP2Shell), `composer install` dari lockfile tanpa `--ignore-platform-req`, `npm ci` (bukan `npm install`), aset dibangun dengan `vendor` tersedia untuk `@source` Tailwind, dan runtime tidak membawa tool build.
- **Alasan:** Dockerfile lama memakai `php:8.3-apache` yang bergerak (tidak berpacth), menyalin seluruh sumber sebelum install (cache batal), mengabaikan lockfile npm, dan tidak punya entrypoint migrasi.
- `docker-compose.fulldb.yaml` dan `docker-compose.app.yaml`: `build.context: ..` + `dockerfile` eksplisit, build arg `PHP_VERSION`, port `127.0.0.1:9000`, `read_only`, `cap_drop: ALL` + hanya CHOWN/SETUID/SETGID/KILL, `no-new-privileges`, tmpfs `/tmp`/`/run`/`bootstrap/cache`, batas log 10m×3, dan service `scheduler` (`schedule:work`, `RUN_MIGRATIONS=false`) karena `routes/console.php` punya jadwal harian/per jam.
- `docker-compose.fulldb.yaml`: `MYSQL_USER` memakai `${DB_USERNAME}` (Laravel membaca `DB_USERNAME`, bukan `DB_USER`) dan database berada di network `backend` bertanda `internal: true`.
- `entrypoint.sh` produksi: tidak menulis `.env`/tidak membuat APP_KEY, menunggu MySQL lewat PDO, menjalankan migrasi hanya di service app, menjalankan `php artisan optimize`, dan menjalankan perintah artisan sebagai `www-data` agar berkas cache/log tidak terkunci root.
- `php.ini` produksi: `expose_php=Off`, `display_errors=Off`, `post_max_size=10M`/`upload_max_filesize=8M`, cookie sesi HttpOnly+Secure+SameSite=Lax, `session.upload_progress.enabled=Off`, OPcache `validate_timestamps=0` (image immutable).
- `apache/shortlink-pro.conf`: port 9000, `AllowOverride None` + rewrite di vhost, `-Indexes`, `LimitRequestBody 10 MB`, log ke stdout/stderr. Dockerfile juga mengarahkan `ErrorLog` global ke stderr dan mematikan `other-vhosts-access-log` agar Apache bisa start di root filesystem read-only.
- `nginx/shortlink-pro.conf`: tanpa blok SSL/redirect HTTPS/HSTS, `server_tokens off`, `listen 127.0.0.1:80`, header `X-Forwarded-Proto https` (TLS diterminasi Cloudflare), WebSocket/Livewire, health check `/up`.
- `install.sh`: tidak lagi menyentuh sertifikat/`/etc/ssl`; hanya memvalidasi domain/port, memasang site, `nginx -t`, reload, lalu mengecek `/up`. `--listen-all` tersedia dengan peringatan.
- `README.md`: ditulis ulang untuk arsitektur Cloudflare Tunnel → nginx host → container, termasuk hardening, operasi, backup, troubleshooting, dan checklist go-live.
- **Alasan:** permintaan pengguna (deploy pribadi, tanpa SSL, publik lewat Cloudflare Tunnel, reverse proxy nginx host, memakai `docker-compose.fulldb.yaml`).

Diperbaiki
- Ketidakcocokan `DB_USER` (compose) vs `DB_USERNAME` (Laravel) yang membuat MySQL dan aplikasi memakai kredensial berbeda.
- Apache tidak bisa membuka `${APACHE_LOG_DIR}/error.log` pada root filesystem read-only; log sekarang ke `/proc/self/fd/2` dan CustomLog global dimatikan.
- `a2disconf other-vhosts-access-log` diberi fallback menghapus symlink bila conf tidak tersedia, supaya build tidak berhenti pada varian image dasar.
- `install.sh` memvalidasi port 1–65535, menolak opsi tanpa nilai, dan memperingatkan bila site `default` nginx masih membuka port 80; skrip juga menguji `/up` lewat nginx (header Host domain), bukan hanya lewat port aplikasi.

**Berkas:** `shortlink-pro-dockerapp-nginxhost/{Dockerfile,php.ini,entrypoint.sh,docker-compose.fulldb.yaml,docker-compose.app.yaml,README.md,install.sh,.env.example.fulldb,.env.example.app,.gitignore,.dockerignore}`, `shortlink-pro-dockerapp-nginxhost/apache/shortlink-pro.conf`, `shortlink-pro-dockerapp-nginxhost/nginx/shortlink-pro.conf`, `bootstrap/app.php`, `.dockerignore`, `.aicontext/activetodo.md`, `.aicontext/coredependencies.md`
**Verifikasi:** YAML kedua compose diurai dengan Symfony YAML (service `db,app,scheduler`; merge anchor, `read_only`, `cap_add`, tmpfs, secret terbaca); `bash -n` untuk `entrypoint.sh` dan `install.sh` lolos; potongan PHP tunggu-DB lolos `php -l`; semua berkas skrip/konfigurasi berakhir LF; `pint --test` passed; `artisan test` 213 passed (829 assertions). Build image belum dijalankan karena Docker tidak tersedia di mesin ini.

---

[2026-10-23] Dockerfile ditulis ulang: urutan build base → vendor → assets → runtime

Diubah
- `Dockerfile` dibagi empat stage: `base` (PHP 8.3.36 + ekstensi + Apache), `vendor` (composer install), `assets` (npm ci + vite build), dan `runtime` (hanya menyalin hasil).
- **Alasan:** Dockerfile lama menyalin seluruh sumber sebelum `composer install` sehingga cache composer selalu batal setiap kode berubah, memakai `npm install` padahal `package-lock.json` ada, dan membangun aset tanpa `vendor` padahal `resources/css/app.css` memakai `@source` ke view pagination Laravel.
- Versi PHP dikunci lewat `ARG PHP_VERSION=8.3.36` (rilis berpacth PHP2Shell); tag `php:8.3-apache` yang bergerak tidak dipakai lagi.
- Stage `assets` menerima `vendor` dari stage `vendor` agar Tailwind dapat memindai view pagination; tanpa itu kelas pagination hilang dari CSS hasil build.
- Stage `runtime` menormalkan akhir baris entrypoint (LF), memberi `chmod 755`, menyiapkan `storage`/`bootstrap/cache` milik `www-data`, dan menambah `HEALTHCHECK` ke `/up`.
- `docker/php.ini`: unggahan 8M/10M, `session.upload_progress.enabled=Off` (vektor CVE-2026-109619), `expose_php=Off`.
- `docker/apache.conf`: `ServerTokens Prod` dan `ServerSignature Off`.
- `docker/entrypoint.sh`: worker tidak lagi membuat APP_KEY sendiri (hanya memperingatkan bila kosong), menjalankan `filament:upgrade`, dan melakukan `chown` akhir setelah artisan agar berkas milik root tidak mengunci `www-data`.
- `.dockerignore`: mengecualikan dokumentasi (`*.md`), `.aicontext`, sisa e2e, dan berkas backup.
- `docker-compose.yaml` dan `docker-compose.app.yaml`: meneruskan `PHP_VERSION` sebagai build arg; service `queue` ikut punya blok `build` agar `docker compose up` tidak mencoba menarik image dari registry; `APP_KEY: ${APP_KEY:-}` diteruskan ke app dan queue supaya memakai kunci yang sama; `APP_ENV`/`APP_DEBUG` ditambahkan ke queue (sebelumnya worker mewarisi `APP_ENV=local`/`APP_DEBUG=true` dari `.env.example` di image); queue menunggu `app` berstatus `service_healthy` sehingga migrasi selesai lebih dulu; `stop_grace_period: 100s` memberi waktu job selesai.
- `docker/entrypoint.sh`: membuang `APP_KEY` kosong dari environment, karena variabel kosong menimpa `.env` (Dotenv immutable) dan membuat `key:generate` tidak berefek.

⚠️ Perlu Perhatian
- Docker belum terpasang di mesin pengembangan, jadi image belum dibangun; verifikasi terbatas pada `npm ci --dry-run`, `composer install --dry-run`, dan pembacaan konfigurasi.
- Untuk mode app + queue, `APP_KEY` wajib diset eksplisit dan sama di kedua service. Sebelumnya worker bisa diam-diam memakai kunci berbeda.
- Bila tag `php:8.3.36-apache` tidak tersedia, gunakan rilis patch yang lebih baru lewat `--build-arg PHP_VERSION`, jangan yang lebih lama.

**Berkas:** `Dockerfile`, `docker/php.ini`, `docker/apache.conf`, `docker/entrypoint.sh`, `.dockerignore`, `docker-compose.yaml`, `docker-compose.app.yaml`, `Cara_Pakai_Docker.md`
**Verifikasi:** `npm ci --dry-run` sukses (71 paket), `composer install --no-dev --dry-run` menyatakan lock valid, ketiga berkas compose lolos parser YAML Symfony (`services` dan build arg terbaca benar), entrypoint 0 byte CR (LF), Pint passed, suite 213 passed (829 assertions).

---

[2026-10-23] Patch PHP 8.2.35 untuk PHP2Shell (CVE-2026-109618 & CVE-2026-109619)

Diubah
- PHP pada XAMPP lokal dinaikkan dari 8.2.12 ke 8.2.35 (ZTS VS2019 x64), sesuai rilis perbaikan 21 Oktober 2026.
- **Alasan:** kedua CVE adalah RCE tanpa autentikasi di core PHP (CVSS 9.2) yang dieksekusi sebelum kode aplikasi dan middleware berjalan, sehingga login, rate limit, maupun validasi aplikasi tidak melindungi. Eksploitasi dilaporkan di alam liar sejak 17 Oktober 2026.
- `C:\xampp\php\php.ini`: `session.upload_progress.enabled=Off` (vektor CVE-2026-109619), `post_max_size` dan `upload_max_filesize` 50M → 8M (unggahan aplikasi terbesar 2 MB), `memory_limit` tetap 512M.
- `C:\xampp\apache\bin` menerima `libssh2.dll`, `nghttp2.dll`, dan `brotlidec.dll` dari PHP 8.2.35 agar `php_curl.dll` dapat dimuat di proses Apache; tanpa itu ekstensi curl gagal dengan "The specified procedure could not be found".
- Prosedur patch, rollback, dan checklist produksi didokumentasikan di `SECURITY.md`.

⚠️ Perlu Perhatian
- Server produksi belum dipatch (belum tersedia). Semua node wajib PHP 8.2.35, 8.3.36, 8.4.27, atau 8.5.12 — patch adalah satu-satunya perbaikan penuh.
- Backup lokal: `C:\xampp\php-8.2.12-backup-20261023`; DLL Apache lama disimpan sebagai `*.bak-20261023`.
- `display_errors=On` masih aktif pada php.ini lokal (konfigurasi pengembangan XAMPP); matikan pada produksi.

**Berkas:** `SECURITY.md`, `changelog.md`, `.aicontext/activetodo.md` (di luar repo: `C:\xampp\php\php.ini`, `C:\xampp\apache\bin\libssh2.dll`, `nghttp2.dll`, `brotlidec.dll`)
**Verifikasi:** `php -v` → 8.2.35; `php -m` identik dengan sebelumnya (bcmath, bz2, curl, exif, fileinfo, ftp, gd, gettext, imap, intl, ldap, mbstring, mysqli, openssl, pdo_mysql, pdo_pgsql, pdo_sqlite, pgsql, soap, sockets, sodium, xsl, zip); halaman uji web melaporkan PHP 8.2.35 / apache2handler dengan curl, openssl, intl, zip, mbstring, pdo_mysql termuat; `php artisan test` 213 passed (829 assertions); aplikasi merespons HTTP 200; `apache/logs/error.log` bersih dari PHP Warning.

[2026-10-23] Pemeriksaan tautan tujuan dengan Google Safe Browsing

Ditambahkan
- Tautan tujuan diperiksa ke Google Safe Browsing Lookup v4 sebelum disimpan, sehingga tautan malware/phishing ditolak dengan pesan pada field "tautan panjang" di beranda publik, API v1, dan kedua panel. Pemeriksaan juga berlaku saat tujuan tautan diubah.
- **Alasan:** pemendek tautan publik tanpa pemeriksaan tujuan cepat dipakai menyebarkan phishing, yang berujung pada pemblokiran domain aplikasi.
- `SafeBrowsingService` + aturan validasi `TautanAman`, dengan kunci `config/pemendek.php` bagian `safe_browsing` (`enabled`, `api_key`, `endpoint`, `timeout_seconds`).

⚠️ Perlu Perhatian
- Isi `PEMENDEK_SAFE_BROWSING_KEY` dari Google Cloud Console dengan "Safe Browsing API" diaktifkan. Tanpa kunci, pemeriksaan sengaja dilewati.
- Layanan yang gagal atau timeout tidak memblokir pemendekan (fail-open); kegagalan dicatat sebagai warning berisi `sebab` dan `host` saja, tanpa URL tujuan utuh.
- Impor CSV massal melewati pemeriksaan (`AturanTautanPendek::semua(pindaiTujuan: false)`) karena memanggil layanan luar per baris hingga ribuan baris.

**Berkas:** `app/Services/ShortUrl/SafeBrowsingService.php`, `app/Support/Validation/TautanAman.php`, `app/Support/Validation/AturanTautanPendek.php`, `app/Services/Admin/AdminShortUrlImportService.php`, `config/pemendek.php`, `.env.example`, `.env`, `tests/Feature/SafeBrowsingTest.php`, `PRDapp.md`, `.aicontext/activetodo.md`, `.aicontext/coredependencies.md`
**Verifikasi:** `pint --test` lulus (seluruh repo); `php artisan test` 213 passed (829 assertions) termasuk 8 test baru; build tidak dijalankan karena tidak ada perubahan aset.

[2026-10-23] Lokalisasi menyeluruh panel admin

Diperbaiki
- Memilih bahasa Inggris kini mengubah seluruh label panel admin, bukan hanya menu bawaan Filament: kartu statistik, peringatan, grafik, tabel, filter, form, aksi, notifikasi, judul halaman, dan relation manager.
- **Akar masalah:** locale server sudah berubah, tetapi ratusan label kustom ditulis langsung dalam bahasa Indonesia sehingga tidak pernah diterjemahkan.
- Memperbaiki fatal error `getTitle()` pada relation manager; signature disesuaikan dengan `getTitle(Model $ownerRecord, string $pageClass): string` milik Filament v5.

Diubah
- Terjemahan memakai `lang/en.json` berbasis teks sumber Indonesia; locale `id` otomatis memakai teks asli tanpa duplikasi kunci.
- Trait `BahasaSumberDaya` menangani label model, navigasi, dan grup navigasi seluruh resource admin.
- Nilai `auth_provider=local` pada detail pengguna ikut diterjemahkan menjadi `Local`/`Lokal`.

**Berkas:** `lang/en.json`, `app/Filament/Concerns/BahasaSumberDaya.php`, `app/Filament/Admin/Resources/**`, `app/Filament/Admin/Pages/{BrandingSettings,ImportShortUrls,OperationalSettings,SecuritySettings}.php`, `app/Filament/Admin/Resources/ShortUrlResource/RelationManagers/ClickEventsRelationManager.php`, `resources/views/filament/{admin,widgets}/**`, `tests/Feature/BahasaTest.php`
**Verifikasi:** `php artisan test` 205 passed (813 assertions); `pint --test` lulus; `npm run build` lulus (70 modul); uji Playwright memastikan dashboard, pengguna, tautan, log, Linktree, pengaturan, impor, keamanan, dan branding tampil dalam bahasa Inggris.

---

[2026-10-22] Perbaikan pemilih bahasa panel dan perapian dashboard admin

Diperbaiki
- Pemilih bahasa di bilah atas panel admin dan pengguna tidak lagi bertumpuk. Tombol ID/EN sekarang disusun sejajar dalam satu grup, dengan pemisah tipis dari jam server dan menu akun.
- Memilih bahasa kini benar-benar memuat ulang halaman yang sedang dibuka. Form bahasa mengirim alamat halaman asal, sehingga pengguna tidak dilempar ke halaman awal dan pilihan langsung terlihat.
- Judul halaman `/user` ikut diterjemahkan (`Dashboard`/`My Dashboard`), bukan lagi teks tetap.
- **Akar masalah:** hook bahasa ditempatkan di area TOPBAR_END yang bertabrakan dengan menu akun; kelas `.fi-topbar-item` menimpa `display: flex` sehingga tombol menumpuk; controller bahasa selalu kembali ke `url()->previous()` yang tidak selalu halaman saat ini. Selain itu, kartu "Perlu Perhatian" di dashboard admin terlalu tinggi karena setiap peringatan memakai satu kartu statistik penuh.
- Dashboard admin dirapikan: peringatan kini berupa kartu ringkas dua kolom, kartu statistik dikurangi dari 9 menjadi 8 dengan urutan yang stabil, dan heatmap klik memakai kontras minimum yang lebih terbaca.

Diubah
- `AdminAttentionWidget` sekarang widget Blade mandiri bergaya kartu, bukan tumpukan `StatsOverviewWidget`; setiap peringatan tetap dapat diklik menuju daftar tindak lanjut.
- Urutan widget dashboard admin ditata ulang: metrik, peringatan, tren klik, pertumbuhan, tautan populer, tautan lintas zona, aktivitas, pengguna perlu perhatian, domain tujuan, heatmap, status sistem, peringkat pengguna.
- Metrik "Klik Hari Ini" digabung ke "Klik 30 Hari" agar tidak ada dua angka klik yang membingungkan pada periode yang sama.

**Berkas:** `resources/views/partials/pemilih-bahasa-filament.blade.php`, `app/Http/Controllers/LocaleController.php`, `app/Providers/Filament/{AdminPanelProvider,UserPanelProvider}.php`, `app/Filament/Widgets/AdminAttentionWidget.php`, `resources/views/filament/widgets/admin-attention.blade.php`, `app/Filament/Widgets/{StatsOverview,AdminGrowthChart,AdminForeignLinksTable,ActiveUsersTable,ClickHeatmapWidget}.php`, `resources/views/filament/widgets/click-heatmap.blade.php`, `resources/views/filament/components/server-clock.blade.php`, `app/Filament/User/Pages/UserDashboard.php`, `tests/Feature/BahasaTest.php`, `tests/Feature/AdminShortUrlToolsTest.php`
**Verifikasi:** `php artisan test` 205 passed (806 assertions); `pint --test` lulus; `npm run build` lulus (70 modul).

[2026-10-22] Dashboard insight pengguna/admin, pelacakan Linktree, dan filter operasional

Ditambahkan
- Dashboard pengguna kini punya pemilih periode 7/30/90 hari, tren klik dengan pembanding periode sebelumnya (total, rata-rata/hari, hari terbaik, delta), sumber klik teratas, jam dan hari tersibuk, kualitas klik berhasil/gagal, serta lima klik terbaru.
- Panel kesehatan tautan pengguna: kedaluwarsa ≤7 hari, sudah kedaluwarsa, terjadwal, belum pernah diklik, tautan aktif tanpa klik, klik gagal, dan Linktree terbit tanpa tautan/alamat. Setiap item membuka daftar Link Saya dengan filter yang sesuai.
- Statistik Linktree: kunjungan `/u/{alamat}`, pengunjung unik, klik tautan, rasio kunjungan→klik, dan tautan teratas. Pelacakan memakai tabel `linktree_events` dan route klik `/u/{alamat}/klik/{tautan}` yang mencatat event sebelum mengalihkan.
- Dashboard admin: filter periode global 7/30/90 yang memengaruhi metrik, grafik pertumbuhan, grafik klik, dan heatmap; delta terhadap periode sebelumnya pada kartu statistik.
- Widget aktivitas audit terbaru, domain tujuan teratas, heatmap hari × jam, tabel pengguna yang perlu perhatian, peringkat pengguna (jumlah tautan/tautan aktif/total klik/klik gagal/zona waktu), dan ringkasan sistem (penjadwal, antrean, retensi log, ukuran basis data, versi).
- Panel "Perlu Perhatian" diperluas: admin tanpa 2FA, pengguna baru >7 hari tanpa tautan, pengguna nonaktif dengan tautan aktif, tautan kedaluwarsa ≤7 hari, Linktree terbit tanpa alamat/tautan, login gagal berulang, klik gagal, penjadwal yang belum berdenyut, dan antrean gagal.
- Filter admin baru: pengguna tanpa 2FA/tanpa tautan/nonaktif bertautan; tautan berdasarkan pemilik, tanggal dibuat, kedaluwarsa ≤7 hari, dan lintas zona waktu. Aksi QR tersedia dari tabel tautan admin.

Diubah
- Ringkasan admin memakai periode terpilih dan menyertakan angka pembanding periode sebelumnya; label grafik mengikuti periode.
- `AdminDashboardService` memakai `DashboardPeriod` agar widget membaca `?periode=7|30|90` secara konsisten.
- Halaman `/user` menampilkan analitik yang dapat ditindaklanjuti, bukan hanya empat kartu ringkas.

Diperbaiki
- Tren klik pengguna tidak lagi membandingkan klik hari ini dengan enam hari sebelumnya sambil berlabel "minggu lalu"; pembanding sekarang periode penuh sebelumnya dan angka mentahnya ditampilkan.
- `AdminAnalyticsService::aktivitasTerbaru()` mengembalikan `Builder` agar cocok dengan `TableWidget::query()`.

Migrasi
- `database/migrations/2026_10_22_000000_create_linktree_events_table.php` membuat tabel `linktree_events` (profil, tautan opsional, jenis kunjungan/klik, IP hash, user agent, referer, waktu, indeks).

**Berkas utama:** `app/Services/Dashboard/{UserStatistikService,UserAktivitasService,UserKesehatanService,UserLinktreeStatistikService,UserDashboardService}.php`, `app/Services/Admin/{AdminDashboardService,AdminAnalyticsService,AdminSystemService,AdminAttentionService}.php`, `app/Filament/Widgets/*`, `app/Filament/User/Pages/UserDashboard.php`, `resources/views/filament/user/pages/kartu/*`, `resources/css/publik/panel.css`, `database/migrations/2026_10_22_000000_create_linktree_events_table.php`.
**Verifikasi:** `php artisan test` 203 passed (793 assertions); Pint passed; `npm run build` berhasil (70 modul); migrasi lokal sudah diterapkan (`Nothing to migrate`). Scheduler produksi harus menjalankan `php artisan schedule:run` setiap menit agar heartbeat status sistem dan peringatan tetap hidup.

---

[2026-10-21] Zona waktu fleksibel per pengguna, jam server di header admin, dan tautan lintas zona di dashboard

Ditambahkan
- `users.timezone` (nullable) untuk preferensi zona waktu pengguna. Pengguna dapat memilih zona di kartu Dashboard `/user`; zona terdeteksi otomatis dari browser saat pertama kali membuka halaman.
- `app/Services/Time/UserTimezoneService.php` mengubah input waktu lokal pengguna ke zona server sebelum disimpan, dan mengubah waktu server ke zona pengguna saat ditampilkan.
- Widget `app/Filament/Widgets/AdminForeignLinksTable.php` menampilkan tautan terbaru dari pengguna yang zonanya berbeda dengan server, lengkap dengan waktu dibuat/mulai/kedaluwarsa versi pengguna dan versi server.
- Jam digital kecil di bilah atas admin (`resources/views/filament/components/server-clock.blade.php`) memakai stempel waktu server dan hanya berdetak di browser, sehingga tidak bergantung pada jam perangkat.

Diubah
- Halaman `TimezoneSettings` admin, `TimezoneService`, dan test lama dihapus. Zona server kini murni dari `APP_TIMEZONE`; tidak ada lagi pengaturan global yang bisa menggeser waktu semua pengguna.
- Form tautan pengguna (dashboard Livewire dan resource Filament) serta daftar/detail/ekspor tautan memakai zona waktu pengguna. Waktu di database tetap memakai zona server agar laporan admin konsisten.
- Form dan infolist pengguna admin menampilkan serta dapat mengubah zona waktu pengguna.

Migrasi
- `database/migrations/2026_10_21_230000_add_timezone_to_users_table.php` menambah kolom `timezone` pada `users`.

Verifikasi
- `tests/Feature/UserTimezoneTest.php` 4 passed (15 assertions): jam admin tampil dan halaman lama 404, input kedaluwarsa New York tersimpan dalam WIB, perubahan zona tercatat `user.update`, serta widget lintas zona menampilkan waktu lokal + server.

[2026-10-21] Kelengkapan panel admin: audit, keamanan, moderasi, dukungan pengguna, dan operasional

⚠️ Perlu Perhatian / Breaking Changes
- Jalankan `php artisan migrate` untuk tiga migrasi baru: soft delete pengguna/tautan, tabel `notifications`, dan kolom dua faktor pada `users`.
- Scheduler produksi harus menjalankan `php artisan schedule:run` setiap menit agar `logs:purge` dan `admin:notify-attention` berjalan.
- Bila kolom "Daftar IP admin" diisi, akses `/admin` hanya dari IP/CIDR tersebut. Kosongkan bila ingin mengizinkan semua alamat.
- Tautan dan pengguna kini dihapus lunak. Hapus permanen tersedia terpisah dan hanya untuk data yang sudah dihapus serta bukan akun terlindungi/diri sendiri.

Ditambahkan
- Audit otomatis lewat `ShortUrlAuditObserver` dan `UserAuditObserver` untuk create/update/delete/restore/force delete, termasuk perubahan kolom dan penyamaran kata sandi.
- Pemulihan data terhapus (`RestoreAction`, `RestoreBulkAction`) dan hapus permanen dengan penjagaan akun sendiri/terlindungi.
- Resource moderasi `LinktreeProfileResource`, filter rentang tanggal pada empat log, ekspor CSV log admin, dan perintah `logs:purge`.
- Halaman detail pengguna dengan relation manager tautan, riwayat masuk, dan audit; aksi kirim tautan reset, keluarkan sesi, serta masuk sebagai pengguna (dengan banner dan tombol berhenti).
- Aksi massal tautan: aktifkan, nonaktifkan, perpanjang, dan pindahkan pemilik; infolist detail tautan dengan analitik klik dan riwayat klik.
- Dashboard admin menampilkan 8 metrik dan peringatan yang dapat diklik menuju halaman tindak lanjut.
- Notifikasi dalam aplikasi untuk admin (`admin:notify-attention`) dengan jeda 6 jam per jenis peringatan.
- Pengaturan operasional: slug dicadangkan, kedaluwarsa bawaan, retensi log, batas laju, pengumuman, dan daftar IP admin.
- Autentikasi dua faktor TOTP untuk admin, halaman verifikasi, dan audit aktif/nonaktif/gagal.
- Impor tautan CSV oleh admin dengan validasi yang sama seperti formulir dan laporan baris gagal.

Diubah
- Validasi formulir tautan admin kini memakai `AturanTautanPendek` (pola slug, panjang URL, jadwal kedaluwarsa, panjang kata sandi).
- Pembuatan tautan admin memakai helper kata sandi yang sama sehingga kata sandi benar-benar di-hash.
- Pembersihan log memakai retensi dari pengaturan bila opsi `--days` tidak diberikan.
- **Alasan:** menutup celah audit, mencegah kehilangan data permanen tanpa sengaja, dan memberi admin jalan menindaklanjuti masalah operasional.

Diperbaiki
- Tautan yang dihapus pengguna tidak lagi menghapus riwayat klik secara permanen.
- Admin dapat menyunting profilnya sendiri tanpa bisa menurunkan peran atau menonaktifkan akunnya sendiri.
- Test `UserDashboardTest` disesuaikan ke `assertSoftDeleted` setelah perubahan soft delete.

Migrasi
- `database/migrations/2026_10_21_200000_add_soft_deletes_to_users_and_short_urls_table.php`
- `database/migrations/2026_10_21_210000_create_notifications_table.php`
- `database/migrations/2026_10_21_220000_add_two_factor_to_users_table.php`

**Berkas:** `app/Observers/`, `app/Services/Admin/`, `app/Services/Setting/OperationalSettingsService.php`, `app/Services/Auth/TwoFactorService.php`, `app/Http/Middleware/{RequireAdminTwoFactor,RestrictAdminIp}.php`, `app/Filament/Admin/`, `app/Filament/Widgets/`, `routes/{web,console}.php`, `tests/Feature/Admin*Test.php`
**Verifikasi:** `pint --test` lulus; `php artisan test` 194 passed (698 assertions); `npm run build` lulus; migrasi lokal dijalankan.
