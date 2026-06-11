#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════════
#  InteTeam Support — Production install script
#  Tested on: Ubuntu 22.04 LTS / Ubuntu 24.04 LTS
#
#  Usage:
#    sudo bash install.sh --domain=support.inte.team --ssl=npm --port=8094
#
#  Options:
#    --domain=<domain>    Public domain (required for production)
#    --ssl=caddy|npm      caddy = auto Let's Encrypt, npm = Nginx Proxy Manager (default)
#    --port=<port>        Host port for Nginx (default: 8094)
#    --repo=<git-url>     Git URL to clone (if not already in the repo dir)
#    --branch=main        Branch to deploy (default: main)
#    --dir=<path>         Clone destination (default: /opt/inteteam_support)
#    --fresh              migrate:fresh --seed ⚠ DESTROYS existing data
#    --help               Show this help
# ═══════════════════════════════════════════════════════════════════
set -euo pipefail
IFS=$'\n\t'

# ── Colours ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

step() { echo -e "\n${BOLD}${BLUE}── Step ${1} ──${RESET}  ${BOLD}${2}${RESET}"; }
ok()   { echo -e "  ${GREEN}✓${RESET}  ${1}"; }
warn() { echo -e "  ${YELLOW}⚠${RESET}  ${1}"; }
err()  { echo -e "  ${RED}✗${RESET}  ${1}" >&2; }
info() { echo -e "  ${CYAN}→${RESET}  ${1}"; }
die()  { err "$1"; exit 1; }

# ── Defaults ─────────────────────────────────────────────────────────────────
DOMAIN=""
SSL_METHOD="npm"
PORT="8094"
REPO_URL=""
BRANCH="main"
DEPLOY_DIR="/opt/inteteam_support"
FRESH=false

for arg in "$@"; do
  case $arg in
    --domain=*)  DOMAIN="${arg#*=}" ;;
    --ssl=*)     SSL_METHOD="${arg#*=}" ;;
    --port=*)    PORT="${arg#*=}" ;;
    --repo=*)    REPO_URL="${arg#*=}" ;;
    --branch=*)  BRANCH="${arg#*=}" ;;
    --dir=*)     DEPLOY_DIR="${arg#*=}" ;;
    --fresh)     FRESH=true ;;
    --help|-h)   grep '^#  ' "$0" | sed 's/^#  //'; exit 0 ;;
    *)           warn "Unknown argument ignored: $arg" ;;
  esac
done

[[ $EUID -eq 0 ]] || die "Run as root: sudo bash install.sh [options]"

# Derive DEPLOY_DIR dynamically from script location if running from inside the repo
if [[ -f "$(dirname "${BASH_SOURCE[0]}")/artisan" ]]; then
  DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
fi

echo -e "${BOLD}${CYAN}"
echo "╔══════════════════════════════════════════════╗"
echo "║   InteTeam Support  —  Install Script        ║"
echo "╚══════════════════════════════════════════════╝"
echo -e "${RESET}"
[[ -n "$DOMAIN" ]] && info "Domain : $DOMAIN" || warn "No domain — dev/local mode"
info "Port   : $PORT"
info "SSL    : $SSL_METHOD"
info "Dir    : $DEPLOY_DIR"

# ── 1. System packages & Docker ───────────────────────────────────────────────
step "1" "System packages & Docker"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq git curl wget vim ufw fail2ban

if ! command -v docker &>/dev/null; then
  curl -fsSL https://get.docker.com | sh
  systemctl enable --now docker
fi

if ! docker compose version &>/dev/null; then
  apt-get install -y -qq docker-compose-plugin
fi

mkdir -p /etc/docker
if ! grep -q '"max-size"' /etc/docker/daemon.json 2>/dev/null; then
  cat > /etc/docker/daemon.json <<'JSON'
{ "log-driver": "json-file", "log-opts": { "max-size": "10m", "max-file": "3" }, "storage-driver": "overlay2" }
JSON
  systemctl restart docker
fi
ok "Docker ready: $(docker --version)"

# ── 2. Firewall ───────────────────────────────────────────────────────────────
step "2" "Firewall"
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
systemctl enable --now fail2ban
ok "UFW + fail2ban active"

# ── 3. Repository ─────────────────────────────────────────────────────────────
step "3" "Repository"
if [[ -f "$DEPLOY_DIR/artisan" ]]; then
  cd "$DEPLOY_DIR"
  git pull --ff-only origin "$BRANCH" 2>/dev/null || warn "git pull skipped"
  ok "Repo up to date: $DEPLOY_DIR"
elif [[ -n "$REPO_URL" ]]; then
  git clone --branch "$BRANCH" "$REPO_URL" "$DEPLOY_DIR"
  cd "$DEPLOY_DIR"
  ok "Cloned to $DEPLOY_DIR"
