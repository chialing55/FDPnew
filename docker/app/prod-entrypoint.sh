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
    storage/app/public/content-images \
    storage/app/public/hero \
    storage/app/public/plot-cards \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/livewire-tmp \
    storage/logs \
    bootstrap/cache \
    public/FDPfiles/splist/photo

for image in public/images/plots/*_thumb.jpg; do
    [ -f "$image" ] || continue
    cp -n "$image" storage/app/public/plot-cards/
done

chown -R www-data:www-data storage bootstrap/cache || true
chown -R www-data:www-data public/FDPfiles/splist/photo || true
chmod -R 775 public/FDPfiles/splist/photo || true
chown www-data:www-data public || true

if [ ! -f .env ] && [ -f .env.production ]; then
    cp .env.production .env
fi

su -s /bin/sh www-data -c 'php artisan storage:link --force' || true
su -s /bin/sh www-data -c 'php artisan package:discover --ansi' || true
su -s /bin/sh www-data -c 'php artisan config:cache'
su -s /bin/sh www-data -c 'php artisan route:cache'
su -s /bin/sh www-data -c 'php artisan view:cache'

exec "$@"
