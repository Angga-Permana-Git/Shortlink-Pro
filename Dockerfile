# syntax=docker/dockerfile:1
# =============================================================================
# Dockerfile - Aplikasi Shortlink (Laravel 12 + Filament + MySQL)
#
# Build sekali jalan:
#   1. composer install (dependensi PHP, tanpa 'dev')
#   2. npm install + npm run build (aset frontend, otomatis saat build)
#   3. runtime PHP 8.3 + Apache, siap dijalankan via docker-compose
#
# Entrypoint (docker/entrypoint.sh) mengurus otomatis:
#   .env (dibuat dari .env.example jika belum ada), APP_KEY,
#   menunggu database, migrate, storage:link, dan optimize cache.
# =============================================================================

# ---------------------------------------------------------------------------
# STAGE 1: vendor  -> composer install
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

# Salin seluruh project (pakai .dockerignore agar vendor/node_modules/.env tidak ikut)
COPY . .

# --no-scripts: jalur post-autoload-dump (package:discover / filament:upgrade)
# dijalankan di runtime via entrypoint agar tidak gagal tanpa .env.
# --no-dev: image produksi tidak butuh phpunit/pint/dll.
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-ansi \
        --no-scripts \
        --optimize-autoloader

# ---------------------------------------------------------------------------
# STAGE 2: node  -> npm install + build aset Vite
# ---------------------------------------------------------------------------
FROM node:22-alpine AS node
WORKDIR /app

# package-lock.json tidak dipakai di repo ini, jadi gunakan `npm install`
# (bukan `npm ci`) agar tidak gagal.
COPY package.json ./
COPY vite.config.js ./
COPY resources ./resources
RUN mkdir -p public \
    && npm install --no-audit --no-fund
RUN npm run build

# ---------------------------------------------------------------------------
# STAGE 3: runtime  -> PHP 8.3 + Apache
# ---------------------------------------------------------------------------
FROM php:8.3-apache AS runtime

ENV APP_DIR=/var/www/html

# Ekstensi PHP yang dibutuhkan aplikasi (Laravel / Filament):
#   pdo_mysql (DB MySQL), gd (proses gambar/logo), intl, zip,
#   bcmath, exif, pcntl, mbstring, opcache
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        bcmath \
        exif \
        pcntl \
        mbstring \
        gd \
        intl \
        zip \
    && docker-php-ext-enable opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

WORKDIR $APP_DIR

# Aplikasi (vendor/node_modules/public/build/.env tidak ikut lewat .dockerignore)
COPY . .

# Overlay vendor (hasil composer) & aset build (hasil vite)
COPY --from=vendor /app/vendor ./vendor
COPY --from=node  /app/public/build ./public/build

# Konfigurasi Apache + PHP + entrypoint
COPY docker/apache.conf   /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini       /usr/local/etc/php/conf.d/99-app.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Direktori runtime yang wajib writable oleh www-data
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/testing \
        storage/logs \
        storage/app/public \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]