#!/bin/bash

# Create .env file from environment variables if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
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

# Generate application key
php artisan key:generate --ansi --force

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
