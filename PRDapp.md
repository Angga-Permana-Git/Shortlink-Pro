---
name: prdapp
description: Sumber kebenaran produk Shortlink Pro — tujuan, pengguna, fitur, stack, keputusan produk, konvensi proyek, dan perintah verifikasi.
---

# PRD Shortlink Pro

Dokumen ini adalah sumber kebenaran untuk **apa** yang dibangun dan **untuk siapa**. Aturan kerja ada di `aturan_pengembangan.md`, struktur kode di `disiplin_struktur.md`, cara berpikir saat menulis kode di `disiplin_coding.md`, peta lokasi kode di `.aicontext/`, dan riwayat perubahan di `changelog.md`. Jika terjadi perbedaan pendapat antar dokumen, urutan prioritasnya ada di `aturan_pengembangan.md` bagian 1.

## 1. Tujuan produk

Shortlink Pro adalah layanan pemendek tautan internal yang dipasang di jaringan perusahaan. Tujuan utamanya:

1. **Memendekkan tautan** yang panjang menjadi tautan pendek yang mudah dibagikan, dengan pilihan slug kustom, masa berlaku, dan kata sandi.
2. **Memberi satu halaman tautan (Linktree)** untuk setiap pengguna, agar beberapa tautan penting bisa dibagikan lewat satu alamat publik.
3. **Menyediakan jejak** yang bisa diaudit: klik tautan, riwayat percobaan masuk, dan riwayat perubahan data penting.
4. **Menyediakan pengaturan terpusat** untuk branding aplikasi dan mode akses publik, tanpa menyentuh kode atau berkas `.env`.

Non-goal ada di bagian 8.

## 2. Pengguna dan peran

| Peran | Cara masuk | Yang bisa dilakukan |
|---|---|---|
| Tamu | tanpa akun | memendekkan tautan dari beranda, membuka tautan pendek, membuka halaman Linktree publik |
| Pengguna (`role = user`) | `/login/local` atau Keycloak | semua hak tamu, ditambah panel `/user`: membuat/mengubah/menghapus tautan miliknya dan mengatur halaman Linktree (nama, keterangan, alamat publik, logo profil) |
| Admin (`role = admin`) | `/login/local` atau Keycloak | semua hak pengguna, ditambah panel `/admin`: seluruh tautan, pengguna, jejak audit/klik/login, branding, dan zona waktu |

Peran disimpan di kolom `users.role` dan hanya boleh diubah oleh Admin; setiap perubahan peran dicatat di `role_change_logs`.

## 3. Fitur

### 3.1 Pemendekan tautan publik (`/`)

- Halaman beranda menampilkan formulir pemendekan; **tamu tidak perlu masuk** untuk memakainya.
- Input: `destination_url` (wajib, `http`/`https`, maksimal 2048 karakter), `slug` (opsional, pola `^[a-zA-Z0-9][a-zA-Z0-9-_]*$`, maksimal 100, harus unik), `expires_at` (opsional, harus di masa depan), `password` (opsional, minimal 8 karakter).
- Endpoint `POST /shorten`, dibatasi `throttle:10,1` (10 permintaan per menit per IP).
- Permintaan JSON menerima `201` berisi `data.slug`, `data.url`, `data.qr`, `data.protected`, `data.expires_at`.
- Permintaan biasa (form) kembali ke beranda dan **hasilnya tampil di bagian bawah formulir**.
- Halaman ini **tidak menawarkan Linktree**: tamu hanya menerima tautan pendek beserta QR-nya. Pembuatan dan pengaturan Linktree ada di panel pengguna setelah masuk.
- Saat `force_sso` aktif, tamu diarahkan ke Keycloak lebih dulu (middleware `EnsurePublicAccessAllowed`).

### 3.2 Membuka tautan pendek (`GET /{slug}`, `POST /{slug}`)

Urutan pemeriksaan pada `LinkAccessService`:

1. slug tidak ada → halaman `short.not_found` (404).
2. tautan tidak aktif → halaman status `inactive`.
3. tautan kedaluwarsa → halaman status `expired`.
4. tautan belum mulai berlaku → halaman status `scheduled`.
5. tautan ber-kata sandi dan sesi pengunjung belum membukanya → formulir `short.password`.
6. selain itu → redirect ke `destination_url`.

