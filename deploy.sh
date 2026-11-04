#!/usr/bin/env bash
#
# HandInHand – Deployment auf netcup (Webhosting/Plesk oder vServer per SSH)
#
# STATUS: Entwurf. Server-Pfade, PHP-Version und Zugang werden noch gemeinsam festgelegt
#         und in .deploy.env eingetragen (Vorlage: .deploy.env.example).
#
# Ablauf:  Prüfen (Tests, Pint, Larastan) -> bauen (Composer --no-dev, Vite) -> Paket schnüren
#          -> hochladen -> auf dem Server entpacken, migrieren, Caches aufwärmen -> Health-Check.
# Der Server braucht weder Composer noch Node: gebaut wird lokal.
# Nie angefasst werden auf dem Server: .env, storage/ (Videos, Fotos, Logs, Sessions), SQLite-Datei.
#
# Aufruf (Git Bash / WSL / Linux / macOS):
#   ./deploy.sh                 vollständiges Deployment
#   ./deploy.sh --build-only    nur Paket bauen (dist/handinhand-*.tar.gz) – z. B. für manuellen Upload per Plesk
#   ./deploy.sh --dry-run       alles bauen und zeigen, was passieren würde; nichts hochladen
#   ./deploy.sh --skip-checks   Tests/Pint/Larastan überspringen (nur im Notfall)
#   ./deploy.sh --help
#
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")"

CONFIG_FILE=".deploy.env"
BUILD_ONLY=0
DRY_RUN=0
SKIP_CHECKS=0

for arg in "$@"; do
    case "$arg" in
        --build-only)  BUILD_ONLY=1 ;;
        --dry-run)     DRY_RUN=1 ;;
        --skip-checks) SKIP_CHECKS=1 ;;
        -h|--help)     sed -n '2,24p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
        *) echo "Unbekannte Option: $arg (siehe --help)" >&2; exit 2 ;;
    esac
done

step() { printf '\n\033[1;33m▶ %s\033[0m\n' "$*"; }
info() { printf '  %s\n' "$*"; }
fail() { printf '\n\033[1;31m✖ %s\033[0m\n' "$*" >&2; exit 1; }

command -v php >/dev/null      || fail "php nicht gefunden."
command -v composer >/dev/null || fail "composer nicht gefunden."
command -v npm >/dev/null      || fail "npm nicht gefunden."
command -v tar >/dev/null      || fail "tar nicht gefunden."

# ---------------------------------------------------------------- Konfiguration
if [[ $BUILD_ONLY -eq 0 ]]; then
    [[ -f "$CONFIG_FILE" ]] || fail "$CONFIG_FILE fehlt. Vorlage kopieren: cp .deploy.env.example $CONFIG_FILE und ausfüllen."
    # shellcheck disable=SC1090
    source "$CONFIG_FILE"
    : "${DEPLOY_HOST:?DEPLOY_HOST fehlt in $CONFIG_FILE}"
    : "${DEPLOY_USER:?DEPLOY_USER fehlt in $CONFIG_FILE}"
    : "${DEPLOY_PATH:?DEPLOY_PATH fehlt in $CONFIG_FILE}"
    DEPLOY_PORT="${DEPLOY_PORT:-22}"
    DEPLOY_PHP="${DEPLOY_PHP:-php}"
    DEPLOY_URL="${DEPLOY_URL:-}"
    command -v ssh >/dev/null || fail "ssh nicht gefunden."
    command -v scp >/dev/null || fail "scp nicht gefunden."
    SSH_OPTS=(-p "$DEPLOY_PORT" -o ServerAliveInterval=30)
    [[ -n "${DEPLOY_SSH_KEY:-}" ]] && SSH_OPTS+=(-i "$DEPLOY_SSH_KEY")
    SCP_OPTS=(-P "$DEPLOY_PORT")
    [[ -n "${DEPLOY_SSH_KEY:-}" ]] && SCP_OPTS+=(-i "$DEPLOY_SSH_KEY")
    REMOTE="$DEPLOY_USER@$DEPLOY_HOST"
fi

STAMP="$(date +%Y%m%d-%H%M%S)"
BUILD_DIR=".deploy-build"
DIST_DIR="dist"
PACKAGE="$DIST_DIR/handinhand-$STAMP.tar.gz"

# ---------------------------------------------------------------- 1. Prüfen
if [[ $SKIP_CHECKS -eq 0 ]]; then
    step "1/5 Prüfen (Tests, Code-Stil, statische Analyse)"
    php artisan test --compact
    ./vendor/bin/pint --test
    ./vendor/bin/phpstan analyse --no-progress --memory-limit=512M
else
    step "1/5 Prüfen – ÜBERSPRUNGEN (--skip-checks)"
fi

# ---------------------------------------------------------------- 2. Bauen
step "2/5 Frontend bauen (Vite)"
npm ci --no-audit --no-fund
npm run build

