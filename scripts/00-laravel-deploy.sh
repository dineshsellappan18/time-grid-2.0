#!/usr/bin/env bash
set -e

echo "Running composer..."
composer install --no-dev --working-dir=/var/www/html

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Caching views..."
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force

echo "Seeding database (first deploy only)..."
php artisan db:seed --class='Database\Seeders\DatabaseSeeder' --force 2>/dev/null || echo "Seeding skipped (likely already seeded)"

echo "Deploy complete!"
