#!/usr/bin/env sh
cd /var/www/html

echo "==> Laravel post-start"

if [ -z "${APP_KEY:-}" ] || ! echo "$APP_KEY" | grep -q '^base64:'; then
  echo "==> Generate APP_KEY"
  php artisan key:generate --force || true
fi

php artisan storage:link 2>/dev/null || true

echo "==> Clear stale caches"
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo "==> Wait for database"
attempt=0
until php artisan db:show 2>/dev/null; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge 30 ]; then
    echo "ERROR: database not reachable after 30 attempts"
    php artisan db:show || true
    break
  fi
  echo "  waiting for database... ($attempt/30)"
  sleep 2
done

echo "==> Migrate database"
if ! php artisan migrate --force; then
  echo "ERROR: migrate failed"
fi

echo "==> Seed database"
php artisan db:seed --force || echo "WARN: seed skipped or partial"

echo "==> Cache for production"
php artisan config:cache || echo "WARN: config:cache failed"
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

echo "==> Laravel post-start done"