Batas percobaan kata sandi: 5 kali per tautan, terkunci 60 detik. Setiap percobaan dicatat sebagai `click_events` (status `protected` atau `wrong_password`), dan setiap redirect menambah `total_clicks` serta `last_clicked_at`. IP disimpan sebagai hash (`sha256(ip + APP_KEY)`), bukan teks asli.

### 3.3 Halaman Linktree publik (`/u/{alamat}`)

- Alamat publik adalah **slug pilihan pemiliknya** (`linktree_profiles.public_slug`), bukan username login: hanya huruf kecil, angka, dan tanda hubung, serta unik antar pengguna.
- Hanya menampilkan profil yang **diterbitkan** (`is_active = true`) dan **hanya tautan aktif**, berurutan sesuai `sort_order`.
- Profil atau alamat yang tidak ada, alamat yang belum diisi, atau profil yang tidak diterbitkan, menghasilkan 404.
- Yang tampil hanya **logo, nama tampilan, dan keterangan**; nama login tidak ditampilkan ke pengunjung.
- Logo profil yang diunggah pengguna menggantikan huruf pertama nama tampilan; tanpa logo, huruf pertama nama tampilan yang dipakai.
- Pengunjung tidak bisa mengubah apa pun dari halaman ini.

### 3.4 Panel pengguna (`/user`)

- Panel pengguna berupa **satu lembar** berisi empat kartu berurutan, tanpa sidebar: pengguna tidak berpindah halaman, seluruh alur berjalan dari kartu 01 ke kartu 04.
  1. **Dashboard** — angka ringkas tautan milik pengguna (total, aktif, total klik, dilindungi sandi).
  2. **Pemendek Tautan** — pembuatan tautan pendek; hasilnya tampil beserta salin, QR, dan pintasan ke kartu Link Saya.
  3. **Link Saya** — daftar tautan dengan aksi salin, QR, sunting, dan hapus, sekaligus centang tautan yang tampil di halaman Linktree dan status terbit halaman itu. Kartu ini bisa dilipat agar lembar tetap ringkas.
  4. **Profil Linktree** — logo, nama tampilan, keterangan, dan alamat publik, beserta pratinjau halaman publik. Hanya bagian identitas yang bisa dilipat; pratinjaunya selalu tampil.
- Menekan **QR** pada satu baris membuka panel QR tautan itu (`GET /qr/{slug}`, SVG) beserta tombol unduh; panelnya ditutup lewat tombol Tutup atau saat baris itu disunting/dihapus.
- **Alamat publik wajib diisi** di kartu Profil Linktree (`public_slug`); mengubahnya membuat alamat lama tidak lagi bisa dibuka.
- Slug kustom menerima tempelan URL penuh (bagian terakhir path yang dipakai).
- Perubahan tautan mencakup tautan panjang, aktif/nonaktif, waktu kedaluwarsa, dan kata sandi (isi untuk mengganti, matikan proteksi untuk melepasnya). **Slug tidak diubah**, karena alamat pendek beserta QR yang sudah dibagikan akan mati bila slug berubah.
- **Penghapusan tautan milik sendiri** tersedia di kartu Link Saya; tautan yang dihapus sekaligus dilepas dari pilihan Linktree. Admin tetap bisa menghapus tautan siapa pun dari panel admin.
- Logo profil disimpan di disk `public` (`linktree/{user_id}/`) dan ditampilkan di halaman publik. Pengguna bisa menghapus logo itu kapan saja.
- Halaman `/user/short-urls` dan `/user/linktree-settings` masih ada dan bisa dibuka langsung, tetapi tidak lagi ditautkan dari lembar `/user`.
- Pengguna hanya bisa melihat dan mengubah tautan miliknya (kueri resource dibatasi `owner_id`; `ShortUrlPolicy` memeriksa kepemilikan dan mengizinkan pemilik menghapus tautannya sendiri).

### 3.5 Panel admin (`/admin`)

