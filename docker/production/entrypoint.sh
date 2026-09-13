#!/bin/sh
set -eu

# Initialize an empty storage volume
if [ ! -d /app/storage/framework ]; then
    echo "Initializing storage volume..."

    cp -a /app/storage-init/. /app/storage/

    chown -R www-data:www-data /app/storage
fi

mkdir -p \
    /app/storage/framework/cache/data \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs

chown -R www-data:www-data \
    /app/storage/framework \
    /app/storage/logs \
    /app/bootstrap/cache

# Run Laravel commands as www-data
gosu www-data php artisan optimize:clear
gosu www-data php artisan migrate --force
gosu www-data php artisan optimize

echo "Starting FrankenPHP..."

exec gosu www-data "$@"