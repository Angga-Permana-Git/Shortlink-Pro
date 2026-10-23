# syntax=docker/dockerfile:1
# =============================================================================
# Dockerfile - Shortlink (Laravel 12 + Filament v5)
#
# Urutan stage dibuat berlapis supaya build tidak gagal dan cache tidak sia-sia:
#   base    -> PHP + ekstensi + Apache (satu-satunya tempat versi PHP)
#   vendor  -> composer install: manifest dulu, sumber belakangan
#   assets  -> npm ci: lockfile dulu, sumber belakangan, lalu vite build
#   runtime -> hanya menyalin hasil, tanpa tool build
#
# Build:
#   docker compose build
#   docker build -t shortlink-app:latest .
#
# Versi PHP dikunci ke rilis yang sudah memuat patch PHP2Shell
# (CVE-2026-109618 & CVE-2026-109619). Bila tag tersebut tidak tersedia di
# registry, naikkan ke rilis patch berikutnya, jangan turunkan:
#   docker build --build-arg PHP_VERSION=8.3.36 -t shortlink-app:latest .
# =============================================================================

ARG PHP_VERSION=8.3.36

# ---------------------------------------------------------------------------
# base: PHP + ekstensi + Apache
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-apache AS base

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Ekstensi disusun dalam satu layer agar perubahan konfigurasi Apache tidak
# memicu kompilasi ulang ekstensi.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
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
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# ---------------------------------------------------------------------------
# vendor: dependensi PHP
# ---------------------------------------------------------------------------
FROM base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# Manifest disalin lebih dulu: selama composer.json/composer.lock tidak
# berubah, layer install tetap ter-cache walau kode aplikasi berubah.
COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-ansi \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

# Sumber disalin setelah dependensi terpasang, lalu autoload dioptimalkan
# ulang agar kelas App\* ikut masuk classmap.
COPY . .

RUN composer dump-autoload --no-dev --optimize --no-scripts

# ---------------------------------------------------------------------------
# assets: aset Vite
# ---------------------------------------------------------------------------
FROM node:22-bookworm-slim AS assets

# Jangan unduh peramban untuk paket uji (e2e/playwright) saat npm ci.
ENV PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1 \
    PUPPETEER_SKIP_DOWNLOAD=1

WORKDIR /app

# package-lock.json sudah ada di repo, jadi npm ci dipakai agar versi
# dependensi sama persis dengan mesin pengembang.
COPY package.json package-lock.json ./

RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY app ./app
COPY public ./public

# resources/css/app.css memakai @source ke view pagination Laravel di vendor.
# Tanpa vendor di stage ini, kelas pagination hilang dari CSS hasil build.
COPY --from=vendor /app/vendor ./vendor

RUN npm run build

# ---------------------------------------------------------------------------
# runtime: aplikasi siap jalan
# ---------------------------------------------------------------------------
FROM base AS runtime

WORKDIR /var/www/html

# Konfigurasi lebih dulu supaya layer ini tidak ikut berubah setiap kode diedit.
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Normalisasi akhir baris (CRLF) dan izin eksekusi. Tanpa ini, container
# gagal start dengan "/bin/sh^M: bad interpreter" bila berkas tersimpan CRLF.
RUN chmod 755 /usr/local/bin/entrypoint.sh \
    && sed -i 's/\r$//' /usr/local/bin/entrypoint.sh

# Vendor disalin sebelum sumber; .dockerignore mengecualikan vendor sehingga
# COPY . . tidak menimpanya.
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Laravel menyediakan route kesehatan /up (lihat bootstrap/app.php).
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1/up') === false ? 1 : 0);"

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
