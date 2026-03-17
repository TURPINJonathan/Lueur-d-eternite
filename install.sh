#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACK_DIR="$SCRIPT_DIR/back"
FRONT_DIR="$SCRIPT_DIR/front"

# ─── Couleurs ─────────────────────────────────────────────────────────────────
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

# ─── Détection de l'OS ────────────────────────────────────────────────────────
# Remplit : OS_TYPE  (windows | linux | macos | unknown)
#           OS_DISTRO (ubuntu | debian | centos | rhel | fedora | alpine | arch | unknown)
#           PKG_MANAGER (apt | dnf | yum | apk | pacman | brew | xampp | unknown)

OS_TYPE="unknown"; OS_DISTRO="unknown"; PKG_MANAGER="unknown"

detect_os() {
  if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "mingw"* || "$OSTYPE" == "cygwin" ]]; then
    OS_TYPE="windows"
    PKG_MANAGER="xampp"
  elif [[ "$OSTYPE" == "darwin"* ]]; then
    OS_TYPE="macos"
    PKG_MANAGER="brew"
  else
    OS_TYPE="linux"
    if [[ -f /etc/os-release ]]; then
      # shellcheck source=/dev/null
      local distro_id
      distro_id=$(grep '^ID=' /etc/os-release | cut -d= -f2 | tr -d '"' | tr '[:upper:]' '[:lower:]')
      OS_DISTRO="${distro_id:-unknown}"
    fi
    if   command -v apt-get &>/dev/null; then PKG_MANAGER="apt"
    elif command -v dnf     &>/dev/null; then PKG_MANAGER="dnf"
    elif command -v yum     &>/dev/null; then PKG_MANAGER="yum"
    elif command -v apk     &>/dev/null; then PKG_MANAGER="apk"
    elif command -v pacman  &>/dev/null; then PKG_MANAGER="pacman"
    fi
  fi
}

detect_os

# ─── Utilitaires généraux ─────────────────────────────────────────────────────

require() {
  if ! command -v "$1" &>/dev/null; then
    fatal "'$1' est requis mais introuvable dans le PATH."
  fi
}

gen_secret()  { openssl rand -hex 32; }
gen_b64_key() { openssl rand -base64 32; }

urlencode() {
  local str="$1"
  python3 -c "import urllib.parse,sys; print(urllib.parse.quote(sys.argv[1],safe=''))" "$str" 2>/dev/null \
    || node -e "process.stdout.write(encodeURIComponent(process.argv[1]))" "$str" 2>/dev/null \
    || printf '%s' "$str"
}

url_to_cors_regex() {
  local url="$1"
  local host
  host=$(printf '%s' "$url" \
    | sed -E 's|https?://||' \
    | sed -E 's|/.*||' \
    | sed -E 's|:[0-9]+$||')
  local escaped
  escaped=$(printf '%s' "$host" | sed 's/\./\\./g')
  printf '%s' "^https?://${escaped}(:[0-9]+)?\$"
}

read_required() {
  local prompt="$1" val=""
  while [[ -z "$val" ]]; do
    read -r -p "  $prompt : " val
    [[ -z "$val" ]] && warn "Ce champ est obligatoire."
  done
  printf '%s' "$val"
}

read_default() {
  local prompt="$1" default="$2" val
  read -r -p "  $prompt [$default] : " val
  printf '%s' "${val:-$default}"
}

read_secret() {
  local prompt="$1" val=""
  while [[ -z "$val" ]]; do
    read -r -s -p "  $prompt : " val
    echo
    [[ -z "$val" ]] && warn "Ce champ est obligatoire."
  done
  printf '%s' "$val"
}

confirm() {
  local prompt="$1" default="${2:-y}" answer
  if [[ "$default" == "y" ]]; then
    read -r -p "  $prompt [Y/n] : " answer; answer="${answer:-y}"
  else
    read -r -p "  $prompt [y/N] : " answer; answer="${answer:-n}"
  fi
  [[ "${answer,,}" == "y" ]]
}

handle_existing_env() {
  local file="$1" label="$2"
  if [[ -f "$file" ]]; then
    warn "$label existe déjà."
    if confirm "  Écraser ?" n; then
      cp "$file" "${file}.bak"
      info "Sauvegarde créée : ${file}.bak"
      return 0
    else
      info "$label conservé tel quel."
      return 1
    fi
  fi
  return 0
}

# ─── Instructions d'installation selon l'OS ──────────────────────────────────

