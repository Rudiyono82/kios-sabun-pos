#!/bin/bash
set -e

# Railway dynamically injects $PORT
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Ensure permissions for writable
chown -R www-data:www-data /var/www/html/writable
chmod -R 775 /var/www/html/writable

exec apache2-foreground

