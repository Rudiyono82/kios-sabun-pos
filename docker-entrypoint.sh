#!/bin/bash
set -e

# Railway dynamically injects $PORT
PORT="${PORT:-80}"
echo "Listen ${PORT}" > /etc/apache2/ports.conf

# Remove any conflicting MPMs
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
a2enmod mpm_prefork 2>/dev/null || true

# Ensure permissions for writable
chown -R www-data:www-data /var/www/html/writable
chmod -R 775 /var/www/html/writable

exec apache2-foreground
