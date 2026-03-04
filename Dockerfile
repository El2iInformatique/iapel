# syntax=docker/dockerfile:1.7

FROM node:20-bookworm-slim AS assets
WORKDIR /app

COPY package.json ./
RUN npm install

COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build


FROM php:8.2-cli-bookworm AS app

ENV APP_ENV=local \
    APP_DEBUG=true \
    APP_URL=http://localhost:8000 \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/database/database.sqlite \
    RUNTIME_SECRETS_DB_DATABASE=/var/lib/runtime-secrets/runtime_secrets.sqlite \
    SESSION_DRIVER=file \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    sqlite3 \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    python3 \
    python3-pip \
    python3-venv \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        bcmath \
        intl \
        gd \
        zip \
        exif \
        pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install \
    --no-interaction \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --no-progress \
    --optimize-autoloader \
    && composer dump-autoload --optimize --no-scripts \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views storage/app/public bootstrap/cache database /var/lib/runtime-secrets \
    && touch database/database.sqlite \
    && touch /var/lib/runtime-secrets/runtime_secrets.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database

VOLUME ["/var/www/html/storage/app/public", "/var/lib/runtime-secrets"]

EXPOSE 8000

CMD ["sh", "-lc", "if [ ! -f .env ]; then if [ -f .env.example ]; then cp .env.example .env; else printf 'APP_ENV=local\nAPP_DEBUG=true\nAPP_KEY=\n' > .env; fi; fi; if ! grep -q '^APP_KEY=base64:' .env; then php artisan key:generate --force --ansi; fi; mkdir -p $(dirname \"${RUNTIME_SECRETS_DB_DATABASE}\") && touch \"${RUNTIME_SECRETS_DB_DATABASE}\"; php artisan storage:link || true; php artisan migrate --force --no-interaction; php artisan serve --host=0.0.0.0 --port=8000"]
