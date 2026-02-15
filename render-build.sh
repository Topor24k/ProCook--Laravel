#!/bin/bash

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Clear config cache
php artisan config:clear

# Cache config for production
php artisan config:cache

# Clear route cache
php artisan route:clear

# Cache routes for production
php artisan route:cache

# Run database migrations
php artisan migrate --force

# Seed database if needed (optional)
# php artisan db:seed --force

echo "Build completed successfully!"