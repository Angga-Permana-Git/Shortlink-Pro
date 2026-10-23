#!/usr/bin/env bash
# =============================================================================
# entrypoint.sh - bootstrap otomatis aplikasi di dalam container
#
# Urutan:
#   1. Buat .env dari .env.example jika belum ada
#   2. Tunggu database MySQL siap
#   3. Mode 'worker'  -> langsung jalankan perintah (queue:work)
#   4. Mode 'app'     -> generate APP_KEY bila perlu, migrate, seed opsional,
#                        storage:link, filament:upgrade, optimize, rapikan izin,
#                        lalu start Apache
#
# APP_KEY hanya dibuat oleh container app. Container worker WAJIB menerima
# APP_KEY yang sama lewat environment (lihat docker-compose).
# =============================================================================
set -euo pipefail

APP_DIR=/var/www/html
cd "$APP_DIR"

ROLE="${APP_ROLE:-app}"

# Compose mengirim APP_KEY kosong bila host tidak mengisinya. Variabel
# lingkungan kosong menimpa isi .env (Dotenv immutable), sehingga
# key:generate tidak akan berefek. Buang dulu agar .env yang dipakai.
if [ -z "${APP_KEY:-}" ]; then
    unset APP_KEY
fi

# ---------------------------------------------------------------- .env
if [ ! -f .env ]; then
    echo "[entrypoint] .env belum ada -> disalin dari .env.example"
    cp .env.example .env
fi

# ---------------------------------------------------------------- APP_KEY
# Catatan: nilai environment di compose MENIMPA nilai di file .env.
if [ "$ROLE" != "worker" ] \
    && [ -z "${APP_KEY:-}" ] \
    && ! grep -qE '^APP_KEY=.+' .env; then
    echo "[entrypoint] generate APP_KEY ..."
    php artisan key:generate --force --no-interaction
fi

# ---------------------------------------------------------------- tunggu DB
: "${DB_HOST:?DB_HOST wajib diset (via environment compose)}"
DB_PORT="${DB_PORT:-3306}"

echo "[entrypoint] menunggu database ${DB_HOST}:${DB_PORT} ..."
attempt=1
until php -r '
    try {
        new PDO(
            "mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
        exit(0);
    } catch (Throwable $e) {
        exit(1);
    }
' >/dev/null 2>&1; do
    if [ "$attempt" -ge 60 ]; then
        echo "[entrypoint] GAGAL: database tidak siap setelah 120 detik."
        exit 1
    fi
    echo "[entrypoint] database belum siap, coba lagi ($attempt/60) ..."
    attempt=$((attempt + 1))
    sleep 2
done
echo "[entrypoint] database siap."

# ---------------------------------------------------------------- worker
if [ "$ROLE" = "worker" ]; then
    if [ -z "${APP_KEY:-}" ]; then
        echo "[entrypoint] PERINGATAN: APP_KEY tidak diset di environment worker."
        echo "[entrypoint]             Set APP_KEY yang SAMA dengan container app."
    fi
    echo "[entrypoint] mode worker -> jalankan: $*"
    exec "$@"
fi

# ---------------------------------------------------------------- app
# Volume storage bisa berasal dari container lama yang dimiliki root, jadi
# izin dirapikan dulu supaya artisan tidak gagal menulis log/session.
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
find storage bootstrap/cache -type d -exec chmod 775 {} + 2>/dev/null || true

# Migrasi (idempotent). Worker sudah keluar di atas, jadi hanya container app
# yang menjalankan migrasi.
echo "[entrypoint] menjalankan migrate --force ..."
php artisan migrate --force --no-interaction \
    || echo "[entrypoint] PERINGATAN: migrate gagal, periksa log di atas."

# Seed demo data (opsional, aktifkan via env APP_SEED=true)
if [ "${APP_SEED:-false}" = "true" ]; then
    echo "[entrypoint] menjalankan db:seed (demo data) ..."
    php artisan db:seed --force --no-interaction
fi

# Symlink storage -> public/storage (untuk logo/branding & upload)
php artisan storage:link --no-interaction 2>/dev/null \
    || echo "[entrypoint] storage:link dilewati (mungkin sudah ada)."

# Selaraskan aset Filament dengan versi paket di image. Aman dijalankan
# berulang dan membuat aset lama tidak tertinggal setelah upgrade.
php artisan filament:upgrade --no-interaction 2>/dev/null \
    || echo "[entrypoint] filament:upgrade dilewati."

# Optimasi cache (config/route/view/event) SETELAH .env siap
php artisan optimize --no-interaction 2>/dev/null \
    || php artisan config:clear --no-interaction 2>/dev/null \
    || true

# Artisan di atas berjalan sebagai root sehingga cache/log baru dimiliki root.
# Kembalikan kepemilikan agar www-data bisa menulis saat melayani request.
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
find storage bootstrap/cache -type d -exec chmod 775 {} + 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 664 {} + 2>/dev/null || true

echo "[entrypoint] siap. Menjalankan: $*"
exec "$@"
