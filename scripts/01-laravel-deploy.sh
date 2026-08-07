#!/usr/bin/env sh
cd /var/www/html

echo "==> Laravel post-start"

echo "==> Ensure valid APP_KEY"
KEY_FILE="storage/app/render-app-key"
mkdir -p storage/app

export APP_KEY="$(php -r '
$keyFile = "storage/app/render-app-key";
$envKey = getenv("APP_KEY") ?: "";
$candidates = [];
if (is_readable($keyFile)) {
    $candidates[] = trim((string) file_get_contents($keyFile));
}
$candidates[] = $envKey;
$valid = null;
foreach ($candidates as $key) {
    $raw = str_starts_with($key, "base64:") ? base64_decode(substr($key, 7), true) : false;
    if ($raw !== false && in_array(strlen($raw), [16, 32], true)) {
        $valid = $key;
        break;
    }
}
if ($valid === null) {
    $valid = "base64:" . base64_encode(random_bytes(32));
}
file_put_contents($keyFile, $valid);
echo $valid;
')"

echo "APP_KEY ready"

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
php artisan db:seed --class=LightsDevicesSeeder --force || echo "WARN: lights seed skipped"

echo "==> Cache for production"
php artisan config:cache || echo "WARN: config:cache failed"
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

echo "==> Laravel post-start done"
