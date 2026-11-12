#!/bin/sh
set -eu

cd /var/www/html

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"

# Accept Laravel's DB_URL and Render's conventional DATABASE_URL. An explicit
# DB_CONNECTION always wins; otherwise infer PostgreSQL/MySQL from the URL scheme.
if [ -z "${DB_URL:-}" ] && [ -n "${DATABASE_URL:-}" ]; then
    export DB_URL="$DATABASE_URL"
fi

if [ -z "${DB_CONNECTION:-}" ]; then
    case "${DB_URL:-}" in
        postgres://*|postgresql://*)
            export DB_CONNECTION="pgsql"
            ;;
        mysql://*)
            export DB_CONNECTION="mysql"
            ;;
        sqlite:*)
            export DB_CONNECTION="sqlite"
            ;;
        "")
            export DB_CONNECTION="sqlite"
            ;;
        *)
            echo "Cannot infer DB_CONNECTION from DB_URL. Set DB_CONNECTION explicitly." >&2
            exit 1
            ;;
    esac
else
    export DB_CONNECTION
fi

# Keep the lightweight preview default, but never silently fall back to SQLite
# when a persistent database connection has been selected.
if [ "$DB_CONNECTION" = "sqlite" ]; then
    export DB_DATABASE="${DB_DATABASE:-/tmp/zazu-preview.sqlite}"
    touch "$DB_DATABASE"
elif [ -z "${DB_URL:-}" ] && [ -z "${DB_HOST:-}" ]; then
    echo "DB_URL, DATABASE_URL or DB_HOST must be configured when DB_CONNECTION is not sqlite." >&2
    exit 1
fi

if [ "$DB_CONNECTION" = "pgsql" ] && ! php -r 'exit(extension_loaded("pdo_pgsql") ? 0 : 1);'; then
    echo "PostgreSQL was selected but the pdo_pgsql PHP extension is unavailable." >&2
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
