#!/bin/bash
# =============================================================
# Games4Cash — Production Deploy Script
# Run this on the server after initial setup, and for every
# future release: ./deploy.sh
# =============================================================
set -e

REPO="https://github.com/Thomas-Thackeray/Games4Cash.git"
APP_DIR="/var/www/games4cash"

echo "==> Pulling latest code from main..."
cd "$APP_DIR"
git pull origin main

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Clearing and re-caching config/routes/views..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Fixing storage permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo ""
echo "✓ Deployed successfully."
