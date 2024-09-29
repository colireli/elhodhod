#!/bin/bash

# Wait for MySQL to be ready
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    echo "Waiting for database connection..."
    sleep 2
done

# Run migrations
php artisan migrate --force

# Start Apache in the foreground
apache2-foreground
