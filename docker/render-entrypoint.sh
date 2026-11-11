#!/bin/sh
set -eu

cd /var/www/html

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"

# Keep the lightweight preview default, but honour an explicitly configured
# persistent database for a real staging/pilot deployment.
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
if [ "$DB_CONNECTION" = "sqlite" ]; then
    export DB_DATABASE="${DB_DATABASE:-/tmp/zazu-preview.sqlite}"
    touch "$DB_DATABASE"
elif [ -z "${DB_URL:-}" ] && [ -z "${DB_HOST:-}" ]; then
    echo "DB_URL or DB_HOST must be configured when DB_CONNECTION is not sqlite." >&2
    exit 1
fi

# Preserve deployment overrides. The preview remains file-backed/synchronous
# by default; a persistent storage mount can retain sessions and private files.
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export CACHE_STORE="${CACHE_STORE:-file}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be configured in Render environment variables." >&2
    exit 1
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
php artisan config:clear
php artisan package:discover --ansi
php artisan migrate --force
if [ "$DB_CONNECTION" = "sqlite" ]; then
    # Apache serves requests as www-data; SQLite must be writable by that process.
    chown www-data:www-data "$DB_DATABASE"
    chmod 660 "$DB_DATABASE"
fi
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
