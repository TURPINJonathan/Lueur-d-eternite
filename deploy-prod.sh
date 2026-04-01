#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACK_DIR="$SCRIPT_DIR/back"
FRONT_DIR="$SCRIPT_DIR/front"
ENV_FILE="${DEPLOY_ENV_FILE:-$SCRIPT_DIR/deploy-prod.env}"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

info()    { echo -e "  ${CYAN}→${NC} $*"; }
success() { echo -e "  ${GREEN}✓${NC} $*"; }
warn()    { echo -e "  ${YELLOW}!${NC} $*"; }
fatal()   { echo -e "\n  ${RED}${BOLD}✗ ERREUR :${NC} $*\n" >&2; exit 1; }
section() {
  echo ""
  echo -e "${BLUE}${BOLD}▶ $*${NC}"
  echo -e "${BLUE}  ─────────────────────────────────────────${NC}"
}

on_error() {
  local line="$1" code="$2"
  fatal "Échec du déploiement (ligne ${line}, code ${code})."
}
trap 'on_error $LINENO $?' ERR

require() {
  command -v "$1" >/dev/null 2>&1 || fatal "'$1' est requis mais introuvable."
}

gen_secret()  { openssl rand -hex 32; }
gen_b64_key() { openssl rand -base64 32; }

url_to_cors_regex() {
  local url="$1"
  local host
  host=$(printf '%s' "$url" | sed -E 's|https?://||' | sed -E 's|/.*||' | sed -E 's|:[0-9]+$||')
  local escaped
  escaped=$(printf '%s' "$host" | sed 's/\./\\./g')
  printf '%s' "^https?://${escaped}(:[0-9]+)?\$"
}