step "3/5 Paket schnüren (Composer --no-dev)"
rm -rf "$BUILD_DIR" && mkdir -p "$BUILD_DIR" "$DIST_DIR"

# Nur, was der Server braucht – keine Tests, Mockups, Konzept, Dev-Dateien, Geheimnisse, Nutzdaten.
tar \
    --exclude='./.git' --exclude='./.claude' --exclude='./.idea' --exclude='./.vscode' \
    --exclude='./node_modules' --exclude='./vendor' --exclude='./tests' --exclude='./UI_Mockups' \
    --exclude='./Konzept.pdf' --exclude='./README.md' --exclude='./CLAUDE.md' --exclude='./docs' \
    --exclude='./.env' --exclude='./.env.*' --exclude='./.deploy.env' \
    --exclude='./.deploy-build' --exclude='./dist' --exclude='./*.bat' --exclude='./phpstan.neon' \
    --exclude='./public/hot' --exclude='./database/*.sqlite*' \
    --exclude='./storage/app/*' --exclude='./storage/logs/*' --exclude='./storage/framework/*' \
    --exclude='./bootstrap/cache/*.php' \
    -cf - . | tar -xf - -C "$BUILD_DIR"

# Leere Laufzeit-Ordner, damit Laravel auf dem Server sofort schreiben kann
mkdir -p "$BUILD_DIR/storage/app/private" "$BUILD_DIR/storage/app/public" "$BUILD_DIR/storage/logs" \
         "$BUILD_DIR/storage/framework/cache/data" "$BUILD_DIR/storage/framework/sessions" \
         "$BUILD_DIR/storage/framework/views" "$BUILD_DIR/bootstrap/cache"

composer install --working-dir="$BUILD_DIR" --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts
tar -czf "$PACKAGE" -C "$BUILD_DIR" .
rm -rf "$BUILD_DIR"
info "Paket: $PACKAGE ($(du -h "$PACKAGE" | cut -f1))"

if [[ $BUILD_ONLY -eq 1 ]]; then
    step "Fertig (--build-only)"
    info "Manuell hochladen und im App-Ordner entpacken. Danach auf dem Server:"
    info "  php artisan migrate --force && php artisan optimize"
    exit 0
fi

if [[ $DRY_RUN -eq 1 ]]; then
    step "Trockenlauf (--dry-run): es wird nichts hochgeladen"
    info "Ziel:   $REMOTE:$DEPLOY_PATH (Port $DEPLOY_PORT, PHP: $DEPLOY_PHP)"
    info "Inhalt: $(tar -tzf "$PACKAGE" | wc -l | tr -d ' ') Dateien"
    exit 0
fi

# ---------------------------------------------------------------- 3. Hochladen
step "4/5 Hochladen nach $REMOTE:$DEPLOY_PATH"
REMOTE_TMP="handinhand-upload-$STAMP.tar.gz"
scp "${SCP_OPTS[@]}" "$PACKAGE" "$REMOTE:$REMOTE_TMP"

# ---------------------------------------------------------------- 4. Auf dem Server aktivieren
step "5/5 Auf dem Server aktivieren (Wartungsmodus, entpacken, migrieren, Caches)"
ssh "${SSH_OPTS[@]}" "$REMOTE" bash -s -- "$DEPLOY_PATH" "$DEPLOY_PHP" "$REMOTE_TMP" <<'REMOTE_SCRIPT'
set -euo pipefail
APP="$1"; PHP="$2"; PKG="$3"

cd "$APP"
[[ -f .env ]] || { echo "✖ $APP/.env fehlt. Einmalig anlegen (Vorlage: .env.example), APP_KEY mit '$PHP artisan key:generate' erzeugen." >&2; exit 1; }

# Wartungsmodus (falls die App schon läuft)
[[ -f artisan ]] && "$PHP" artisan down --retry=30 || true

# Alten Code entfernen, damit keine verwaisten Dateien bleiben – .env, storage/ und Datenbank bleiben unberührt
rm -rf app bootstrap config database/migrations database/seeders lang resources routes vendor public/build public/app-icons

tar -xzf "$HOME/$PKG" -C "$APP"
rm -f "$HOME/$PKG"

chmod -R u+rwX storage bootstrap/cache

"$PHP" artisan migrate --force
"$PHP" artisan optimize:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache
"$PHP" artisan event:cache

"$PHP" artisan up
echo "✔ Aktiviert."
REMOTE_SCRIPT

# ---------------------------------------------------------------- Health-Check
if [[ -n "$DEPLOY_URL" ]] && command -v curl >/dev/null; then
    step "Health-Check: $DEPLOY_URL/up"
    if curl -fsS --max-time 20 "$DEPLOY_URL/up" >/dev/null; then
        info "App antwortet."
    else
        fail "Health-Check fehlgeschlagen – bitte $DEPLOY_URL und das Server-Log (storage/logs) prüfen."
    fi
fi

printf '\n\033[1;32m✔ Deployment abgeschlossen (%s)\033[0m\n' "$STAMP"
