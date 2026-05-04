#!/usr/bin/env bash
set -euo pipefail

cd /app

if [ -d /app/public-source ]; then
    cp -a /app/public-source/. /app/public/
fi

if [ -d /app/storage-source/fonts ] && [ ! -d /app/storage/fonts ]; then
    mkdir -p /app/storage
    cp -a /app/storage-source/fonts /app/storage/fonts
fi

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache || true
chown www-data:www-data public || true

if [ ! -f .env ] && [ -f .env.production ]; then
    cp .env.production .env
fi

php artisan storage:link --force || true
php artisan package:discover --ansi || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
