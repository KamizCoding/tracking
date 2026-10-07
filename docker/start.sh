#!/bin/bash

# Create .env file with environment variables (use printf for better handling of special chars)
printf "APP_ENV=%s\n" "${APP_ENV:-production}" > /var/www/html/.env
printf "APP_DEBUG=%s\n" "${APP_DEBUG:-false}" >> /var/www/html/.env
printf "APP_URL=%s\n" "${APP_URL:-https://vellix-tracking.onrender.com}" >> /var/www/html/.env
printf "APP_KEY=%s\n" "${APP_KEY}" >> /var/www/html/.env
printf "DB_CONNECTION=%s\n" "${DB_CONNECTION:-pgsql}" >> /var/www/html/.env
printf "DB_HOST=%s\n" "${DB_HOST}" >> /var/www/html/.env
printf "DB_PORT=%s\n" "${DB_PORT:-5432}" >> /var/www/html/.env
printf "DB_DATABASE=%s\n" "${DB_DATABASE}" >> /var/www/html/.env
printf "DB_USERNAME=%s\n" "${DB_USERNAME}" >> /var/www/html/.env
printf "DB_PASSWORD=%s\n" "${DB_PASSWORD}" >> /var/www/html/.env
printf "TRACKING_DOMAIN=%s\n" "${TRACKING_DOMAIN:-vellix-tracking.onrender.com}" >> /var/www/html/.env
printf "CACHE_DRIVER=%s\n" "${CACHE_DRIVER:-file}" >> /var/www/html/.env
printf "SESSION_DRIVER=%s\n" "${SESSION_DRIVER:-file}" >> /var/www/html/.env
printf "QUEUE_CONNECTION=%s\n" "${QUEUE_CONNECTION:-sync}" >> /var/www/html/.env

# Download GeoLite2 database if not present (optional - for GeoIP)
# GeoIP is optional - app will work without it
if [ ! -f /var/www/html/database/GeoLite2-City.mmdb ]; then
    echo "GeoLite2 database not found. GeoIP will use IP fallback."
    mkdir -p /var/www/html/database
    # Create empty file to prevent errors
    touch /var/www/html/database/GeoLite2-City.mmdb
fi

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
