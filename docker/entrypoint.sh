
#!/bin/sh
set -eu

cd /var/www/html

# Dossiers nécessaires à Laravel
mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/app/signature \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

# Préparer la base SQLite et son dossier
DB_FILE="${DB_DATABASE:-/var/lib/laravel/database.sqlite}"
DB_DIR="$(dirname "$DB_FILE")"

mkdir -p "$DB_DIR"

if [ ! -f "$DB_FILE" ]; then
    touch "$DB_FILE"
fi

if [ "${APP_ENV:-production}" = "local" ]; then
    # Développement local : les dossiers peuvent être des montages
    # Windows dont les propriétaires Linux ne sont pas modifiables.
    chmod -R a+rwX bootstrap/cache storage "$DB_DIR" 2>/dev/null || true
    chmod 666 "$DB_FILE" 2>/dev/null || true
else
    # Production Linux : donner les droits au compte PHP-FPM.
    chown -R www-data:www-data \
        bootstrap/cache \
        storage \
        "$DB_DIR"

    chmod -R u+rwX,g+rwX \
        bootstrap/cache \
        storage \
        "$DB_DIR"

    chmod 664 "$DB_FILE"
fi

# Tinker/PsySH doit disposer d'un dossier de configuration accessible.
mkdir -p /tmp/psysh
chown www-data:www-data /tmp/psysh
chmod 700 /tmp/psysh

export XDG_CONFIG_HOME=/tmp

exec "$@"