- **Tautan Pendek**: seluruh tautan, termasuk pembuatan dan perubahan.
- **Pengguna**: daftar, pembuatan, dan perubahan pengguna (termasuk peran).
- **Jejak baca-saja**: `Audit Log`, `Click Event`, `Login Audit Log`, `Role Change Log`.
- **Branding** (`/admin/branding-settings`): nama aplikasi, unggah/hapus logo login, dan tombol **Paksa login Keycloak** (`force_sso`).
- **Pengaturan Waktu** (`/admin/timezone-settings`): zona waktu aplikasi beserta jam berjalan.
- Dasbor berisi `StatsOverview` dan grafik `ClicksPerDayChart` (memakai warna brand).

### 3.6 Autentikasi

- **Keycloak (SSO utama)** lewat `laravel/socialite` + `socialiteproviders/keycloak`.
  - `GET /auth/keycloak/redirect` menyimpan `keycloak_state` di sesi, lalu mengarahkan ke Keycloak.
  - `GET /auth/keycloak/callback` memeriksa state, mengambil klaim, lalu menyiapkan akun.
  - Akun dicocokkan lewat kolom `keycloak_sub`; bila belum ada, akun dibuat otomatis dengan peran `user`.
  - Username kosong atau termasuk daftar cadangan (`root`, `admin`, `superadmin`, `administrator`) ditolak (403).
  - Akun yang dinonaktifkan tidak boleh masuk (403).
- **Masuk dengan Google** lewat `laravel/socialite` (driver Google bawaan).
  - Tombol "Masuk dengan Google" hanya tampil bila `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` terisi; tanpa kredensial, login berjalan seperti sebelumnya. Lihat `GOOGLE_SETUP.md`.
  - `GET /auth/google/redirect` mengarahkan ke Google; `GET /auth/google/callback` mengambil klaim lalu menyiapkan akun.
  - Akun dicocokkan lewat kolom `google_sub`; bila belum ada, email terverifikasi milik akun lokal yang sama **ditautkan** (kata sandi lamanya tetap bisa dipakai), selain itu akun baru dibuat dengan peran `user` dan tanpa kata sandi.
  - Username akun baru diambil dari bagian depan email, dibersihkan agar lolos `alpha_dash`, dan dihindari dari daftar cadangan (`app/Support/Auth/UsernameAkun.php`).
  - Email yang belum terverifikasi, akun nonaktif, dan email milik penyedia masuk lain ditolak: pengguna kembali ke halaman masuk membawa pesan galatnya.
- **Login darurat lokal** (`/login/local`, `POST /login/local`): hanya untuk akun dengan `auth_provider = local`, harus aktif dan punya kata sandi. Batas 5 percobaan per 60 detik per username. Throttle rute `throttle:5,1`.
- **Pendaftaran lokal** (`/register`, `POST /register`): nama, username (`alpha_dash`, unik), email (unik), kata sandi minimal 12 karakter dan dikonfirmasi. Kata sandi di-hash di `RegistrationService`, bukan di controller. Akun baru berperan `user`. Pendaftaran selalu terbuka untuk tamu.
- **Lupa kata sandi** (`/forgot-password`, `POST /forgot-password`), hanya untuk akun lokal yang aktif dan punya kata sandi.
  - Token, masa berlaku 60 menit, dan pembatasan permintaan (60 detik per akun) memakai broker kata sandi bawaan Laravel (`password_reset_tokens`); token disimpan dalam bentuk hash.
  - Email berbahasa Indonesia dikirim lewat `App\Notifications\ResetPasswordNotification`.
  - Akun Google atau SSO dijawab dengan penjelasan penyedianya, bukan tautan. Email yang tidak dikenali dan akun nonaktif dijawab dengan pesan yang sama agar tidak membocorkan keberadaan akun.
  - `GET /reset-password/{token}` menampilkan formulir, `POST /reset-password` menyimpan kata sandi baru (minimal 12 karakter dan dikonfirmasi). Perubahan dicatat di `audit_logs` sebagai `auth.password_reset`.
  - Selama `MAIL_MAILER=log` (pengembangan), tautan atur ulang ikut ditampilkan di layar agar alurnya bisa diuji tanpa SMTP; pada mailer lain tautan tidak pernah ditampilkan.
