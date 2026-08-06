#!/usr/bin/env sh
set -e

OUTPUT="${1:-storage/backups/render-backup-$(date +%Y%m%d-%H%M%S).sql}"
URL="${RENDER_DB_URL:-${DB_URL:-${DATABASE_URL:-}}}"

if [ -z "$URL" ]; then
  echo "ERROR: set RENDER_DB_URL, DB_URL, or DATABASE_URL (External Database URL from Render)"
  exit 1
fi

mkdir -p "$(dirname "$OUTPUT")"

echo "==> Backup to $OUTPUT"
pg_dump "$URL" --no-owner --no-acl --clean --if-exists > "$OUTPUT"
echo "==> Done ($(wc -c < "$OUTPUT") bytes)"
