#!/usr/bin/env bash
set -e

echo "Caching config..."
cd /var/www/html
php artisan config:cache || true

echo "Caching routes..."
php artisan route:cache || true

echo "Caching views..."
php artisan view:cache || true

echo "Running migrations..."
php artisan migrate --force || true

echo "Seeding database..."
php artisan db:seed --class='Database\Seeders\DatabaseSeeder' --force 2>/dev/null || echo "Seeding skipped (likely already seeded)"

echo "Deploy complete!"
