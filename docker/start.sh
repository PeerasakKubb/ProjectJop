#!/bin/sh
PORT="${PORT:-10000}"
sed -i "s/listen 10000;/listen ${PORT};/" /etc/nginx/http.d/default.conf

cd /var/www/html || exit 1

if [ -f scripts/01-laravel-deploy.sh ]; then
  sh scripts/01-laravel-deploy.sh || echo "WARN: Laravel deploy script failed"
fi

php-fpm -D
exec nginx -g 'daemon off;'