- **Keluar** (`POST /logout`): sesi dibersihkan; bila SSO aktif, pengguna juga diarahkan keluar dari Keycloak.
- Setiap percobaan masuk dicatat di `login_audit_logs` (berhasil/gagal, provider, IP, user agent).

### 3.7 API v1

| Metode | Alamat | Akses | Keterangan |
|---|---|---|---|
| POST | `/api/v1/links` | publik (`throttle:10,1`) | membuat tautan pendek; `201` berisi `data.slug` dan `data.url` |
| GET | `/api/v1/linktree` | perlu login | profil Linktree milik pemanggil beserta tautannya |
| PUT | `/api/v1/linktree` | perlu login | mengubah `display_name`, `bio`, `public_slug`, dan/atau `is_active` (kolom yang tidak dikirim tidak diubah) |

### 3.8 Kode QR

- `GET /qr/{slug}` menyajikan kode QR berupa SVG untuk tautan tersebut (`chillerlan/php-qrcode`), dipakai di beranda dan panel.

## 4. Stack

| Bagian | Pilihan |
|---|---|
| Bahasa | PHP 8.2 |
| Framework | Laravel 12 |
| Panel admin & pengguna | Filament v5 (Livewire) |
| Autentikasi | Session Laravel + Keycloak dan Google via Laravel Socialite, serta login lokal dan atur ulang kata sandi |
| Email | Notifikasi Laravel lewat mailer `MAIL_MAILER` (`log` pada pengembangan, SMTP pada produksi) |
| Basis data | MySQL (pengembangan lokal), SQLite in-memory (pengujian) |
| Sesi, cache, antrean | Driver `database` |
| Aset | Vite + Tailwind v4 (khusus panel Filament); halaman publik memakai CSS biasa |
| Bahasa antarmuka | `APP_LOCALE=id`; label Filament memakai terjemahan `id` bawaan paket, kunci yang belum tersedia ditimpa di `lang/vendor/` |
| Basis simpan | `storage/` (log, cache, unggahan logo di disk `public`) |
| QR | `chillerlan/php-qrcode` |

Aturan: jangan menambah, menaikkan, atau mengubah versi dependensi tanpa diminta (`aturan_pengembangan.md` bagian 10).

## 5. Keputusan produk dan teknis

Setiap keputusan di bawah ini mengikat; perubahan harus melalui pengguna lebih dulu.

1. **Satu tombol mode akses publik.** Kunci pengaturan `force_sso` hanya dibaca dan ditulis oleh `AccessModeService`.
   - `force_sso` aktif → tamu diarahkan ke Keycloak sebelum bisa membuka halaman publik; halaman masuk menampilkan tombol "Login dengan SSO".
   - `force_sso` nonaktif → beranda publik terbuka dan bisa dipakai tanpa akun; halaman masuk mengarah ke formulir login lokal.
