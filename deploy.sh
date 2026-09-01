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

echo "==> Building frontend assets (Vite/Tailwind)..."
npm install --no-audit --no-fund
npm run build

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

echo "==> Reloading PHP-FPM so the new cached config is picked up (avoids stale OPcache)..."
systemctl reload php8.2-fpm || true

echo ""
echo "✓ Deployed successfully."
