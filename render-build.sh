#!/bin/bash

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Clear and cache Laravel configs  
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache

echo "Build completed successfully!"