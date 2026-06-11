#!/usr/bin/env bash
# Called by inteteam-panel after every git pull deploy.
# MUST start with cd to SCRIPT_DIR — Panel invokes this from a different working directory.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")"

# ── Helpers ───────────────────────────────────────────────────────────────────
ENV_FILE=".env"
append_if_missing() {
  local key="$1" val="$2"
  if grep -q "^${key}=" "$ENV_FILE" 2>/dev/null; then
    return 0
  fi
  echo "${key}=${val}" >> "$ENV_FILE"
  echo "[post-deploy] Added missing key: ${key}"
}

DC=(docker compose -f docker-compose.yml -f docker-compose.prod.yml)

echo "[post-deploy] Starting inteteam-support post-deploy hooks…"

# ── Scaffold any env keys that may be missing ─────────────────────────────────
append_if_missing "SSO_ENABLED"       "false"
append_if_missing "SSO_URL"           ""
append_if_missing "SSO_INTERNAL_URL"  ""
append_if_missing "SSO_CLIENT_ID"     ""
append_if_missing "SSO_CLIENT_SECRET" ""
append_if_missing "SSO_REDIRECT_URI"  ""
append_if_missing "PANEL_TOKEN"            ""
append_if_missing "MAIL_MAILER"           "log"
append_if_missing "MAIL_PORT"         "465"
append_if_missing "MAIL_ENCRYPTION"   "ssl"
append_if_missing "PORT"                  "8094"
append_if_missing "BROADCAST_CONNECTION" "reverb"
append_if_missing "REVERB_APP_ID"        "support"
append_if_missing "REVERB_APP_KEY"       ""
append_if_missing "REVERB_APP_SECRET"    ""
append_if_missing "REVERB_HOST"          "localhost"
append_if_missing "REVERB_PORT"          "8080"
append_if_missing "REVERB_SCHEME"        "http"
append_if_missing "RAG_URL"                   ""
append_if_missing "REMOTE_JWT_SECRET"         ""
append_if_missing "REMOTE_SIGNALING_URL"      "http://remote_signaling:8090"
append_if_missing "REMOTE_SIGNALING_WS_URL"   "wss://remote.inte.team"

# ── PHP dependencies ──────────────────────────────────────────────────────────
echo "[post-deploy] composer install…"
"${DC[@]}" exec -T php-fpm composer install --no-dev --optimize-autoloader

# ── Migrations ────────────────────────────────────────────────────────────────
echo "[post-deploy] php artisan migrate…"
"${DC[@]}" exec -T php-fpm php artisan migrate --force

# ── Clear caches ──────────────────────────────────────────────────────────────
echo "[post-deploy] clearing caches…"
"${DC[@]}" exec -T php-fpm php artisan optimize:clear

# ── Re-warm production caches ─────────────────────────────────────────────────
echo "[post-deploy] warming caches…"
"${DC[@]}" exec -T php-fpm php artisan config:cache
"${DC[@]}" exec -T php-fpm php artisan route:cache
"${DC[@]}" exec -T php-fpm php artisan view:cache

# ── Rebuild frontend ──────────────────────────────────────────────────────────
echo "[post-deploy] building frontend…"
docker run --rm \
  --env-file "$ENV_FILE" \
  -v "$(pwd)":/var/www \
  -w /var/www \
  node:22-alpine \
  sh -c "npm ci --silent && npm run build"

rm -f public/hot

# ── Restart queue worker ──────────────────────────────────────────────────────
echo "[post-deploy] restarting queue worker…"
"${DC[@]}" exec -T php-fpm php artisan queue:restart
"${DC[@]}" restart queue-worker

echo "[post-deploy] restarting reverb…"
"${DC[@]}" restart reverb

echo "[post-deploy] Done."
