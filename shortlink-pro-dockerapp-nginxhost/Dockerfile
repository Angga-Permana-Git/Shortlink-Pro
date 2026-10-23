# syntax=docker/dockerfile:1.7
# =============================================================================
# Shortlink Pro - image produksi (Apache mod_php + MySQL)
# =============================================================================
# Konteks build WAJIB root repo karena sumber aplikasi ada di sana:
#   docker compose -f shortlink-pro-dockerapp-nginxhost/docker-compose.fulldb.yaml build
#
# Urutan stage dan alasannya:
#   base    : PHP + ekstensi + Apache (dipakai bersama stage lain)
#   vendor  : composer install HANYA dari composer.json/lock lebih dulu, supaya
#             layer ini tetap ter-cache saat kode berubah
#   assets  : npm ci + vite build; vendor ikut disalin karena app.css memakai
#             @source ke view pagination Laravel di dalam vendor
#   runtime : hasil akhir; tidak membawa node_modules, composer, atau tool build
#
# Jangan menurunkan PHP_VERSION di bawah 8.3.36 tanpa alasan kuat: versi itu
# adalah rilis pertama yang memuat patch PHP2Shell (CVE-2026-109618/109619).
# =============================================================================

ARG PHP_VERSION=8.3.36

# -----------------------------------------------------------------------------
# base - PHP, ekstensi, dan Apache
# -----------------------------------------------------------------------------
FROM php:${PHP_VERSION}-apache AS base

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
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------------------------------------------------
# vendor - dependensi PHP produksi
# -----------------------------------------------------------------------------
FROM base AS vendor

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Manifest dulu supaya cache composer tidak batal hanya karena kode berubah.
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

COPY . .

# Script composer sengaja tidak dijalankan di build: package:discover butuh
# filesystem tulis dan dijalankan saat container start.
RUN composer dump-autoload --no-dev --optimize --no-scripts

# -----------------------------------------------------------------------------
# assets - CSS/JS produksi
# -----------------------------------------------------------------------------
FROM node:22-bookworm-slim AS assets

ENV PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1 \
    PUPPETEER_SKIP_DOWNLOAD=1

WORKDIR /app

COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Tailwind memindai view pagination milik Laravel di dalam vendor.
COPY --from=vendor /app/vendor ./vendor

RUN npm run build

# -----------------------------------------------------------------------------
# runtime - citra akhir yang dijalankan
# -----------------------------------------------------------------------------
FROM base AS runtime

WORKDIR /var/www/html

# Konfigurasi Apache dan PHP khusus produksi.
COPY shortlink-pro-dockerapp-nginxhost/apache/shortlink-pro.conf /etc/apache2/sites-available/000-default.conf
COPY shortlink-pro-dockerapp-nginxhost/php.ini /usr/local/etc/php/conf.d/zz-shortlink-pro.ini
COPY shortlink-pro-dockerapp-nginxhost/entrypoint.sh /usr/local/bin/entrypoint.sh

# Port internal 9000 (tidak butuh hak istimewa), lalu matikan jejak versi.
# Root filesystem read-only: log global Apache harus diarahkan ke stderr dan
# conf CustomLog global dimatikan, kalau tidak Apache gagal membuka berkas log.
RUN sed -ri 's/^Listen 80$/Listen 9000/' /etc/apache2/ports.conf \
    && sed -ri 's#^ErrorLog .*#ErrorLog /proc/self/fd/2#' /etc/apache2/apache2.conf \
    && (a2disconf other-vhosts-access-log 2>/dev/null \
        || rm -f /etc/apache2/conf-enabled/other-vhosts-access-log.conf) \
    && printf '%s\n' \
        'ServerTokens Prod' \
        'ServerSignature Off' \
        'TraceEnable Off' \
        > /etc/apache2/conf-available/zz-shortlink-hardening.conf \
    && a2enconf zz-shortlink-hardening \
    && chmod 755 /usr/local/bin/entrypoint.sh \
    && sed -i 's/\r$//' /usr/local/bin/entrypoint.sh

# Vendor dari stage vendor, sumber aplikasi, lalu aset hasil build.
# .dockerignore memastikan vendor/, node_modules/, .env, dan public/build asli
# tidak ikut masuk dari konteks.
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

# Direktori runtime. public/storage adalah symlink relatif supaya tetap benar
# saat root filesystem dipasang read-only.
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && rm -rf public/storage \
    && ln -s ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1:9000/up') === false ? 1 : 0);"

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
