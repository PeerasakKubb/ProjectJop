#!/usr/bin/env bash
# Bind nginx to Render PORT and keep /up as static 200
PORT="${PORT:-10000}"
CONF="/etc/nginx/sites-available/default.conf"

if [[ -f "$CONF" ]]; then
  sed -i "s/listen [0-9]*;/listen ${PORT};/g" "$CONF"
  # Ensure static health endpoint exists even if conf was overwritten
  if ! grep -q "location = /up" "$CONF"; then
    sed -i "/server_name _;/a\\    location = /up { access_log off; default_type text/plain; return 200 'ok'; }" "$CONF"
  fi
  echo "==> Nginx listen=${PORT} health=/up(static)"
else
  echo "==> WARNING: $CONF missing"
fi
