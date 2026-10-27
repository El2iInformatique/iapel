#!/bin/sh
set -eu

mkdir -p \
    storage/app/public \
    storage/app/signature \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

touch database/database.sqlite

rm -rf public/storage
ln -s ../storage/app/public public/storage

php artisan migrate --force
php artisan optimize:clear

exec "$@"
