#!/bin/sh
set -e

# Copy .env.docker if .env does not exist
if [ ! -f /var/www/.env ]; then
    if [ -f /var/www/.env.docker ]; then
        echo "[SmartExam] Creating .env from .env.docker..."
        cp /var/www/.env.docker /var/www/.env
    elif [ -f /var/www/.env.example ]; then
        echo "[SmartExam] Creating .env from .env.example..."
        cp /var/www/.env.example /var/www/.env
        php artisan key:generate --force
    fi
fi

# Wait for MySQL database connection using PDO
echo "[SmartExam] Waiting for database connection ($DB_HOST:$DB_PORT)..."
until php -r "try { new PDO('mysql:host=' . (getenv('DB_HOST') ?: 'db') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME') ?: 'smartexam', getenv('DB_PASSWORD') ?: 'secret'); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
    echo "[SmartExam] Database is starting up, waiting 2 seconds..."
    sleep 2
done
echo "[SmartExam] Database connection established successfully!"

# Run migrations and setup if running web server (php-fpm)
if [ "$1" = "php-fpm" ]; then
    echo "[SmartExam] Preparing Laravel application..."
    php artisan storage:link --quiet || true
    php artisan optimize:clear || true
    php artisan migrate --force || true

    # Auto-seed questions if empty
    php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); if (\App\Models\Question::count() === 0) { exit(1); } else { exit(0); }" 2>/dev/null || php artisan db:seed --force || true

    echo "[SmartExam] Setup completed!"
fi

# Fix permissions for storage and cache directories
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

exec "$@"
