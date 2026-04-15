#!/bin/bash
# =============================================================
# Games4Cash — One-Time Server Setup Script
# Run this once on a fresh Ubuntu 22.04 VPS as root:
#   bash server-setup.sh yourdomain.com
# =============================================================
set -e

DOMAIN=${1:-"yourdomain.com"}
APP_DIR="/var/www/games4cash"
REPO="https://github.com/Thomas-Thackeray/Games4Cash.git"

echo "==> Installing system packages..."
apt update && apt upgrade -y
apt install -y nginx git curl unzip sqlite3 \
    php8.2 php8.2-fpm php8.2-cli php8.2-sqlite3 \
    php8.2-mbstring php8.2-xml php8.2-zip php8.2-curl \
    php8.2-bcmath php8.2-intl

echo "==> Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

echo "==> Cloning repository..."
mkdir -p /var/www
git clone "$REPO" "$APP_DIR"
cd "$APP_DIR"

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Creating .env file..."
cp .env.example .env
php artisan key:generate

echo "==> Creating SQLite database..."
touch database/database.sqlite
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR/storage"
chmod -R 755 "$APP_DIR/bootstrap/cache"
chmod 664 "$APP_DIR/database/database.sqlite"

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Configuring Nginx..."
cat > /etc/nginx/sites-available/games4cash << NGINX
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root $APP_DIR/public;

    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 10M;
}
NGINX

ln -sf /etc/nginx/sites-available/games4cash /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo "==> Installing Certbot for SSL..."
apt install -y certbot python3-certbot-nginx
echo ""
echo "========================================================"
echo "✓ Server setup complete!"
echo ""
echo "NEXT STEPS:"
echo "1. Edit your .env file:  nano $APP_DIR/.env"
echo "2. Set APP_URL, APP_DEBUG=false, mail settings"
echo "3. Run SSL:  certbot --nginx -d $DOMAIN -d www.$DOMAIN"
echo "4. Set up daily DB backup (see README)"
echo "========================================================"
