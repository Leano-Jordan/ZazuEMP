#!/bin/sh
set -eu
cd /var/www/html

: "${APP_ENV:=production}"
export APP_ENV
: "${APP_DEBUG:=false}"
export APP_DEBUG
: "${LOG_CHANNEL:=stderr}"
export LOG_CHANNEL
: "${DB_CONNECTION:=pgsql}"
export DB_CONNECTION
: "${SESSION_DRIVER:=database}"
export SESSION_DRIVER
: "${CACHE_STORE:=database}"
export CACHE_STORE
: "${QUEUE_CONNECTION:=database}"
export QUEUE_CONNECTION

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY must be set as a persistent Render environment variable." >&2
  exit 1
fi

php artisan config:clear
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

exec apache2-foreground
