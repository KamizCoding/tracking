#!/bin/bash

# Create .env file if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    touch /var/www/html/.env
fi

# Write environment variables to .env file
echo "APP_ENV=${APP_ENV:-production}" >> /var/www/html/.env
echo "APP_DEBUG=${APP_DEBUG:-false}" >> /var/www/html/.env
echo "APP_URL=${APP_URL:-https://vellix-tracking.onrender.com}" >> /var/www/html/.env
echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
echo "DB_CONNECTION=${DB_CONNECTION:-pgsql}" >> /var/www/html/.env
echo "DB_HOST=${DB_HOST}" >> /var/www/html/.env
echo "DB_PORT=${DB_PORT:-5432}" >> /var/www/html/.env
echo "DB_DATABASE=${DB_DATABASE}" >> /var/www/html/.env
echo "DB_USERNAME=${DB_USERNAME}" >> /var/www/html/.env
echo "DB_PASSWORD=${DB_PASSWORD}" >> /var/www/html/.env
echo "TRACKING_DOMAIN=${TRACKING_DOMAIN:-vellix-tracking.onrender.com}" >> /var/www/html/.env
echo "CACHE_DRIVER=${CACHE_DRIVER:-file}" >> /var/www/html/.env
echo "SESSION_DRIVER=${SESSION_DRIVER:-file}" >> /var/www/html/.env
echo "QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}" >> /var/www/html/.env

# Create storage directories if they don't exist
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --ansi --force
fi

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