# Convertit un nom d'extension vers le nom de paquet selon le gestionnaire
ext_to_pkg() {
  local ext="$1" major="$2" minor="$3"
  local ver="${major}.${minor}"

  case "$PKG_MANAGER" in
    apt)
      case "$ext" in
        pdo_mysql|mysql) echo "php${ver}-mysql" ;;
        mbstring)        echo "php${ver}-mbstring" ;;
        xml|dom)         echo "php${ver}-xml" ;;
        intl)            echo "php${ver}-intl" ;;
        gd)              echo "php${ver}-gd" ;;
        zip)             echo "php${ver}-zip" ;;
        curl)            echo "php${ver}-curl" ;;
        fileinfo)        echo "php${ver}-fileinfo" ;;
        zlib)            echo "php${ver}-common" ;;   # inclus dans php-common
        *)               echo "php${ver}-${ext}" ;;
      esac
      ;;
    dnf|yum)
      case "$ext" in
        pdo_mysql|mysql) echo "php-mysqlnd" ;;
        mbstring)        echo "php-mbstring" ;;
        xml|dom)         echo "php-xml" ;;
        intl)            echo "php-intl" ;;
        gd)              echo "php-gd" ;;
        zip)             echo "php-zip" ;;
        curl)            echo "php-curl" ;;
        zlib)            echo "php-common" ;;
        *)               echo "php-${ext}" ;;
      esac
      ;;
    apk)
      # Alpine : php83-xxx (utilise le major uniquement)
      case "$ext" in
        pdo_mysql|mysql) echo "php${major}-pdo_mysql" ;;
        xml|dom)         echo "php${major}-xml" ;;
        zlib)            echo "php${major}-zlib" ;;
        *)               echo "php${major}-${ext}" ;;
      esac
      ;;
    pacman)
      case "$ext" in
        pdo_mysql|mysql) echo "php-mysql" ;;
        *)               echo "php-${ext}" ;;
      esac
      ;;
    *)
      echo "${ext}"
      ;;
  esac
}

# Affiche les instructions pour activer des extensions PHP selon l'OS détecté
show_php_fix_instructions() {
  local ini_path="$1" major="$2" minor="$3"
  shift 3
  local exts=("$@")
  local ver="${major}.${minor}"

  case "$OS_TYPE" in

    windows)
      echo -e "  ${BOLD}Windows — XAMPP / WAMP :${NC}"
      echo -e "    Ouvrez : ${BOLD}${ini_path}${NC}"
      echo -e "    Cherchez et décommentez (retirez le ';') ou ajoutez :"
      for ext in "${exts[@]}"; do
        [[ "$ext" == php* ]] && continue
        echo -e "      extension=${ext}"
      done
      echo -e "    Redémarrez Apache depuis le panneau de contrôle XAMPP/WAMP."
      ;;

    linux)
      case "$PKG_MANAGER" in
        apt)
          local pkgs=()
          for ext in "${exts[@]}"; do
            [[ "$ext" == php* ]] && continue
            pkgs+=("$(ext_to_pkg "$ext" "$major" "$minor")")
          done
          # Dédoublonne
          local unique_pkgs
          unique_pkgs=$(printf '%s\n' "${pkgs[@]}" | sort -u | tr '\n' ' ')
          echo -e "  ${BOLD}Ubuntu / Debian (apt) :${NC}"
          echo -e "    sudo apt update"
          echo -e "    sudo apt install ${unique_pkgs}"
          echo -e "    sudo systemctl restart php${ver}-fpm   # ou: apache2"
          ;;
        dnf|yum)
          local pkgs=()
          for ext in "${exts[@]}"; do
            [[ "$ext" == php* ]] && continue
            pkgs+=("$(ext_to_pkg "$ext" "$major" "$minor")")
          done
          local unique_pkgs
          unique_pkgs=$(printf '%s\n' "${pkgs[@]}" | sort -u | tr '\n' ' ')
          echo -e "  ${BOLD}RHEL / CentOS / Fedora (${PKG_MANAGER}) :${NC}"
          echo -e "    sudo ${PKG_MANAGER} install ${unique_pkgs}"
          echo -e "    sudo systemctl restart php-fpm"
          ;;
        apk)
          local pkgs=()
          for ext in "${exts[@]}"; do
            [[ "$ext" == php* ]] && continue
            pkgs+=("$(ext_to_pkg "$ext" "$major" "$minor")")
          done
          local unique_pkgs
          unique_pkgs=$(printf '%s\n' "${pkgs[@]}" | sort -u | tr '\n' ' ')
          echo -e "  ${BOLD}Alpine Linux (apk) :${NC}"
          echo -e "    apk add ${unique_pkgs}"
          ;;
        pacman)
          local pkgs=()
          for ext in "${exts[@]}"; do
            [[ "$ext" == php* ]] && continue
            pkgs+=("$(ext_to_pkg "$ext" "$major" "$minor")")
          done
          local unique_pkgs
          unique_pkgs=$(printf '%s\n' "${pkgs[@]}" | sort -u | tr '\n' ' ')
          echo -e "  ${BOLD}Arch Linux (pacman) :${NC}"
          echo -e "    sudo pacman -S ${unique_pkgs}"
          ;;
        *)
          echo -e "  ${BOLD}Linux (gestionnaire inconnu) :${NC}"
          echo -e "    Activez dans ${ini_path} :"
          for ext in "${exts[@]}"; do
            [[ "$ext" == php* ]] && continue
            echo -e "      extension=${ext}"
          done
          ;;
      esac
      ;;

    macos)
      echo -e "  ${BOLD}macOS — Homebrew :${NC}"
      echo -e "    brew install php   # GD, intl, mbstring, xml, zip inclus"
      echo -e "    brew services restart php"
      echo -e "    Pour les autres extensions : pecl install <nom>"
      ;;

    *)
      echo -e "  ${BOLD}OS non reconnu — méthode générique :${NC}"
      echo -e "    Activez dans ${ini_path} :"
      for ext in "${exts[@]}"; do
        [[ "$ext" == php* ]] && continue
        echo -e "      extension=${ext}"
      done
      ;;
  esac
}