else
  die "No repo at $DEPLOY_DIR and no --repo=<url> provided."
fi

# ── 4. Storage permissions ────────────────────────────────────────────────────
step "4" "Storage permissions (UID 1000 = www in container)"
mkdir -p storage/app/public storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache
chown -R 1000:1000 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
ok "Permissions set"

# ── 5. Environment (.env) ─────────────────────────────────────────────────────
step "5" "Environment"
ENV_FILE="$DEPLOY_DIR/.env"

[[ -f "$ENV_FILE" ]] || cp .env.example "$ENV_FILE"

append_if_missing() {
  local key="$1" val="$2"
  if grep -q "^${key}=" "$ENV_FILE" 2>/dev/null; then
    sed -i "s|^${key}=.*|${key}=${val}|" "$ENV_FILE"
  else
    echo "${key}=${val}" >> "$ENV_FILE"
  fi
}
randpass() { openssl rand -base64 48 | tr -dc 'A-Za-z0-9' | head -c 32; }

append_if_missing "APP_NAME"  "InteTeam Support"
append_if_missing "DB_CONNECTION" "pgsql"
append_if_missing "DB_HOST"       "postgres"
append_if_missing "DB_PORT"       "5432"
append_if_missing "DB_DATABASE"   "inteteam_support"
append_if_missing "DB_USERNAME"   "support"

_DB_PASS="$(grep "^DB_PASSWORD=" "$ENV_FILE" | cut -d= -f2 || true)"
if [[ -z "$_DB_PASS" || "$_DB_PASS" == "SupportDevPass" ]]; then
  _DB_PASS="$(randpass)"
  append_if_missing "DB_PASSWORD" "$_DB_PASS"
fi

append_if_missing "REDIS_CLIENT" "phpredis"
append_if_missing "REDIS_HOST"   "redis"
append_if_missing "REDIS_PORT"   "6379"
_REDIS_PASS="$(grep "^REDIS_PASSWORD=" "$ENV_FILE" | cut -d= -f2 || true)"
if [[ -z "$_REDIS_PASS" || "$_REDIS_PASS" == "RedisDevPass" ]]; then
  append_if_missing "REDIS_PASSWORD" "$(randpass)"
fi

append_if_missing "SESSION_DRIVER"   "redis"
append_if_missing "CACHE_STORE"      "redis"
append_if_missing "QUEUE_CONNECTION" "redis"

_PANEL_TOKEN="$(grep "^PANEL_TOKEN=" "$ENV_FILE" | cut -d= -f2 || true)"
if [[ -z "$_PANEL_TOKEN" ]]; then
  _PANEL_TOKEN="$(randpass)"
  append_if_missing "PANEL_TOKEN" "$_PANEL_TOKEN"
fi

append_if_missing "PORT" "$PORT"

if [[ -n "$DOMAIN" ]]; then
  append_if_missing "APP_ENV"   "production"
  append_if_missing "APP_DEBUG" "false"
  append_if_missing "APP_URL"   "https://${DOMAIN}"
  append_if_missing "LOG_LEVEL" "warning"
fi

# Scaffold optional keys so they never go missing between deploys
append_if_missing "SSO_ENABLED"      "false"
append_if_missing "SSO_URL"          ""
append_if_missing "SSO_INTERNAL_URL" ""
append_if_missing "SSO_CLIENT_ID"    ""
append_if_missing "SSO_CLIENT_SECRET" ""
append_if_missing "SSO_REDIRECT_URI" "${APP_URL:-https://${DOMAIN}}/auth/sso/callback"
append_if_missing "MAIL_MAILER"      "log"
append_if_missing "MAIL_PORT"        "465"
append_if_missing "MAIL_ENCRYPTION"  "ssl"

chown 1000:1000 "$ENV_FILE"
chmod 664 "$ENV_FILE"
ok ".env configured"

# ── 6. Docker overlay ─────────────────────────────────────────────────────────
step "6" "Docker Compose overlay"
cat > docker-compose.prod.yml <<YAML
# AUTO-GENERATED by install.sh — do not edit manually.
services:
  nginx:
    ports:
      - "${PORT}:80"
  postgres:
    environment:
      POSTGRES_PASSWORD: $( grep "^DB_PASSWORD=" "$ENV_FILE" | cut -d= -f2 )
  redis:
    command: redis-server --appendonly yes --requirepass $( grep "^REDIS_PASSWORD=" "$ENV_FILE" | cut -d= -f2 )
YAML
ok "docker-compose.prod.yml written"

