#!/bin/bash
set -e

# Create database file if not exists
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    chown www-data:www-data database/database.sqlite
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf

