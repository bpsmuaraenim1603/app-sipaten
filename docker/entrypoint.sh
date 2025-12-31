#!/usr/bin/env bash
set -e

cd /var/www/html

# Kalau .env belum ada, copy dari example
if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

# Generate key kalau belum ada (aman buat run berulang)
php artisan key:generate --force || true

# Pastikan cache folder ada
mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache

# Optional: kalau mau otomatis migrate setiap start (biasanya iya untuk lokal)
php artisan migrate --force || true

exec "$@"

php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true