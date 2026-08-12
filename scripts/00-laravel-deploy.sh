#!/bin/sh
set -e

cd /var/www/html

echo "Caching config..."
php artisan config:cache || true

echo "Caching routes..."
php artisan route:cache || true

echo "Running migrations..."
php artisan migrate --force || true

echo "Deploy complete!"
