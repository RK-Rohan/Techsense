#!/bin/sh
set -eu

cd /app

# Warm caches for faster request handling in production.
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

if [ "${RUNTIME_PATCH_MAINTENANCE:-false}" = "true" ]; then
    php artisan down
fi

exec frankenphp run --config /etc/caddy/Caddyfile