# Affiche les instructions d'installation de Node.js selon l'OS
show_node_install_instructions() {
  echo ""
  echo -e "  ${BOLD}Installer Node.js 18+ :${NC}"
  case "$OS_TYPE" in
    windows)
      echo -e "    Téléchargez depuis : https://nodejs.org"
      echo -e "    Ou via nvm-windows : https://github.com/coreybutler/nvm-windows"
      ;;
    linux)
      echo -e "    Via nvm (recommandé, indépendant de l'OS) :"
      echo -e "      curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash"
      echo -e "      nvm install 22"
      case "$PKG_MANAGER" in
        apt)
          echo -e "    Via apt (NodeSource) :"
          echo -e "      curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -"
          echo -e "      sudo apt install -y nodejs"
          ;;
        dnf|yum)
          echo -e "    Via ${PKG_MANAGER} (NodeSource) :"
          echo -e "      curl -fsSL https://rpm.nodesource.com/setup_22.x | sudo bash -"
          echo -e "      sudo ${PKG_MANAGER} install nodejs"
          ;;
        apk)
          echo -e "    Via apk :"
          echo -e "      apk add nodejs npm"
          ;;
      esac
      ;;
    macos)
      echo -e "    brew install node"
      echo -e "    Ou via nvm : nvm install 22"
      ;;
  esac
}

# ─── Vérification PHP ─────────────────────────────────────────────────────────
# Un seul appel PHP pour tout récupérer (évite les blocages Windows/XAMPP)

