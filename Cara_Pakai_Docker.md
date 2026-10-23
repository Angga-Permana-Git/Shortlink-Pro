# Cara Pakai Docker — Shortlink

## Build & jalankan

```bash
# Mode 1 - gabungan app + queue + db
docker compose up -d --build          # http://localhost:8080

# Mode 2 - terpisah (urutan penting)
docker compose -f docker-compose.db.yaml up -d
docker compose -f docker-compose.app.yaml up -d --build
```

Build pertama mengunduh dependensi composer dan npm, jadi butuh koneksi
internet dan waktu beberapa menit. Build berikutnya memakai cache selama
`composer.lock`, `package-lock.json`, dan `Dockerfile` tidak berubah.

## Urutan stage di Dockerfile

| Stage | Isi | Alasan urutan |
| --- | --- | --- |
| `base` | PHP + ekstensi + Apache | Satu-satunya tempat versi PHP ditentukan |
| `vendor` | `composer install` | Manifest disalin dulu agar cache tidak batal saat kode berubah |
| `assets` | `npm ci` + `vite build` | Lockfile disalin dulu; sumber menyusul |
| `runtime` | Salinan hasil build | Tanpa compiler/composer/node |

Stage `assets` sengaja menerima `vendor` dari stage `vendor`: `resources/css/app.css`
memakai `@source` ke view pagination Laravel. Tanpa vendor di stage itu, kelas
pagination hilang dari CSS hasil build.

## Versi PHP

Image dikunci ke rilis yang sudah memuat patch PHP2Shell:

```bash
docker build --build-arg PHP_VERSION=8.3.36 -t shortlink-app:latest .
```

Bila tag tersebut tidak tersedia di registry, pakai rilis patch yang lebih baru
(8.3.37 dan seterusnya), **jangan** kembali ke 8.3.35 atau lebih lama. Jangan
memakai `php:8.3-apache` tanpa versi karena tag itu bergerak mengikuti rilis
terbaru dan hasil build tidak reproducible.

## Yang wajib disesuaikan

Compose membaca `.env` di root proyek untuk interpolasi `${...}`. Berkas itu
**tidak** ikut ke dalam image; nilainya hanya diteruskan sebagai environment.

- `APP_URL` — untuk Docker harus `http://localhost:8080` (atau domain asli di
  produksi), bukan `http://localhost`, karena nilai ini dipakai untuk URL aset,
  tautan pendek, dan redirect.
- `APP_PORT` — port host (default `8080`).
- `APP_ENV=production` dan `APP_DEBUG=false` untuk produksi. Bila `.env` host
  masih berisi nilai pengembangan, nilai itulah yang dipakai container.
- `DB_PASSWORD`, `DB_ROOT_PASSWORD` — wajib diganti untuk produksi.
- `APP_KEY` — isi sekali di `.env` host: `php artisan key:generate --show`.
  Compose meneruskan nilai yang sama ke `app` dan `queue`. Bila kosong,
  container `app` membuat kunci sendiri dan `queue` hanya memperingatkan,
  sehingga session/queue memakai kunci berbeda.
- `PHP_VERSION` — default `8.3.36`; naikkan bila memakai rilis patch lebih baru.
- `KEYCLOAK_BASE_URL`/`REALM`/`SECRET` — isi bila memakai SSO.
- `APP_SEED=true` bila ingin auto-seed demo data saat pertama kali.

Periksa hasil interpolasi tanpa menjalankan container:

```bash
docker compose config
```

## Kesehatan container

Image memakai `HEALTHCHECK` ke `http://127.0.0.1/up` (route bawaan Laravel).
Pantau dengan:

```bash
docker compose ps
docker compose logs -f app
```

## Catatan build

- `npm ci` dipakai karena `package-lock.json` ada di repo, sehingga versi aset
  sama dengan mesin pengembang.
- `docker/entrypoint.sh` menunggu database, menjalankan migrasi, `storage:link`,
  `filament:upgrade`, lalu `optimize`. Worker hanya menunggu database lalu
  menjalankan `queue:work` — tanpa migrasi.
- Service `queue` menunggu `app` berstatus `service_healthy`, jadi migrasi dan
  Apache sudah siap sebelum worker mulai. Saat container dihentikan, worker
  diberi waktu 100 detik agar job yang sedang berjalan tidak terpotong.
- Kedua service memakai `build` yang sama; build kedua hanya memakai cache.
- Untuk upgrade PHP berikutnya, ekstensi yang dikompilasi ada di stage `base`;
  tambahkan ekstensi baru di daftar `docker-php-ext-install` bila fitur baru
  membutuhkannya.
