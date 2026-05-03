#!/bin/sh
set -eu

cd /app

# Warm caches for faster request handling in production.
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec frankenphp run --config /etc/caddy/Caddyfile