check_php_environment() {
  section "Environnement PHP"
  echo ""

  local missing_required=()
  local missing_recommended=()
  local missing_future=()
  local ini_warnings=()

  # ── Collecte en un seul appel PHP + < /dev/null pour couper stdin ─────────
  local php_data
  php_data=$(php -r "
    \$out = [];
    \$out[] = 'php_version='   . PHP_VERSION;
    \$out[] = 'php_major='     . PHP_MAJOR_VERSION;
    \$out[] = 'php_minor='     . PHP_MINOR_VERSION;
    foreach ([
      'ctype','iconv','pdo','pdo_mysql','openssl','json',
      'mbstring','xml','dom','tokenizer','session',
      'curl','intl','fileinfo','zip',
      'gd','zlib'
    ] as \$e) {
      \$out[] = 'ext_' . \$e . '=' . (extension_loaded(\$e) ? '1' : '0');
    }
    \$out[] = 'ini_memory_limit='        . ini_get('memory_limit');
    \$out[] = 'ini_upload_max='          . ini_get('upload_max_filesize');
    \$out[] = 'ini_post_max='            . ini_get('post_max_size');
    \$out[] = 'ini_timezone='            . ini_get('date.timezone');
    \$out[] = 'ini_max_exec='            . ini_get('max_execution_time');
    \$mem = ini_get('memory_limit');
    \$n = (int)\$mem;
    \$u = strtolower(substr(\$mem,-1));
    if (\$u==='g') \$n*=1024; elseif (\$u==='k') \$n=intval(\$n/1024);
    elseif (\$u!=='m') \$n=intval(\$n/1024/1024);
    \$out[] = 'ini_memory_mb=' . (\$mem=='-1' ? '99999' : \$n);
    echo implode(\"\\n\", \$out) . \"\\n\";
  " 2>/dev/null < /dev/null) || fatal "PHP ne répond pas. Vérifiez que XAMPP/PHP est bien dans le PATH."

  # Lecture d'une clé dans les données collectées
  get_val() { printf '%s\n' "$php_data" | grep "^$1=" | cut -d= -f2-; }
  is_ext()  { [[ "$(get_val "ext_$1")" == "1" ]]; }

  # ── Version PHP ──────────────────────────────────────────────────────────────
  local php_version php_major php_minor
  php_version=$(get_val "php_version")
  php_major=$(get_val "php_major")
  php_minor=$(get_val "php_minor")

  if (( php_major > 8 )) || (( php_major == 8 && php_minor >= 2 )); then
    success "PHP $php_version"
  else
    echo -e "  ${RED}✗${NC}  PHP $php_version  —  version ${BOLD}8.2+${NC} requise"
    missing_required+=("php>=8.2")
  fi

  # ── Extensions requises ───────────────────────────────────────────────────
  echo ""
  echo -e "  ${BOLD}Extensions requises :${NC}"

  local req_exts=(
    "ctype:caractères spéciaux (Symfony core)"
    "iconv:encodage de chaînes (Symfony core)"
    "pdo:couche d'abstraction base de données"
    "pdo_mysql:pilote MariaDB / MySQL"
    "openssl:génération clés JWT + chiffrement AES"
    "json:réponses API + colonnes JSON Doctrine"
    "mbstring:manipulation de chaînes multi-octets"
    "xml:métadonnées Doctrine"
    "dom:Symfony / API Platform"
    "tokenizer:compilateur Symfony"
    "session:authentification back-office"
  )

  for entry in "${req_exts[@]}"; do
    local ext="${entry%%:*}" desc="${entry##*:}"
    if is_ext "$ext"; then
      printf "    ${GREEN}✓${NC}  %-18s  %s\n" "ext-$ext" "$desc"
    else
      printf "    ${RED}✗${NC}  %-18s  ${RED}MANQUANT${NC} — %s\n" "ext-$ext" "$desc"
      missing_required+=("$ext")
    fi
  done

  # ── Extensions recommandées ───────────────────────────────────────────────
  echo ""
  echo -e "  ${BOLD}Extensions recommandées :${NC}"

  local rec_exts=(
    "curl:client HTTP (GeocodingService)"
    "intl:internationalisation (symfony/intl)"
    "fileinfo:détection type MIME (uploads)"
    "zip:archives"
  )

  for entry in "${rec_exts[@]}"; do
    local ext="${entry%%:*}" desc="${entry##*:}"
    if is_ext "$ext"; then
      printf "    ${GREEN}✓${NC}  %-18s  %s\n" "ext-$ext" "$desc"
    else
      printf "    ${YELLOW}!${NC}  %-18s  manquant — %s\n" "ext-$ext" "$desc"
      missing_recommended+=("$ext")
    fi
  done

  # ── Extensions futures ────────────────────────────────────────────────────
  echo ""
  echo -e "  ${BOLD}Extensions requises prochainement (stockage de médias) :${NC}"

  local future_exts=(
    "gd:traitement d'images WebP"
    "zlib:compression de fichiers (gzcompress)"
  )

  for entry in "${future_exts[@]}"; do
    local ext="${entry%%:*}" desc="${entry##*:}"
    if is_ext "$ext"; then
      printf "    ${GREEN}✓${NC}  %-18s  %s\n" "ext-$ext" "$desc"
    else
      printf "    ${CYAN}→${NC}  %-18s  absent — %s\n" "ext-$ext" "$desc"
      missing_future+=("$ext")
    fi
  done

  # ── Paramètres php.ini ────────────────────────────────────────────────────
  echo ""
  echo -e "  ${BOLD}Paramètres php.ini :${NC}"

  local mem_limit upload_max post_max timezone max_exec mem_mb
  mem_limit=$(get_val "ini_memory_limit")
  upload_max=$(get_val "ini_upload_max")
  post_max=$(get_val "ini_post_max")
  timezone=$(get_val "ini_timezone")
  max_exec=$(get_val "ini_max_exec")
  mem_mb=$(get_val "ini_memory_mb")

  if (( mem_mb >= 256 )); then
    printf "    ${GREEN}✓${NC}  %-25s  = %s\n" "memory_limit" "$mem_limit"
  else
    printf "    ${YELLOW}!${NC}  %-25s  = %-8s  ${YELLOW}recommandé : 256M minimum${NC}\n" "memory_limit" "$mem_limit"
    ini_warnings+=("memory_limit = 256M")
  fi

  printf "    ${CYAN}→${NC}  %-25s  = %-8s  (recommandé : 10M pour les médias)\n" "upload_max_filesize" "$upload_max"
  printf "    ${CYAN}→${NC}  %-25s  = %-8s  (recommandé : 10M pour les médias)\n" "post_max_size" "$post_max"

  if [[ "$max_exec" == "0" ]] || (( max_exec >= 30 )); then
    printf "    ${GREEN}✓${NC}  %-25s  = %s\n" "max_execution_time" "$max_exec"
  else
    printf "    ${YELLOW}!${NC}  %-25s  = %-8s  ${YELLOW}recommandé : 30 minimum${NC}\n" "max_execution_time" "$max_exec"
    ini_warnings+=("max_execution_time = 30")
  fi

  if [[ -n "$timezone" ]]; then
    printf "    ${GREEN}✓${NC}  %-25s  = %s\n" "date.timezone" "$timezone"
  else
    printf "    ${YELLOW}!${NC}  %-25s  ${YELLOW}non défini — recommandé : Europe/Paris${NC}\n" "date.timezone"
    ini_warnings+=("date.timezone = Europe/Paris")
  fi

  # ── Chemin du php.ini ─────────────────────────────────────────────────────
  echo ""
  local ini_path
  ini_path=$(php --ini 2>/dev/null < /dev/null | grep "Loaded Configuration" | awk -F: '{print $2}' | xargs 2>/dev/null || echo "introuvable")
  info "Fichier php.ini : ${BOLD}${ini_path}${NC}"
  info "OS détecté      : ${BOLD}${OS_TYPE}${OS_DISTRO:+ (${OS_DISTRO})}  —  gestionnaire : ${PKG_MANAGER}${NC}"

  # ── Résumé et instructions de correction ─────────────────────────────────
  if (( ${#missing_required[@]} > 0 )); then
    echo ""
    echo -e "  ${RED}${BOLD}✗  ${#missing_required[@]} extension(s) requise(s) manquante(s) :${NC}"
    for ext in "${missing_required[@]}"; do
      echo -e "     ${RED}•${NC} $ext"
    done
    echo ""
    echo -e "  ${BOLD}Comment les activer :${NC}"
    echo ""
    show_php_fix_instructions "${ini_path}" "${php_major}" "${php_minor}" "${missing_required[@]}"
    echo ""
    fatal "Corrigez les extensions manquantes puis relancez install.sh."
  fi

  if (( ${#missing_recommended[@]} > 0 )); then
    echo ""
    warn "${#missing_recommended[@]} extension(s) recommandée(s) absente(s) : ${missing_recommended[*]}"
    if ! confirm "Continuer malgré les avertissements ?"; then
      exit 0
    fi
  fi

  if (( ${#ini_warnings[@]} > 0 )); then
    echo ""
    warn "Paramètres php.ini à ajuster (dans ${ini_path}) :"
    for w in "${ini_warnings[@]}"; do
      echo -e "    ${YELLOW}•${NC} $w"
    done
    echo ""
  fi

  if (( ${#missing_future[@]} > 0 )); then
    echo ""
    info "À installer avant d'activer le stockage de médias :"
    for ext in "${missing_future[@]}"; do
      echo -e "    ${CYAN}•${NC} ext-${ext}"
    done
    show_php_fix_instructions "${ini_path}" "${php_major}" "${php_minor}" "${missing_future[@]}"
  fi

  echo ""
  success "Environnement PHP validé."
}

# ─── Vérification Node.js ─────────────────────────────────────────────────────

check_node_version() {
  local node_version major
  node_version=$(node --version | tr -d 'v')
  major=$(echo "$node_version" | cut -d. -f1)

  if (( major >= 18 )); then
    success "Node.js v${node_version}"
  else
    echo -e "  ${RED}✗${NC}  Node.js v${node_version}  —  version ${BOLD}18+${NC} requise par Next.js"
    show_node_install_instructions
    echo ""
    fatal "Mettez à jour Node.js puis relancez install.sh."
  fi
}

################################################################################
# BANNER
################################################################################

echo -e "\n${BLUE}${BOLD}"
echo "  ╔══════════════════════════════════════════════╗"
echo "  ║        LUEUR D'ÉTERNITÉ  —  Installation         ║"
echo "  ╚══════════════════════════════════════════════╝"
echo -e "${NC}"

[[ -d "$BACK_DIR"  ]] || fatal "Répertoire 'back/'  introuvable. Lancez ce script depuis la racine du repo."
[[ -d "$FRONT_DIR" ]] || fatal "Répertoire 'front/' introuvable. Lancez ce script depuis la racine du repo."

################################################################################
# 1. Dépendances système
################################################################################

section "Dépendances système"
echo ""
for cmd in php composer node npm openssl; do
  require "$cmd"
  success "$cmd  →  $(command -v "$cmd")"
done

################################################################################
# 2. Environnement PHP
################################################################################

check_php_environment

################################################################################
# 3. Version Node.js
################################################################################

check_node_version

################################################################################
# 4. Questionnaire
################################################################################

section "Environnement cible"
echo ""
echo -e "    ${BOLD}1${NC})  dev      — debug activé, logs verbeux"
echo -e "    ${BOLD}2${NC})  staging  — debug désactivé, sans build"
echo -e "    ${BOLD}3${NC})  prod     — optimisé, build Next.js inclus"
echo ""
ENV_CHOICE=""
while [[ ! "$ENV_CHOICE" =~ ^[123]$ ]]; do
  read -r -p "  Choix [1/2/3] : " ENV_CHOICE
done

case "$ENV_CHOICE" in
  1) APP_ENV=dev;     APP_DEBUG=1 ;;
  2) APP_ENV=staging; APP_DEBUG=0 ;;
  3) APP_ENV=prod;    APP_DEBUG=0 ;;
esac
echo ""
success "Environnement : ${BOLD}${APP_ENV}${NC}"

section "URLs"
echo ""
BACK_URL=$(read_default  "URL du back  (Symfony / API)" "http://localhost:8000")
FRONT_URL=$(read_default "URL du front (Next.js)"       "http://localhost:3000")

section "Base de données MariaDB"
echo ""
DB_HOST=$(read_default "Hôte"           "127.0.0.1")
DB_PORT=$(read_default "Port"           "3306")
DB_NAME=$(read_default "Nom de la base" "elixir_linge")
DB_USER=$(read_default "Utilisateur"    "root")
read -r -s -p "  Mot de passe DB (vide = aucun) : " DB_PASS
echo ""

section "Mailer"
echo ""
if [[ "$APP_ENV" == "dev" ]]; then
  MAILER_DSN="null://null"
  info "Mode dev → mailer désactivé (null://null)"
else
  echo -e "  Exemples :"
  echo -e "    smtp://user:pass@smtp.example.com:587"
  echo -e "    null://null  (désactivé)"
  echo ""
  read -r -p "  MAILER_DSN [null://null] : " MAILER_DSN
  MAILER_DSN="${MAILER_DSN:-null://null}"
fi

section "Sécurité"
echo ""
APP_SECRET=$(gen_secret)
MEDIA_ENCRYPTION_KEY=$(gen_b64_key)
info "APP_SECRET             — généré automatiquement"
info "MEDIA_ENCRYPTION_KEY   — généré automatiquement"
echo ""

if confirm "Générer la JWT_PASSPHRASE automatiquement ?"; then
  JWT_PASSPHRASE=$(gen_secret)
  info "JWT_PASSPHRASE         — générée automatiquement"
else
  JWT_PASSPHRASE=$(read_secret "JWT_PASSPHRASE")
fi

DB_PASS_ENCODED=$(urlencode "$DB_PASS")
DATABASE_URL="mysql://${DB_USER}:${DB_PASS_ENCODED}@${DB_HOST}:${DB_PORT}/${DB_NAME}?serverVersion=mariadb-8.0.32&charset=utf8mb4"
CORS_REGEX=$(url_to_cors_regex "$FRONT_URL")

################################################################################
# 5. Génération des fichiers .env
################################################################################

section "Génération des fichiers .env"
echo ""

BACK_ENV="$BACK_DIR/.env.local"
if handle_existing_env "$BACK_ENV" "back/.env.local"; then
  {
    printf "###> symfony/framework-bundle ###\n"
    printf "APP_ENV=%s\n"    "$APP_ENV"
    printf "APP_DEBUG=%s\n"  "$APP_DEBUG"
    printf "APP_SECRET=%s\n" "$APP_SECRET"
    printf "###< symfony/framework-bundle ###\n\n"
    printf "###> symfony/routing ###\n"
    printf "DEFAULT_URI=%s\n" "$BACK_URL"
    printf "###< symfony/routing ###\n\n"
    printf "###> doctrine/doctrine-bundle ###\n"
    printf 'DATABASE_URL="%s"\n' "$DATABASE_URL"
    printf "###< doctrine/doctrine-bundle ###\n\n"
    printf "###> symfony/messenger ###\n"
    printf "MESSENGER_TRANSPORT_DSN=doctrine://default\n"
    printf "###< symfony/messenger ###\n\n"
    printf "###> symfony/mailer ###\n"
    printf "MAILER_DSN=%s\n" "$MAILER_DSN"
    printf "###< symfony/mailer ###\n\n"
    printf "###> nelmio/cors-bundle ###\n"
    printf "CORS_ALLOW_ORIGIN='%s'\n" "$CORS_REGEX"
    printf "###< nelmio/cors-bundle ###\n\n"
    printf "###> lexik/jwt-authentication-bundle ###\n"
    printf "JWT_SECRET_KEY=%%kernel.project_dir%%/config/jwt/private.pem\n"
    printf "JWT_PUBLIC_KEY=%%kernel.project_dir%%/config/jwt/public.pem\n"
    printf "JWT_PASSPHRASE=%s\n" "$JWT_PASSPHRASE"
    printf "###< lexik/jwt-authentication-bundle ###\n\n"
    printf "###> media ###\n"
    printf "MEDIA_ENCRYPTION_KEY=%s\n" "$MEDIA_ENCRYPTION_KEY"
    printf "###< media ###\n"
  } > "$BACK_ENV"
  success "back/.env.local créé."
fi

FRONT_ENV="$FRONT_DIR/.env.local"
if handle_existing_env "$FRONT_ENV" "front/.env.local"; then
  {
    printf "# URL de l'API côté serveur (SSR / API routes Next.js)\n"
    printf "API_URL=%s/api\n" "$BACK_URL"
    printf "\n"
    printf "# URL de l'API accessible depuis le navigateur\n"
    printf "NEXT_PUBLIC_API_URL=%s/api\n" "$BACK_URL"
    printf "\n"
    printf "# URL publique du front\n"
    printf "NEXT_PUBLIC_APP_URL=%s\n" "$FRONT_URL"
    printf "\n"
    printf "# Nom de l'application\n"
    printf "NEXT_PUBLIC_APP_NAME=Elixir Linge\n"
  } > "$FRONT_ENV"
  success "front/.env.local créé."
fi

################################################################################
# Génération des clés JWT
# Tente d'abord la commande Lexik ; si PHP/OpenSSL échoue (fréquent sur
# Windows/XAMPP), on bascule automatiquement sur le binaire openssl CLI.
################################################################################

generate_jwt_keys() {
  local jwt_dir="$BACK_DIR/config/jwt"
  mkdir -p "$jwt_dir"

  # Demander si on régénère des clés existantes
  local overwrite=false
  if [[ -f "$jwt_dir/private.pem" ]]; then
    warn "Clés JWT déjà présentes."
    if confirm "  Régénérer ? (les tokens en cours seront invalidés)" n; then
      overwrite=true
    else
      info "Clés JWT conservées."
      return
    fi
  fi

  # ── Sur Windows : aider PHP à trouver openssl.cnf ──────────────────────────
  # PHP hérite de OPENSSL_CONF ; sans lui, il échoue avec :
  #   "error:80000003:system library::No such process"
  if [[ "$OS_TYPE" == "windows" ]]; then
    if [[ -z "${OPENSSL_CONF:-}" ]]; then
      local php_bin xampp_root
      php_bin=$(command -v php)
      xampp_root=$(dirname "$(dirname "$php_bin")")   # /c/xampp/php/php → /c/xampp

      local candidates=(
        "${xampp_root}/apache/conf/openssl.cnf"
        "${xampp_root}/php/extras/openssl/openssl.cnf"
        "/c/xampp/apache/conf/openssl.cnf"
        "/c/wamp64/bin/apache/apache2.4.62/conf/openssl.cnf"
        "/mingw64/ssl/openssl.cnf"
        "/usr/ssl/openssl.cnf"
      )
      for f in "${candidates[@]}"; do
        if [[ -f "$f" ]]; then
          export OPENSSL_CONF="$f"
          info "OPENSSL_CONF détecté → $f"
          break
        fi
      done

      if [[ -z "${OPENSSL_CONF:-}" ]]; then
        warn "openssl.cnf introuvable automatiquement."
        warn "Si la génération échoue, définissez : export OPENSSL_CONF=/c/xampp/apache/conf/openssl.cnf"
      fi
    else
      info "OPENSSL_CONF déjà défini → ${OPENSSL_CONF}"
    fi
  fi

  # ── Tentative 1 : commande Lexik ───────────────────────────────────────────
  local lexik_flags=""
  [[ "$overwrite" == true ]] && lexik_flags="--overwrite"

  if php bin/console lexik:jwt:generate-keypair $lexik_flags 2>/tmp/lexik-jwt.err; then
    success "Clés JWT générées (via lexik:jwt:generate-keypair)."
    return
  fi

  # ── Tentative 2 : fallback openssl CLI ─────────────────────────────────────
  warn "La commande Lexik a échoué :"
  sed 's/^/    /' /tmp/lexik-jwt.err >&2 || true
  echo ""
  info "Basculement sur openssl CLI..."

  if ! command -v openssl &>/dev/null; then
    fatal "openssl introuvable dans le PATH. Impossible de générer les clés JWT."
  fi

  [[ "$overwrite" == true ]] && rm -f "$jwt_dir/private.pem" "$jwt_dir/public.pem"

  # Clé privée RSA-4096 chiffrée avec JWT_PASSPHRASE
  openssl genpkey \
    -algorithm RSA \
    -out "$jwt_dir/private.pem" \
    -pkeyopt rsa_keygen_bits:4096 \
    -aes256 \
    -pass "pass:${JWT_PASSPHRASE}"

  # Clé publique extraite
  openssl pkey \
    -in  "$jwt_dir/private.pem" \
    -out "$jwt_dir/public.pem" \
    -pubout \
    -passin "pass:${JWT_PASSPHRASE}"

  # Permissions restrictives (inutile sur Windows mais bonne pratique)
  if [[ "$OS_TYPE" != "windows" ]]; then
    chmod 600 "$jwt_dir/private.pem"
    chmod 644 "$jwt_dir/public.pem"
  fi

  success "Clés JWT générées (via openssl CLI)."
}

################################################################################
# 6. Installation du back (Symfony)
################################################################################

section "Installation du back (Symfony)"
echo ""

cd "$BACK_DIR"

info "Installation des dépendances PHP..."
if [[ "$APP_ENV" == "prod" ]]; then
  composer install --no-dev --optimize-autoloader
else
  composer install
fi
success "Dépendances PHP installées."

echo ""
generate_jwt_keys

echo ""
info "Création de la base de données si elle n'existe pas..."
php bin/console doctrine:database:create --if-not-exists
success "Base de données prête."

echo ""
info "Exécution des migrations Doctrine..."
php bin/console doctrine:migrations:migrate --no-interaction
success "Migrations exécutées."

echo ""
info "Vidage du cache Symfony..."
php bin/console cache:clear
success "Cache vidé."

cd "$SCRIPT_DIR"

################################################################################
# 7. Installation du front (Next.js)
################################################################################

section "Installation du front (Next.js)"
echo ""

cd "$FRONT_DIR"

info "Installation des dépendances Node..."
npm install
success "Dépendances Node installées."

if [[ "$APP_ENV" == "prod" ]]; then
  echo ""
  info "Build de production Next.js..."
  npm run build
  success "Build terminé."
fi

cd "$SCRIPT_DIR"

################################################################################
# 8. Création du super administrateur
################################################################################

section "Création du super administrateur"
echo ""
bash "$SCRIPT_DIR/create-super-admin.sh"

################################################################################
# 9. Démarrage des serveurs (optionnel)
################################################################################

# Affiche le tableau des URLs — appelé à la fin dans tous les cas
print_urls() {
  echo -e "  ${BOLD}API${NC}         →  ${CYAN}${BACK_URL}/api${NC}"
  echo -e "  ${BOLD}Back-office${NC} →  ${CYAN}${BACK_URL}/backoffice${NC}"
  echo -e "  ${BOLD}Swagger${NC}     →  ${CYAN}${BACK_URL}/api/docs${NC}"
  echo -e "  ${BOLD}Front${NC}       →  ${CYAN}${FRONT_URL}${NC}"
}

start_servers() {
  section "Démarrage des serveurs"
  echo ""

  if ! confirm "Démarrer les serveurs maintenant ?"; then
    return
  fi

  case "$APP_ENV" in

    # ── DEV ──────────────────────────────────────────────────────────────────
    dev)
      # Back
      echo ""
      info "Démarrage du back (Symfony)..."
      cd "$BACK_DIR"
      if command -v symfony &>/dev/null; then
        symfony serve --daemon
        success "Symfony démarré en arrière-plan."
        info "Arrêt : cd back && symfony server:stop"
      else
        warn "CLI Symfony introuvable — démarrage via serveur PHP built-in."
        php -S 127.0.0.1:8000 -t public > /tmp/elixir-back.log 2>&1 &
        local back_pid=$!
        success "Serveur PHP démarré (PID ${back_pid}). Logs : /tmp/elixir-back.log"
        warn "Le serveur built-in n'est PAS adapté à la production."
      fi
      cd "$SCRIPT_DIR"

      # Front
      echo ""
      if confirm "Démarrer aussi le front (Next.js dev) ?"; then
        echo ""
        echo -e "    ${BOLD}1${NC})  Premier plan   — occupe ce terminal (Ctrl+C pour arrêter)"
        echo -e "    ${BOLD}2${NC})  Arrière-plan   — nohup, logs dans front/npm-dev.log"
        echo ""
        local front_mode=""
        while [[ ! "$front_mode" =~ ^[12]$ ]]; do
          read -r -p "  Choix [1/2] : " front_mode
        done

        cd "$FRONT_DIR"
        if [[ "$front_mode" == "2" ]]; then
          nohup npm run dev > npm-dev.log 2>&1 &
          local front_pid=$!
          success "Next.js démarré en arrière-plan (PID ${front_pid})."
          info "Logs : front/npm-dev.log"
          cd "$SCRIPT_DIR"
        else
          # Foreground — on affiche les URLs avant que la sortie du serveur prenne le dessus
          cd "$SCRIPT_DIR"
          echo ""
          echo -e "${GREEN}${BOLD}"
          echo "  ╔══════════════════════════════════════════════╗"
          echo "  ║          Installation terminée !              ║"
          echo "  ╚══════════════════════════════════════════════╝"
          echo -e "${NC}"
          print_urls
          echo ""
          info "Next.js démarre... (Ctrl+C pour arrêter)"
          echo ""
          cd "$FRONT_DIR"
          npm run dev
          exit 0   # npm run dev est bloquant ; on ne continue pas après
        fi
      else
        info "Front non démarré. Commande : cd front && npm run dev"
      fi
      ;;

    # ── STAGING / PROD ────────────────────────────────────────────────────────
    staging|prod)
      # Back : géré par le serveur web, rien à lancer ici
      echo ""
      warn "Back (Symfony) — aucune action : doit être servi par Apache ou Nginx."
      info "Vérifiez que votre vhost pointe vers : ${BOLD}back/public/${NC}"

      # Front
      echo ""
      if confirm "Démarrer le front (Next.js) maintenant ?"; then
        cd "$FRONT_DIR"
        if command -v pm2 &>/dev/null; then
          # Arrête l'éventuelle instance précédente sans faire échouer le script
          pm2 stop elixir-linge-front 2>/dev/null || true
          pm2 delete elixir-linge-front 2>/dev/null || true
          pm2 start npm --name "elixir-linge-front" -- start
          pm2 save
          success "Front démarré avec PM2."
          info "pm2 status | pm2 logs elixir-linge-front | pm2 stop elixir-linge-front"
        else
          warn "PM2 non trouvé — démarrage avec nohup."
          nohup npm start > npm-start.log 2>&1 &
          local front_pid=$!
          success "Front démarré en arrière-plan (PID ${front_pid})."
          info "Logs : front/npm-start.log"
          info "Pour une gestion plus robuste : npm install -g pm2"
        fi
        cd "$SCRIPT_DIR"
      fi
      ;;

  esac
}

start_servers

################################################################################
# Résumé final
################################################################################

echo ""
echo -e "${GREEN}${BOLD}"
echo "  ╔══════════════════════════════════════════════╗"
echo "  ║          Installation terminée !              ║"
echo "  ╚══════════════════════════════════════════════╝"
echo -e "${NC}"

print_urls
echo ""

echo -e "  ${BOLD}Commandes manuelles :${NC}"
case "$APP_ENV" in
  dev)
    if command -v symfony &>/dev/null; then
      echo -e "    ${CYAN}cd back  && symfony serve${NC}"
    else
      echo -e "    ${CYAN}cd back  && php -S 127.0.0.1:8000 -t public${NC}"
    fi
    echo -e "    ${CYAN}cd front && npm run dev${NC}"
    ;;
  staging)
    echo -e "    Back  : configurer Apache / Nginx → back/public/"
    echo -e "    ${CYAN}cd front && npm run build && npm start${NC}  (ou pm2)"
    ;;
  prod)
    echo -e "    Back  : configurer Apache / Nginx → back/public/"
    echo -e "    ${CYAN}cd front && npm start${NC}  (ou pm2)"
    ;;
esac

echo ""
