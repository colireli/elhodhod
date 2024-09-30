#!/bin/bash

# Wait for MySQL to be ready
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    echo "Waiting for database connection..."
    sleep 2
done
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations
php artisan migrate --force

# Start Apache in the foreground
apache2-foreground
