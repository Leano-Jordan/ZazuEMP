#!/bin/sh
set -eu

cd /var/www/html

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"

# This is deliberately a disposable preview database, not production persistence.
export DB_CONNECTION=sqlite
export DB_DATABASE=/tmp/zazu-preview.sqlite

# File-backed sessions/cache keep the preview self-contained; all are ephemeral.
export SESSION_DRIVER=file
export CACHE_STORE=file
export QUEUE_CONNECTION=sync

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be configured in Render environment variables." >&2
    exit 1
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
touch "$DB_DATABASE"
chown -R www-data:www-data storage bootstrap/cache
php artisan config:clear
php artisan package:discover --ansi
php artisan migrate --force
# Apache serves requests as www-data; SQLite must be writable by that process.
chown www-data:www-data "$DB_DATABASE"
chmod 660 "$DB_DATABASE"
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
