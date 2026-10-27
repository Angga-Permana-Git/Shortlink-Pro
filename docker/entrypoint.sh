#!/usr/bin/env bash
# =============================================================================
# Entrypoint produksi Shortlink Pro
# - Menunggu basis data siap sebelum migrasi.
# - Menjalankan migrasi (sekali, dari service app).
# - Menyiapkan cache Laravel, lalu menyerahkan kendali ke CMD.
# Tidak menulis .env dan tidak membuat APP_KEY: keduanya datang dari env_file
# supaya image tetap immutable dan read-only.
# =============================================================================
set -euo pipefail

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY kosong. Isi APP_KEY di berkas .env (format base64:...)." >&2
    exit 1
fi

mkdir -p \
    /run/apache2 \
    /run/lock/apache2 \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Volume bernama dibuat Docker sebagai root, sedangkan Apache berjalan sebagai
# www-data. bootstrap/cache biasanya tmpfs; chown tetap aman diulang.
# Jangan chmod di sini: container hanya punya cap CHOWN/SETUID/SETGID/KILL,
# tanpa FOWNER, sehingga chmod pada berkas milik www-data akan gagal.
chown -R www-data:www-data storage bootstrap/cache

# Perintah artisan dijalankan sebagai www-data supaya berkas yang dibuat
# (cache view, log) tidak berakhir milik root dan tetap bisa ditulis Apache.
jalankan_sebagai_www() {
    setpriv --reuid=www-data --regid=www-data --clear-groups "$@"
}

tunggu_basis_data() {
    php -r '
        $host = getenv("DB_HOST") ?: "db";
        $port = getenv("DB_PORT") ?: "3306";
        $nama = getenv("DB_DATABASE") ?: "";
        $pengguna = getenv("DB_USERNAME") ?: "";
        $sandi = getenv("DB_PASSWORD") ?: "";
        $batas = time() + 60;

        do {
            try {
                new PDO(
                    "mysql:host={$host};port={$port};dbname={$nama}",
                    $pengguna,
                    $sandi,
                    [PDO::ATTR_TIMEOUT => 2]
                );

                return;
            } catch (Throwable $e) {
                if (time() >= $batas) {
                    fwrite(STDERR, "Basis data tidak siap: {$e->getMessage()}\n");
                    exit(1);
                }

                sleep(2);
            }
        } while (true);
    '
}

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    tunggu_basis_data
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    jalankan_sebagai_www php artisan migrate --force --no-interaction
fi

jalankan_sebagai_www php artisan optimize --no-interaction

# Scheduler (`php artisan schedule:work`) juga dijalankan sebagai www-data.
# Apache tetap root karena master perlu menurunkan hak ke www-data.
if [ "${1:-}" = "php" ]; then
    exec setpriv --reuid=www-data --regid=www-data --clear-groups "$@"
fi

exec "$@"