2. **Tamu boleh memendekkan tautan tanpa akun.** Hasil pemendekan selalu muncul **di bawah** formulir, bukan di halaman terpisah.
3. **Kartu promosi Linktree di beranda tidak memuat tombol masuk.** Tombol "Lanjutkan dengan Keycloak" hanya muncul saat `force_sso` aktif.
4. **Warna beranda dipertahankan.** Perubahan tampilan tidak boleh mengganti palet yang sudah dipakai; nilai desain diambil dari token.
5. **Linktree adalah fasilitas pengguna yang sudah masuk.** Membuat dan mengatur Linktree hanya bisa dari panel pengguna (`/user` atau `/user/linktree-settings`) setelah login. Alur pemendekan tautan di halaman awal (`/`) tidak menawarkan Linktree: tamu hanya mendapat tautan pendek dan QR-nya. Halaman Linktree publik `/u/{alamat}` tetap bisa dibuka tanpa akun, dan alamatnya dipilih sendiri oleh pemiliknya bukan dari username login. Kartu promosi di beranda hanya menjelaskan fasilitas ini untuk pengguna terdaftar, tanpa tombol masuk.
6. **Login lokal adalah jalur darurat.** Hanya akun `auth_provider = local` yang boleh memakainya, sehingga akun SSO tidak bisa dilewati.
7. **Kata sandi tidak pernah disimpan atau ditampilkan apa adanya.** Kata sandi pengguna dan kata sandi tautan selalu di-hash (Laravel `Hash`).
8. **Semua perubahan penting meninggalkan jejak**: `audit_logs` (perubahan data), `click_events` (klik tautan), `login_audit_logs` (percobaan masuk), `role_change_logs` (perubahan peran).
9. **Akun demo dibuat lewat perintah**, bukan lewat kode: `php artisan app:create-demo-accounts` dengan kata sandi dari `DEMO_ACCOUNT_PASSWORD`. Perintah menolak berjalan di produksi tanpa `--force`.
10. **Tidak ada rahasia di dalam kode.** Semua nilai sensitif berasal dari environment (lihat `disiplin_struktur.md` bagian 5).
11. **Data antar layer dikirim sebagai DTO** (`app/Dto/*DTO.php`), dibangun di controller atau handler lewat `fromArray()`, sehingga service tidak lagi menerima array tanpa bentuk yang jelas.
12. **Service tidak menyentuh HTTP.** Kebutuhan mencatat IP, user agent, dan referer dikirim lewat `RequestContextDTO`. Pengecualian tunggal: `AuditLogger::log()` membaca `request()->ip()` karena dipakai dari semua layer, termasuk halaman panel.
13. **Akun Google ditautkan lewat email terverifikasi.** Email `email_verified` dari Google wajib `true`. Bila email itu sudah dipakai akun lokal, akun tersebut yang dipakai dan hanya kolom `google_sub` yang diisi, sehingga masuk dengan kata sandi tetap berlaku; email milik penyedia masuk lain (Keycloak) tidak pernah ditimpa. Akun Google baru dibuat tanpa kata sandi, jadi tidak bisa memakai login lokal maupun atur ulang kata sandi.
14. **Atur ulang kata sandi hanya untuk akun lokal.** Akun Google dan SSO dijawab dengan penjelasan penyedianya. Pratinjau tautan di layar hanya muncul saat `MAIL_MAILER=log`, jadi produksi tidak pernah menampilkan tautan atur ulang.
15. **Tautan tujuan diperiksa sebelum disimpan.** Tujuan baru maupun tujuan yang diubah diperiksa ke Google Safe Browsing; tautan yang terdaftar sebagai malware/phishing ditolak dengan galat pada field "tautan panjang". Kunci API berasal dari environment dan tanpa kunci pemeriksaan dilewati. Kegagalan layanan tidak memblokir pemendekan (fail-open) dan dicatat sebagai warning tanpa URL utuh. Impor CSV massal melewati pemeriksaan ini karena memanggil layanan luar per baris.

## 6. Konvensi proyek

Nilai bawaan `disiplin_struktur.md` berlaku, dengan penegasan dan penyimpangan tertulis berikut.

**Bahasa**
- Bahasa Indonesia untuk seluruh teks tampilan, komentar kode, pesan log, nama berkas tampilan/CSS/JavaScript, dan dokumen.
- Identifier PHP tetap Bahasa Inggris dan mengikuti konvensi Laravel (mis. `ShortUrlService::create()`), karena nama kelas dan tabel sudah terlanjur Inggris di seluruh proyek.

**DTO**
- Berkas DTO berada di `app/Dto/`, nama berakhiran `DTO` (`CreateShortUrlDTO`, `UpdateShortUrlDTO`). Aturan penamaan ada di `disiplin_struktur.md` bagian 8; letak foldernya ditetapkan di sini.

**Controller**
- Controller satu-tujuan memakai `__invoke` dan berkas sendiri; kelompok aksi yang serumpun diletakkan dalam satu folder domain (`app/Http/Controllers/Auth/`, `app/Http/Controllers/Redirect/`).
- Controller ringkas yang masih memuat dua langkah dari satu domain (mis. `RegisterController` dengan `create` dan `store`) tidak dipecah.
- Batas 30 baris dihitung dari isi kelas (deklarasi dan pernyataan), di luar pembuka berkas, `use`, docblock, dan baris kosong. Batas 100 baris service dihitung dari jumlah baris berkas.

