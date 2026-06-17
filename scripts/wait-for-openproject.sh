#!/usr/bin/env bash
#
# Waits for the throwaway OpenProject instance to finish booting. First boot
# seeds the database and takes minutes, during which the endpoint either
# refuses the connection or returns a 5xx from the front-end proxy. This polls
# GET /api/v3 until the Rails app itself answers (any 2xx-4xx status, e.g. the
# 401 returned before credentials are supplied), then exits 0.
#
# Usage (after `docker compose --profile openproject up -d`):
#   scripts/wait-for-openproject.sh
#
# Override the polled URL through the environment:
#   OPENPROJECT_URL=http://localhost:8080/api/v3 scripts/wait-for-openproject.sh
set -euo pipefail

url="${OPENPROJECT_URL:-http://localhost:8080/api/v3}"

while :; do
  code=$(curl -s -o /dev/null -w '%{http_code}' "$url" || true)
  code="${code:-000}"
  if [ "$code" -ge 200 ] && [ "$code" -lt 500 ]; then
    echo "OpenProject API is ready (HTTP $code) at $url"
    exit 0
  fi
  echo "Waiting for OpenProject API at $url (HTTP $code)..."
  sleep 10
done
