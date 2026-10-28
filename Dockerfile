
# --------------------------------------------------
# Dépendances PHP
# --------------------------------------------------
FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# --------------------------------------------------
# Assets frontend
# --------------------------------------------------
FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# --------------------------------------------------
# Application Laravel + PHP-FPM
# --------------------------------------------------
FROM php:8.3-fpm-bookworm AS app-runtime

ENV DB_CONNECTION=sqlite \
    DB_DATABASE=/var/lib/laravel/database.sqlite \
    PYTHONUNBUFFERED=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libonig-dev \
        libsqlite3-dev \
        libzip-dev \
        openssl \
        python3 \
        python3-pip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath exif gd mbstring pcntl pdo_sqlite zip

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

COPY requirements.txt /tmp/requirements.txt

RUN python3 -m pip install \
        --no-cache-dir \
        --break-system-packages \
        -r /tmp/requirements.txt

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/app/signature \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        /var/lib/laravel \
    && rm -rf public/storage \
    && ln -s ../storage/app/public public/storage \
    && chmod -R ug+rwX bootstrap/cache storage \
    && php artisan package:discover --ansi

EXPOSE 9000

# Appeler le script via sh évite de dépendre du bit exécutable
# du fichier copié depuis Windows.
ENTRYPOINT ["sh", "/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]

# --------------------------------------------------
# Serveur Nginx
# --------------------------------------------------
FROM nginx:stable-alpine AS nginx-runtime

COPY --from=app-runtime /var/www/html/public /var/www/html/public

RUN mkdir -p /var/www/html/storage/app/public

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