# ── 7. proxy-tier network ─────────────────────────────────────────────────────
step "7" "Docker proxy-tier network"
docker network inspect proxy-tier &>/dev/null || docker network create proxy-tier
ok "proxy-tier network ready"

# ── 8. Build & start ──────────────────────────────────────────────────────────
step "8" "Build images & start containers"
DC=(docker compose -f docker-compose.yml -f docker-compose.prod.yml)

if [[ "$SSL_METHOD" == "caddy" && -n "$DOMAIN" ]]; then
  "${DC[@]}" --profile prod up -d --build
else
  "${DC[@]}" up -d --build
fi

# Wait for PostgreSQL
info "Waiting for PostgreSQL…"
for i in $(seq 1 30); do
  if "${DC[@]}" exec -T postgres pg_isready -U support -d inteteam_support &>/dev/null; then
    ok "PostgreSQL ready"; break
  fi
  sleep 2
  [[ $i -eq 30 ]] && die "PostgreSQL not ready after 60s"
done

# ── 9. Post-start fixes ───────────────────────────────────────────────────────
step "9" "Container post-start fixes"
"${DC[@]}" exec -T -u root php-fpm sh -c \
  "mkdir -p /home/www/.config && chown -R 1000:1000 /home/www/.config storage bootstrap/cache" 2>/dev/null || true
ok "php-fpm permissions set"

# ── 10. Laravel bootstrap ─────────────────────────────────────────────────────
step "10" "Laravel bootstrap"
"${DC[@]}" exec -T php-fpm composer install --no-dev --optimize-autoloader

_APP_KEY="$(grep "^APP_KEY=" "$ENV_FILE" | cut -d= -f2 || true)"
[[ -z "$_APP_KEY" ]] && "${DC[@]}" exec -T php-fpm php artisan key:generate --force

if [[ "$FRESH" == "true" ]]; then
  warn "Running migrate:fresh --seed (data will be wiped)…"
  "${DC[@]}" exec -T php-fpm php artisan migrate:fresh --seed --force
else
  "${DC[@]}" exec -T php-fpm php artisan migrate --force
fi

"${DC[@]}" exec -T php-fpm php artisan storage:link 2>/dev/null || true

if [[ -n "$DOMAIN" ]]; then
  "${DC[@]}" exec -T php-fpm php artisan config:cache
  "${DC[@]}" exec -T php-fpm php artisan route:cache
  "${DC[@]}" exec -T php-fpm php artisan view:cache
fi
ok "Laravel bootstrapped"

# ── 11. Frontend build ────────────────────────────────────────────────────────
step "11" "Frontend assets"
docker run --rm \
  --env-file "$ENV_FILE" \
  -v "$(pwd)":/var/www \
  -w /var/www \
  node:22-alpine \
  sh -c "npm ci --silent && npm run build"

rm -f public/hot

[[ -f "public/build/manifest.json" ]] \
  || die "Frontend build FAILED — public/build/manifest.json not found"
ok "Frontend built"

# ── 12. Health check ──────────────────────────────────────────────────────────
step "12" "Health check"
sleep 3
_HTTP="$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:${PORT}/" 2>/dev/null || echo "000")"
if [[ "$_HTTP" == "200" || "$_HTTP" == "302" ]]; then
  ok "App responding (HTTP $_HTTP)"
else
  warn "App returned HTTP $_HTTP — check logs: ${DC[*]} logs nginx && ${DC[*]} logs php-fpm"
fi

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}════════════════════════════════════════════════${RESET}"
echo -e "${BOLD}${GREEN}  InteTeam Support installed!${RESET}"
echo -e "${BOLD}${GREEN}════════════════════════════════════════════════${RESET}"
echo ""
echo -e "  ${BOLD}App URL  :${RESET}  ${DOMAIN:+https://$DOMAIN}${DOMAIN:-http://localhost:$PORT}"
echo ""
echo -e "${BOLD}${YELLOW}  Generated secrets — configure these in Panel .env editor:${RESET}"
echo -e "  PANEL_TOKEN = $( grep "^PANEL_TOKEN=" "$ENV_FILE" | cut -d= -f2 )"
echo -e "  (set this as SUPPORT_PANEL_TOKEN in inteteam-panel .env)"
echo ""
echo -e "${BOLD}${YELLOW}  Still required — set via Panel .env editor:${RESET}"
echo -e "  SSO_CLIENT_ID     — from SSO admin panel /admin/clients"
echo -e "  SSO_CLIENT_SECRET — from SSO admin panel /admin/clients"
echo -e "  SSO_URL           — public SSO URL"
echo -e "  SSO_INTERNAL_URL  — SSO URL reachable from container (WireGuard IP)"
echo -e "  SSO_ENABLED       — set to true after SSO client is configured"
echo ""