**Struktur test**
- Direktori `tests/Unit` dan `tests/Feature` (huruf awal besar) mengikuti bawaan Laravel dan menggantikan contoh huruf kecil di `disiplin_struktur.md` bagian 9, sesuai bagian 8 dokumen yang sama (ikuti konvensi framework).

**Tampilan**
- Metode styling: halaman publik memakai CSS biasa dengan kelas BEM dan token di `resources/views/partials/tokens.blade.php`; panel Filament memakai Tailwind bawaan Filament. Tidak ada halaman publik yang memakai Tailwind, dan tidak ada gaya inline selain nilai dinamis.
- Breakpoint: mobile-first dengan satu acuan `--breakpoint-sedang: 40rem`. Nilainya ditulis literal di media query karena CSS tidak mengizinkan `var()` di sana. Panel mengikuti breakpoint bawaan Filament.
- Kontras teks mengikuti WCAG 2.1 AA (rasio minimal 4,5:1 untuk teks biasa, 3:1 untuk teks besar dan batas komponen).
- Ukuran layar acuan pemeriksaan tampilan: 360 px (telepon), 768 px (tablet), dan 1280 px (desktop).

**Sesi dan data pribadi**
- Autentikasi memakai session cookie Laravel; tidak ada token yang disimpan di `localStorage`.
- IP pengunjung hanya disimpan sebagai hash di `click_events`; IP asli hanya ada di jejak audit yang bisa diakses Admin.

**Basis data**
- Migrasi yang sudah dijalankan tidak diedit; perubahan selalu lewat migrasi baru (`aturan_pengembangan.md` bagian 6).

## 7. Perintah verifikasi

Jalankan seluruh perintah berikut sebelum menyatakan sebuah perubahan selesai:

```powershell
# 1. Format dan lint kode PHP
C:\xampp\php\php.exe vendor\bin\pint --test

# 2. Uji unit dan feature (SQLite in-memory)
C:\xampp\php\php.exe artisan test

# 3. Build aset tampilan
npm run build
```

Pemeriksaan tambahan sesuai jenis perubahan:

```powershell
# Daftar rute (memastikan rute terbaca setelah perubahan controller)
C:\xampp\php\php.exe artisan route:list --except-vendor

# Uji nyata dengan server berjalan (beranda, halaman Linktree publik, API)
C:\xampp\php\php.exe artisan serve
Invoke-WebRequest -Uri http://127.0.0.1:8000/ -UseBasicParsing | Select-Object StatusCode
Invoke-WebRequest -Uri http://127.0.0.1:8000/u/user -UseBasicParsing | Select-Object StatusCode
```

Catatan: halaman publik memakai `@vite`, jadi `npm run build` (atau `npm run dev`) wajib dijalankan sebelum halaman dibuka. Uji E2E hanya dijalankan saat aplikasi dinyatakan selesai atau saat pengguna meminta verifikasi penuh (`uji_e2e.md`).

Uji E2E (e2e@0.17 + Chromium, konfigurasi di `e2e.config.ts`; server dinyalakan otomatis pada port acak):

```powershell
npx e2e run                                  # seluruh suite
npx e2e run tests/e2e/03-user-panel.e2e.ts   # satu berkas
C:\xampp\php\php.exe tests\e2e\bersihkan.php # hapus data uji setelah suite
```

Laporan dan artefak kegagalan ada di `.e2e/` (gitignored); pohon aksesibilitas saat gagal dibaca dari `.e2e/artifacts/<uji>/failure/screen.txt`.

## 8. Non-goal

Hal-hal berikut **bukan** bagian dari produk ini pada fase sekarang:

- Multi-tenant atau pemisahan data per organisasi.
- Tema gelap, animasi, atau efek visual yang tidak diminta.
- Analitik lanjutan (grafik per negara, perangkat, atau atribusi kampanye).
- Integrasi pembayaran, langganan, atau kuota berbayar.
- Aplikasi mobile native atau API publik tanpa autentikasi.
- Antarmuka dalam bahasa selain Bahasa Indonesia.
