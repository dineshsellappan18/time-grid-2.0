#!/bin/sh
set -e

echo "Caching config..."
cd /var/www/html
php artisan config:cache || true

echo "Caching routes..."
php artisan route:cache || true

echo "Caching views..."
php artisan view:cache || true

echo "Dropping all tables for fresh start..."
php -r "
require '/var/www/html/vendor/autoload.php';
\$app = require_once '/var/www/html/bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\$tables = DB::select(\"SELECT tablename FROM pg_tables WHERE schemaname = 'public'\");
DB::statement('SET session_replication_role = replica;');
foreach (\$tables as \$t) { DB::statement('DROP TABLE IF EXISTS public.' . \$t->tablename . ' CASCADE'); }
DB::statement('SET session_replication_role = DEFAULT;');
echo 'Dropped ' . count(\$tables) . ' tables.' . PHP_EOL;
" || echo "Table drop skipped"

echo "Running migrations..."
php artisan migrate --force || true

echo "Seeding database..."
php artisan db:seed --class='Database\Seeders\DatabaseSeeder' --force 2>/dev/null || echo "Seeding skipped (likely already seeded)"

echo "Deploy complete!"