usage() {
  cat <<'EOF'
Usage:
  ./deploy-prod.sh [--env-file path]

Variables prises depuis le fichier env (ou l'environnement shell):
  APP_ENV, APP_DEBUG, BACK_URL, FRONT_URL, DATABASE_URL, JWT_PASSPHRASE
  (voir deploy-prod.env.example)
EOF
}

if [[ "${1:-}" == "--help" || "${1:-}" == "-h" ]]; then
  usage
  exit 0
fi

if [[ "${1:-}" == "--env-file" ]]; then
  [[ -n "${2:-}" ]] || fatal "Argument manquant pour --env-file."
  ENV_FILE="$2"
fi

[[ -d "$BACK_DIR" && -d "$FRONT_DIR" ]] || fatal "Lancez ce script depuis la racine du repo."

section "Chargement de la configuration"
if [[ -f "$ENV_FILE" ]]; then
  info "Fichier chargé : $ENV_FILE"
  set -a
  # shellcheck source=/dev/null
  source "$ENV_FILE"
  set +a
else
  warn "Fichier env introuvable: $ENV_FILE (variables shell utilisées uniquement)."
fi

APP_ENV="${APP_ENV:-prod}"
APP_DEBUG="${APP_DEBUG:-0}"
MAILER_DSN="${MAILER_DSN:-null://null}"
MESSENGER_TRANSPORT_DSN="${MESSENGER_TRANSPORT_DSN:-doctrine://default}"
APP_SECRET="${APP_SECRET:-$(gen_secret)}"
MEDIA_ENCRYPTION_KEY="${MEDIA_ENCRYPTION_KEY:-$(gen_b64_key)}"
DEFAULT_URI="${DEFAULT_URI:-${BACK_URL:-}}"
CORS_ALLOW_ORIGIN="${CORS_ALLOW_ORIGIN:-}"
CREATE_SUPER_ADMIN="${CREATE_SUPER_ADMIN:-0}"
ADMIN_FIRSTNAME="${ADMIN_FIRSTNAME:-}"
ADMIN_LASTNAME="${ADMIN_LASTNAME:-}"
ADMIN_EMAIL="${ADMIN_EMAIL:-}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-}"
FORCE_JWT_REGEN="${FORCE_JWT_REGEN:-0}"
RUN_HEALTHCHECKS="${RUN_HEALTHCHECKS:-1}"
SETUP_SYSTEMD="${SETUP_SYSTEMD:-0}"
SYSTEMD_FRONT_SERVICE_NAME="${SYSTEMD_FRONT_SERVICE_NAME:-lueur-front}"
SYSTEMD_MESSENGER_SERVICE_NAME="${SYSTEMD_MESSENGER_SERVICE_NAME:-lueur-messenger}"
DEPLOY_USER="${DEPLOY_USER:-$(id -un)}"

[[ "$APP_ENV" == "prod" || "$APP_ENV" == "staging" ]] || fatal "APP_ENV doit être prod ou staging."
[[ "$APP_DEBUG" == "0" || "$APP_DEBUG" == "1" ]] || fatal "APP_DEBUG doit être 0 ou 1."
[[ -n "${BACK_URL:-}" ]] || fatal "BACK_URL est obligatoire."
[[ -n "${FRONT_URL:-}" ]] || fatal "FRONT_URL est obligatoire."
[[ -n "${DATABASE_URL:-}" ]] || fatal "DATABASE_URL est obligatoire."
[[ -n "${JWT_PASSPHRASE:-}" ]] || fatal "JWT_PASSPHRASE est obligatoire."

if [[ -z "$CORS_ALLOW_ORIGIN" ]]; then
  CORS_ALLOW_ORIGIN="$(url_to_cors_regex "$FRONT_URL")"
fi

section "Prérequis"
for cmd in php composer node npm openssl; do
  require "$cmd"
  success "$cmd OK"
done

section "Version Node.js"
NODE_MAJOR="$(node --version | tr -d 'v' | cut -d. -f1)"
(( NODE_MAJOR >= 18 )) || fatal "Node.js 18+ requis."
success "Node.js $(node --version)"

section "Écriture des fichiers .env.local"
BACK_ENV="$BACK_DIR/.env.local"
cat > "$BACK_ENV" <<EOF
###> symfony/framework-bundle ###
APP_ENV=$APP_ENV
APP_DEBUG=$APP_DEBUG
APP_SECRET=$APP_SECRET
###< symfony/framework-bundle ###

###> symfony/routing ###
DEFAULT_URI=$DEFAULT_URI
###< symfony/routing ###

###> doctrine/doctrine-bundle ###
DATABASE_URL="$DATABASE_URL"
###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
MESSENGER_TRANSPORT_DSN=$MESSENGER_TRANSPORT_DSN
###< symfony/messenger ###

###> symfony/mailer ###
MAILER_DSN=$MAILER_DSN
###< symfony/mailer ###

###> nelmio/cors-bundle ###
CORS_ALLOW_ORIGIN='$CORS_ALLOW_ORIGIN'
###< nelmio/cors-bundle ###

###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=$JWT_PASSPHRASE
###< lexik/jwt-authentication-bundle ###

###> media ###
MEDIA_ENCRYPTION_KEY=$MEDIA_ENCRYPTION_KEY
###< media ###
EOF

FRONT_ENV="$FRONT_DIR/.env.local"
cat > "$FRONT_ENV" <<EOF
API_URL=$BACK_URL/api
NEXT_PUBLIC_API_URL=$BACK_URL/api
NEXT_PUBLIC_APP_URL=$FRONT_URL
NEXT_PUBLIC_APP_NAME=Elixir Linge
EOF

chmod 600 "$BACK_ENV" "$FRONT_ENV" || true
success "Fichiers d'environnement écrits."

section "Installation back (Symfony)"
cd "$BACK_DIR"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

JWT_DIR="$BACK_DIR/config/jwt"
mkdir -p "$JWT_DIR"

if [[ "$FORCE_JWT_REGEN" == "1" ]]; then
  rm -f "$JWT_DIR/private.pem" "$JWT_DIR/public.pem"
fi

if [[ ! -f "$JWT_DIR/private.pem" || ! -f "$JWT_DIR/public.pem" ]]; then
  info "Génération des clés JWT via OpenSSL..."
  openssl genpkey -algorithm RSA -out "$JWT_DIR/private.pem" -pkeyopt rsa_keygen_bits:4096 -aes256 -pass "pass:${JWT_PASSPHRASE}"
  openssl pkey -in "$JWT_DIR/private.pem" -out "$JWT_DIR/public.pem" -pubout -passin "pass:${JWT_PASSPHRASE}"
  chmod 600 "$JWT_DIR/private.pem"
  chmod 644 "$JWT_DIR/public.pem"
  success "Clés JWT générées."
else
  success "Clés JWT déjà présentes."
fi

APP_ENV="$APP_ENV" APP_DEBUG="$APP_DEBUG" php bin/console doctrine:database:create --if-not-exists --no-interaction
APP_ENV="$APP_ENV" APP_DEBUG="$APP_DEBUG" php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
APP_ENV="$APP_ENV" APP_DEBUG="$APP_DEBUG" php bin/console cache:clear --no-warmup
APP_ENV="$APP_ENV" APP_DEBUG="$APP_DEBUG" php bin/console cache:warmup
success "Back déployé."

if [[ "$CREATE_SUPER_ADMIN" == "1" ]]; then
  [[ -n "$ADMIN_FIRSTNAME" && -n "$ADMIN_LASTNAME" && -n "$ADMIN_EMAIL" && -n "$ADMIN_PASSWORD" ]] \
    || fatal "CREATE_SUPER_ADMIN=1 mais ADMIN_* incomplet."
  info "Création du super admin..."
  php bin/console app:create-super-admin "$ADMIN_EMAIL" "$ADMIN_PASSWORD" "$ADMIN_FIRSTNAME" "$ADMIN_LASTNAME"
  success "Super admin créé."
fi

section "Installation front (Next.js)"
cd "$FRONT_DIR"
if [[ -f package-lock.json ]]; then
  npm ci
else
  npm install
fi
npm run build
success "Front buildé."

if [[ "$SETUP_SYSTEMD" == "1" ]]; then
  section "Configuration systemd"
  command -v systemctl >/dev/null 2>&1 || fatal "systemctl introuvable."

  if [[ "$EUID" -ne 0 ]]; then
    command -v sudo >/dev/null 2>&1 || fatal "sudo requis pour écrire les unités systemd."
    SUDO="sudo"
  else
    SUDO=""
  fi

  FRONT_SERVICE_PATH="/etc/systemd/system/${SYSTEMD_FRONT_SERVICE_NAME}.service"
  MESSENGER_SERVICE_PATH="/etc/systemd/system/${SYSTEMD_MESSENGER_SERVICE_NAME}.service"

  $SUDO tee "$FRONT_SERVICE_PATH" >/dev/null <<EOF
[Unit]
Description=Lueur d'eternite Front (Next.js)
After=network.target

[Service]
Type=simple
User=$DEPLOY_USER
WorkingDirectory=$FRONT_DIR
Environment=NODE_ENV=production
ExecStart=/usr/bin/env npm start
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

  $SUDO tee "$MESSENGER_SERVICE_PATH" >/dev/null <<EOF
[Unit]
Description=Lueur d'eternite Messenger Worker
After=network.target

[Service]
Type=simple
User=$DEPLOY_USER
WorkingDirectory=$BACK_DIR
ExecStart=/usr/bin/env php bin/console messenger:consume async --time-limit=3600 --memory-limit=256M
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

  $SUDO systemctl daemon-reload
  $SUDO systemctl enable --now "${SYSTEMD_FRONT_SERVICE_NAME}.service"
  $SUDO systemctl enable --now "${SYSTEMD_MESSENGER_SERVICE_NAME}.service"
  success "Services systemd actifs."
fi

if [[ "$RUN_HEALTHCHECKS" == "1" && $(command -v curl >/dev/null 2>&1; echo $?) -eq 0 ]]; then
  section "Healthchecks"
  curl -fsS "${BACK_URL}/api" >/dev/null && success "Back API OK (${BACK_URL}/api)" || warn "Back API non joignable"
  curl -fsS "${FRONT_URL}" >/dev/null && success "Front OK (${FRONT_URL})" || warn "Front non joignable"
fi

echo ""
echo -e "${GREEN}${BOLD}Déploiement terminé.${NC}"
echo -e "  Back  : ${CYAN}${BACK_URL}${NC}"
echo -e "  Front : ${CYAN}${FRONT_URL}${NC}"
