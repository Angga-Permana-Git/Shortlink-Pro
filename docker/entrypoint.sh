#!/usr/bin/env bash
# =============================================================================
# entrypoint.sh - bootstrap otomatis aplikasi di dalam container
#
# Urutan:
#   1. Buat .env dari .env.example jika belum ada
#   2. Generate APP_KEY jika belum ada (dan tidak diset via env)
#   3. Tunggu database MySQL siap
#   4. Mode 'worker'  -> langsung jalankan perintah (queue:work)
#   5. Mode 'app'     -> chown storage, migrate, seed opsional,
#                        storage:link, optimize, lalu start Apache
# =============================================================================
set -euo pipefail

APP_DIR=/var/www/html
cd "$APP_DIR"

# ---------------------------------------------------------------- .env
if [ ! -f .env ]; then
    echo "[entrypoint] .env belum ada -> disalin dari .env.example"
    cp .env.example .env
fi

# ---------------------------------------------------------------- APP_KEY
# Catatan: nilai environment di compose MENIMPA nilai di file .env.
# Untuk kestabilan (sessions/queue terenkripsi) set APP_KEY SAMA di semua
# service (lihat baris # APP_KEY: ... pada docker-compose).
if [ -z "${APP_KEY:-}" ] && ! grep -qE '^APP_KEY=.+' .env; then
    echo "[entrypoint] generate APP_KEY ..."
    php artisan key:generate --force --no-interaction
fi

# ---------------------------------------------------------------- tunggu DB
: "${DB_HOST:?DB_HOST wajib diset (via environment compose)}"
DB_HOST="${DB_HOST:-127.0.0.1}"
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
if [ "${APP_ROLE:-app}" = "worker" ]; then
    echo "[entrypoint] mode worker -> jalankan: $*"
    exec "$@"
fi

# ---------------------------------------------------------------- app
# Hak akses storage & bootstrap/cache untuk www-data
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
find storage bootstrap/cache -type d -exec chmod 775 {} + 2>/dev/null || true

# Migrasi (idempotent; di mode semua-in-one sebaiknya hanya 1 container yg migrate,
# tapi karena worker mode sudah di-skip, migrate hanya terjadi di container app)
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

# Optimasi cache (config/route/view/event) SETELAH .env siap
php artisan optimize --no-interaction 2>/dev/null \
    || php artisan config:clear --no-interaction 2>/dev/null \
    || true

echo "[entrypoint] siap. Menjalankan: $*"
exec "$@"