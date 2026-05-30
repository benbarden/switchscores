#!/bin/bash

# Post-deployment script for Switch Scores
# Run this after pulling new code to the server

set -e  # Exit on any error

SITE_DIR="/var/www/switchscores.com"

echo "=== Post-deploy: Starting ==="

cd "$SITE_DIR"

echo "=== Installing composer dependencies ==="
composer install --no-dev --optimize-autoloader

echo "=== Clearing Laravel caches ==="
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "=== Clearing Twig cache ==="
sudo rm -rf storage/framework/views/twig/*

echo "=== Running migrations ==="
php artisan migrate --force

echo "=== Restarting queue workers ==="
sudo supervisorctl restart all

echo "=== Post-deploy: Complete ==="
