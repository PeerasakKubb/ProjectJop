#!/usr/bin/env bash
# Do not use set -e — never block container start / health checks
cd /var/www/html || exit 0

echo "==> Ensure APP_KEY"
if [[ -z "${APP_KEY:-}" || "${APP_KEY}" != base64:* ]]; then
  php artisan key:generate --force || true
fi

echo "==> Storage link"
php artisan storage:link 2>/dev/null || true

echo "==> Cache"
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

echo "==> Migrate"
php artisan migrate --force 2>/dev/null || echo "WARN: migrate failed"

echo "==> Laravel post-start done"
