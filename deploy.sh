#!/usr/bin/env bash
#
# HandInHand auf https://handinhand.carmen-vavra.at bringen (netcup/Plesk).
#
# Eine Datei für alles. Ausführen per Doppelklick (Git Bash) oder im Terminal:
#
#   bash deploy.sh                  Tests -> Build -> Upload -> composer -> Migrationen -> Prüfung der Live-Seite
#                                   (richtet den Server beim ersten Mal selbst ein)
#   bash deploy.sh --skip-tests     dasselbe ohne lokale Prüfungen (Tests, Pint, Larastan)
#   bash deploy.sh check            Verbindung, Server und Live-Seite prüfen (ändert nichts)
#   bash deploy.sh link             Freischalt-Link für das Handy der Kleinen anzeigen
#   bash deploy.sh env KEY=Wert     Wert in die Server-.env schreiben (z. B. LEGAL_NAME="…")
#   bash deploy.sh env KEY          dasselbe, Wert wird verdeckt abgefragt (für Passwörter)
#   bash deploy.sh artisan <befehl> Artisan-Befehl auf dem Server ausführen (z. B. "about")
#   bash deploy.sh logs             letzte Zeilen aus storage/logs/laravel.log
#
# Der Code wird vom PC hochgeladen (git archive über SSH); der Server braucht keinen GitHub-Zugriff,
# weder Node noch Git. Anmeldung nur per SSH-Schlüssel, in dieser Datei stehen keine Passwörter.
# Geheimnisse (Admin-Startpasswort, Kinder-Token) werden zufällig erzeugt, nur in die Server-.env bzw.
# in zwei lokale, nicht eingecheckte Textdateien geschrieben (siehe unten).
#
# Nie überschrieben wird auf dem Server: .env, storage/ (Videos, Fotos, Logs, Sessions), database/database.sqlite.
#
# Einmalig in Plesk (Websites & Domains -> handinhand.carmen-vavra.at):
#   - Hosting-Einstellungen -> Dokumentenstamm: handinhand.carmen-vavra.at/app/public
#   - PHP-Einstellungen -> PHP 8.3 (mind. 8.2), open_basedir wie beim ServerDashboard,
#     post_max_size / upload_max_filesize mindestens 40M (siehe public/.user.ini; falls Plesk sie überstimmt, dort setzen)
#   - SSL/TLS: Let's-Encrypt-Zertifikat, HTTP -> HTTPS umleiten (Kamera/Mikrofon funktionieren nur über HTTPS)
# Keine geplanten Aufgaben, kein Queue-Worker, kein WebSocket nötig.

set -Eeuo pipefail

# --- Einstellungen ------------------------------------------------------------------------------------
DEPLOY_HOST="handinhand.carmen-vavra.at"
DEPLOY_USER="hosting171221"
DEPLOY_PORT="22"
DEPLOY_KEY="$HOME/.ssh/cleanstart_netcup"
DEPLOY_PATH="/handinhand.carmen-vavra.at/app"   # Sicht der SSH-Sitzung; Dokumentenstamm = <DEPLOY_PATH>/public
DEPLOY_URL="https://handinhand.carmen-vavra.at"
DEPLOY_PHP="php"
DEPLOY_BRANCH="main"

# Nur für die Erstinstallation (danach in der App bzw. per "deploy.sh env" änderbar)
ADMIN_NAME="${DEPLOY_ADMIN_NAME:-Tante Carmen}"
ADMIN_EMAIL="${DEPLOY_ADMIN_EMAIL:-admin@carmen-vavra.at}"   # nur Login-Name, es werden keine Mails verschickt
ADMIN_PASSWORD_FILE="admin-startpasswort.txt"                # lokal, nicht im Git
CHILD_LINK_FILE="kinder-handy-link.txt"                      # lokal, nicht im Git
# ------------------------------------------------------------------------------------------------------

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

# Doppelklick: Fenster am Ende offen lassen (nicht bei Aufrufen aus Skripten oder ohne Terminal).
finish() {
    local rc=$?
    if [[ $rc -eq 0 ]]; then
        printf '\n\033[1;32mFertig.\033[0m\n'
    else
        printf '\n\033[1;31mNICHT erfolgreich (Fehlercode %s).\033[0m\n' "$rc"
    fi
    if [[ -t 0 && -t 1 && -z "${DEPLOY_NO_PAUSE:-}" ]]; then
        read -rn1 -p 'Taste drücken zum Schließen …' || true
        echo
    fi
}
trap finish EXIT

step() { printf '\n\033[1;36m> %s\033[0m\n' "$*"; }
note() { printf '  %s\n' "$*"; }
warn() { printf '  \033[1;33mHinweis: %s\033[0m\n' "$*"; }
fail() { printf '\n\033[1;31mFEHLER: %s\033[0m\n' "$*" >&2; exit 1; }

DEPLOY_KEY="${DEPLOY_KEY/#\~/$HOME}"
[[ -f "$DEPLOY_KEY" ]] || fail "SSH-Schlüssel $DEPLOY_KEY fehlt."

SSH_OPTS=(-i "$DEPLOY_KEY" -p "$DEPLOY_PORT" -o IdentitiesOnly=yes -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=15)
TARGET="$DEPLOY_USER@$DEPLOY_HOST"

remote() { ssh "${SSH_OPTS[@]}" "$TARGET" "$@"; }
in_app() { remote "cd $DEPLOY_PATH && $*"; }
artisan() { in_app "$DEPLOY_PHP artisan $*"; }
http_status() { curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$1" | tr -d '\r' || true; }

# Setzt Werte in der Server-.env (JSON über stdin, Werte erscheinen nie in Ausgabe oder Prozessliste).
# APP_KEY wird nie überschrieben. Ausgegeben werden nur die Namen geänderter Einträge.
SET_ENV_PHP='<?php
$values = json_decode((string) stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$lines = is_file(".env") ? (file(".env", FILE_IGNORE_NEW_LINES) ?: []) : [];
$format = static fn (string $v): string => preg_match("/^[A-Za-z0-9_.:@\/+-]*$/", $v) ? $v : "\"".str_replace(["\\", "\""], ["\\\\", "\\\""], $v)."\"";
$changed = [];
foreach ($values as $key => $value) {
    if ($key === "APP_KEY") { continue; }
    $entry = $key."=".$format((string) $value);
    $found = false;
    foreach ($lines as $i => $line) {
        if (str_starts_with($line, $key."=")) {
            $found = true;
            if ($line !== $entry) { $lines[$i] = $entry; $changed[] = $key; }
            break;
        }
    }
    if (! $found) { $lines[] = $entry; $changed[] = $key; }
}
file_put_contents(".env", implode("\n", $lines)."\n");
chmod(".env", 0600);
echo $changed === [] ? "keine Änderungen\n" : implode(", ", $changed)."\n";
'

# $1 = JSON-Objekt {"KEY":"Wert", …}
set_remote_env() {
    printf '%s' "$SET_ENV_PHP" | remote "cat > $DEPLOY_PATH/storage/set-env.php && chmod 600 $DEPLOY_PATH/storage/set-env.php"
    printf '%s' "$1" | in_app "$DEPLOY_PHP storage/set-env.php; rc=\$?; rm -f storage/set-env.php; exit \$rc" | sed 's/^/  geändert: /'
}

check_server() {
    remote 'true' || fail "Keine SSH-Verbindung zu $TARGET. Ist $DEPLOY_KEY.pub im Webhosting eingetragen?"
    remote "$DEPLOY_PHP -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);'" || fail "PHP 8.2 oder neuer nötig (DEPLOY_PHP oben im Skript anpassen)."
    local missing
    missing="$(remote "$DEPLOY_PHP -r 'foreach ([\"pdo_sqlite\", \"mbstring\", \"fileinfo\", \"openssl\", \"tokenizer\", \"xml\", \"ctype\", \"curl\", \"gd\"] as \$e) { if (! extension_loaded(\$e)) { echo \$e.\" \"; } }'")"
    [[ -z "${missing// /}" ]] || fail "Diese PHP-Erweiterungen fehlen auf dem Server: $missing"
    remote "$DEPLOY_PHP -r 'exit(extension_loaded(\"exif\") ? 0 : 1);'" \
        || warn "PHP-Erweiterung exif fehlt: Handy-Fotos werden dann nicht automatisch gedreht."
}

# Composer: vorhandenes "composer", sonst composer.phar neben dem Projektordner (mit geprüfter Prüfsumme).
resolve_composer() {
    if remote "command -v composer >/dev/null 2>&1"; then
        COMPOSER="composer"
        return
    fi

    local phar
    phar="$(dirname "$DEPLOY_PATH")/composer.phar"
    if ! remote "test -f $phar"; then
        step "Composer herunterladen ($phar)"
        remote "$DEPLOY_PHP -r '
            \$url = \"https://getcomposer.org/download/latest-stable/composer.phar\";
            \$phar = file_get_contents(\$url);
            \$sum = trim(explode(\" \", file_get_contents(\$url.\".sha256sum\"))[0]);
            if (\$phar === false || ! hash_equals(\$sum, hash(\"sha256\", \$phar))) { fwrite(STDERR, \"Pruefsumme von composer.phar stimmt nicht.\n\"); exit(1); }
            file_put_contents(\"$phar\", \$phar);
        '" || fail "Composer konnte nicht geladen werden."
    fi
    COMPOSER="$DEPLOY_PHP $phar"
}

# Server und GitHub sollen denselben Code zeigen: alles committet, gepusht, richtiger Branch.
require_clean_pushed_state() {
    step "Lokalen Stand prüfen"
    [[ -z "$(git status --porcelain)" ]] || fail "Es gibt nicht committete Änderungen. Bitte erst committen und pushen."
    [[ "$(git rev-parse --abbrev-ref HEAD)" == "$DEPLOY_BRANCH" ]] || fail "Es wird nur der Branch $DEPLOY_BRANCH deployt (aktuell: $(git rev-parse --abbrev-ref HEAD))."
    git fetch --quiet origin "$DEPLOY_BRANCH"
    [[ "$(git rev-parse HEAD)" == "$(git rev-parse "origin/$DEPLOY_BRANCH")" ]] || fail "Der lokale Stand ist nicht auf GitHub (git push fehlt?)."
    note "Commit $(git log --oneline -1)"
}

# Committeten Code hochladen und seit dem letzten Deploy gelöschte Dateien entfernen. Dateien, die nur auf
# dem Server existieren (.env, vendor/, storage/, database.sqlite, public/build/), bleiben unberührt.
# Mockups, Konzept, Tests und lokale Skripte sind per .gitattributes (export-ignore) ausgenommen.
upload_code() {
    local revision previous deleted
    revision="$(git rev-parse HEAD)"
    previous="$(remote "cat $DEPLOY_PATH/REVISION 2>/dev/null || true" | tr -d '\r\n')"

    step "Code hochladen ($(git log --oneline -1))"
    git archive --format=tar HEAD | gzip -c | remote "mkdir -p $DEPLOY_PATH && tar xzf - -C $DEPLOY_PATH"

    if [[ -n "$previous" ]] && git cat-file -e "$previous^{commit}" 2>/dev/null; then
        deleted="$(git diff --name-only --no-renames --diff-filter=D "$previous" "$revision")"
        if [[ -n "$deleted" ]]; then
            note "Entferne gelöschte Dateien:"
            printf '    %s\n' $deleted
            printf '%s\n' "$deleted" | remote "cd $DEPLOY_PATH && xargs -d '\n' rm -f --"
        fi
    fi

    remote "echo $revision > $DEPLOY_PATH/REVISION"
}

build_assets() {
    step "Styles und Skripte bauen"
    [[ -d node_modules ]] || npm ci --no-audit --no-fund
    npm run build --silent
}

upload_build() {
    step "public/build hochladen"
    tar czf - -C public build | remote "cd $DEPLOY_PATH/public && rm -rf build.neu build.alt && mkdir build.neu && tar xzf - -C build.neu --strip-components=1 && { test -d build && mv build build.alt || true; } && mv build.neu build && rm -rf build.alt && rm -f hot"
}

install_dependencies() {
    step "Abhängigkeiten (composer)"
    in_app "COMPOSER_MEMORY_LIMIT=-1 $COMPOSER install --no-dev --optimize-autoloader --no-interaction --no-progress --quiet"
}

prepare_runtime() {
    step "Ordner und Rechte"
    # Plesk legt beim Ändern des Dokumentenstamms eine Platzhalter-index.html an; sie hätte Vorrang vor index.php.
    in_app "rm -f public/index.html"
    in_app "mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/private bootstrap/cache database \
        && chmod -R u+rwX storage bootstrap/cache \
        && { test -f database/database.sqlite || touch database/database.sqlite; } && chmod 664 database/database.sqlite"
}

# Erstinstallation: Produktions-.env schreiben (APP_KEY erzeugt danach artisan key:generate).
# Der Kinder-Token ist zufällig und steht nur in der Server-.env und in $CHILD_LINK_FILE (lokal, nicht im Git).
init_env() {
    local token
    token="$(php -r 'echo bin2hex(random_bytes(24));')"

    step ".env anlegen (Erstinstallation)"
    remote "cat > $DEPLOY_PATH/.env && chmod 600 $DEPLOY_PATH/.env" <<ENV
APP_NAME=HandInHand
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=$DEPLOY_URL
APP_TIMEZONE=Europe/Vienna

APP_LOCALE=de
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=de_AT

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

BROADCAST_CONNECTION=log
CACHE_STORE=database
FILESYSTEM_DISK=local
# Keine Hintergrund-Jobs nötig: alles läuft direkt im Request.
QUEUE_CONNECTION=sync

MAIL_MAILER=log

# --- HandInHand ---
HIH_AUNT_NAME="$ADMIN_NAME"
# Nur Geräte mit dem Freischalt-Link (bash deploy.sh link) kommen in den Kinderbereich.
HIH_CHILD_TOKEN=$token
HIH_MAX_VIDEO_MB=30
HIH_MAX_AUDIO_MB=5
HIH_MAX_IMAGE_MB=12
HIH_MAX_RECORD_SECONDS=90
HIH_POLL_SECONDS=20

# Impressum & Datenschutz (mit "bash deploy.sh env LEGAL_NAME=…" usw. ausfüllen)
LEGAL_NAME=
LEGAL_STREET=
LEGAL_CITY=
LEGAL_COUNTRY="Österreich"
LEGAL_EMAIL=
LEGAL_PHONE=
LEGAL_VAT_ID=

VITE_APP_NAME="\${APP_NAME}"
ENV

    printf 'HandInHand – Freischalt-Link für das Handy der Kleinen (%s)\n\n%s/zugang/%s\n\nEinmalig auf dem Handy öffnen (am besten in Chrome/Safari), danach "Zum Startbildschirm hinzufügen".\nDiesen Link nicht weitergeben – wer ihn kennt, kommt in den Kinderbereich.\n' \
        "$(date '+%d.%m.%Y %H:%M')" "$DEPLOY_URL" "$token" > "$CHILD_LINK_FILE"
    note "Kinder-Link gespeichert in $ROOT/$CHILD_LINK_FILE (nicht im Git)"
}

# Admin-Konto anlegen, solange es keines gibt. Das Startpasswort wird zufällig erzeugt, über stdin übergeben
# (nicht in der Prozessliste) und nur lokal in $ADMIN_PASSWORD_FILE gespeichert.
seed_database() {
    local users password
    users="$(in_app "$DEPLOY_PHP -r 'echo (new PDO(\"sqlite:database/database.sqlite\"))->query(\"select count(*) from users\")->fetchColumn();'" | tr -d '\r\n ')"

    if [[ "$users" != "0" ]]; then
        note "Admin-Konto vorhanden – keine Startdaten nötig"
        return
    fi

    step "Admin-Konto anlegen (Erstinstallation)"
    password="$(php -r 'echo "Hih-".bin2hex(random_bytes(8));')"
    printf '%s\n' "$password" | in_app "IFS= read -r ADMIN_PASSWORD && export ADMIN_PASSWORD ADMIN_EMAIL='$ADMIN_EMAIL' ADMIN_NAME='$ADMIN_NAME' && $DEPLOY_PHP artisan db:seed --force" | sed 's/^/  /'

    printf 'HandInHand – Admin-Startzugang (%s)\n\nAdresse:  %s/admin/login\nE-Mail:   %s\nPasswort: %s\n\nBitte nach dem ersten Login unter Einstellungen -> "Passwort ändern" ändern und diese Datei löschen.\n' \
        "$(date '+%d.%m.%Y %H:%M')" "$DEPLOY_URL" "$ADMIN_EMAIL" "$password" > "$ADMIN_PASSWORD_FILE"
    note "Startpasswort gespeichert in $ROOT/$ADMIN_PASSWORD_FILE (nicht im Git)"
}

# Prüft die Live-Seite von außen: richtige App, Zugangsschutz, keine offenen internen Dateien.
verify() {
    local status location body
    step "Prüfung: $DEPLOY_URL"

    body="$(curl -s --max-time 20 "$DEPLOY_URL/manifest.webmanifest" || true)"
    if [[ "$body" != *'"HandInHand"'* ]]; then
        in_app "tail -n 25 storage/logs/laravel*.log 2>/dev/null || true"
        fail "$DEPLOY_URL liefert nicht HandInHand. Zeigt der Dokumentenstamm in Plesk auf ${DEPLOY_PATH#/}/public? Bei leerem Fehler 500: open_basedir prüfen (Kopf dieser Datei)."
    fi
    note "Manifest: erreichbar"

    status="$(http_status "$DEPLOY_URL/up")"
    [[ "$status" == "200" ]] || fail "$DEPLOY_URL/up antwortet mit $status statt 200."

    read -r status location < <(curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' --max-time 20 "$DEPLOY_URL/admin" | tr -d '\r')
    [[ "$status" == "302" && "$location" == */admin/login ]] || fail "$DEPLOY_URL/admin antwortet ohne Anmeldung mit $status ($location) statt mit der Weiterleitung zum Login."
    note "Admin ohne Anmeldung: Weiterleitung zum Login"

    if in_app "grep -qE '^HIH_CHILD_TOKEN=.+' .env"; then
        status="$(http_status "$DEPLOY_URL/home")"
        [[ "$status" == "403" ]] || fail "$DEPLOY_URL/home antwortet ohne Freischaltung mit $status statt 403 – ist der Zugangsschutz aktiv (config:cache veraltet? bash deploy.sh artisan optimize:clear)?"
        note "Kinderbereich ohne Freischaltung: gesperrt"
    else
        warn "HIH_CHILD_TOKEN ist leer: der Kinderbereich ist für jeden offen (bash deploy.sh env HIH_CHILD_TOKEN=…)."
    fi

    for path in /admin/login /impressum /datenschutz /offline /service-worker.js /app-icons/icon-192.png /app-icons/icon-512.png; do
        status="$(http_status "$DEPLOY_URL$path")"
        [[ "$status" == "200" ]] || fail "$DEPLOY_URL$path antwortet mit $status statt 200."
    done
    note "Login, Impressum, Datenschutz, Offline-Seite, Service Worker und Icons: erreichbar"

    for path in /.env /database/database.sqlite /artisan /composer.json /storage/app/private; do
        status="$(http_status "$DEPLOY_URL$path")"
        [[ "$status" == "404" || "$status" == "403" ]] || fail "$DEPLOY_URL$path liefert $status – der Dokumentenstamm muss auf …/app/public zeigen."
    done
    note "Interne Dateien: nicht öffentlich"

    [[ "$(http_status "http://${DEPLOY_URL#https://}/admin/login")" != "200" ]] \
        || warn "http:// wird nicht auf https:// umgeleitet (Plesk -> SSL/TLS). Ohne HTTPS laufen Kamera, Mikrofon und Login nicht."
    [[ "$(curl -s --max-time 20 "$DEPLOY_URL/impressum" || true)" != *"[LEGAL_"* ]] \
        || warn "Impressum ist unvollständig (Platzhalter [LEGAL_…]): bash deploy.sh env LEGAL_NAME=\"…\" LEGAL_STREET=\"…\" LEGAL_CITY=\"…\" LEGAL_EMAIL=… LEGAL_PHONE=…"
}

cmd_deploy() {
    local skip_tests="${1:-}" fresh=false

    require_clean_pushed_state

    if [[ "$skip_tests" != "--skip-tests" ]]; then
        step "Tests, Code-Stil, statische Analyse"
        php artisan test --compact || fail "Tests sind fehlgeschlagen – nichts deployt."
        ./vendor/bin/pint --test || fail "Code-Stil (Pint) verletzt – nichts deployt. Mit ./vendor/bin/pint korrigieren."
        ./vendor/bin/phpstan analyse --no-progress --memory-limit=512M || fail "Larastan meldet Fehler – nichts deployt."
    fi

    build_assets

    step "Server prüfen ($TARGET)"
    check_server
    resolve_composer
    remote "test -f $DEPLOY_PATH/.env" || fresh=true

    if $fresh; then
        note "Erstinstallation"
        upload_code
        init_env
        install_dependencies
        artisan "key:generate --force"
    else
        step "Wartungsmodus an"
        artisan "down --retry=15" || true
        trap 'printf "\n\033[1;31mDeploy abgebrochen – Wartungsmodus wird beendet.\033[0m\n" >&2; artisan up || true' ERR
        upload_code
        install_dependencies
    fi

    prepare_runtime
    upload_build

    step "Datenbank und Cache"
    artisan "migrate --force"
    seed_database
    # Nur leeren, nicht cachen: SSH sieht /handinhand…/app, der Webserver /var/www/vhosts/…/app.
    artisan "optimize:clear" >/dev/null

    if ! $fresh; then
        step "Wartungsmodus aus"
        artisan up
        trap - ERR
    fi

    verify
    note "Online: $DEPLOY_URL"
    if $fresh; then
        warn "Nächste Schritte: 1) Admin-Login aus $ADMIN_PASSWORD_FILE, Passwort ändern  2) Link aus $CHILD_LINK_FILE auf dem Handy der Kleinen öffnen  3) Impressum-Daten setzen (bash deploy.sh env LEGAL_NAME=…)"
    fi
}

cmd_check() {
    step "Verbindung und Programme ($TARGET)"
    check_server
    remote "$DEPLOY_PHP -v | head -1"
    note "PHP-Erweiterungen: vollständig"

    step "Projektordner $DEPLOY_PATH"
    if remote "test -f $DEPLOY_PATH/artisan"; then
        in_app "echo \"  Stand: \$(cat REVISION 2>/dev/null || echo unbekannt)\"; test -f .env && echo '  .env vorhanden' || echo '  .env fehlt'"
        verify
    else
        note "noch nicht eingerichtet – das erledigt der erste Deploy automatisch."
        note "HTTP-Status $DEPLOY_URL/: $(http_status "$DEPLOY_URL/")"
    fi
}

# Zeigt den Freischalt-Link (aus der Server-.env) – das Token wird nur auf diesem Bildschirm ausgegeben.
cmd_link() {
    local token
    token="$(in_app "grep -E '^HIH_CHILD_TOKEN=' .env | cut -d= -f2- | tr -d '\"'" | tr -d '\r\n')"
    [[ -n "$token" ]] || fail "Auf dem Server ist kein HIH_CHILD_TOKEN gesetzt (bash deploy.sh env HIH_CHILD_TOKEN=…)."
    note "Freischalt-Link für das Handy der Kleinen (einmalig öffnen, nicht weitergeben):"
    note "$DEPLOY_URL/zugang/$token"
}

# bash deploy.sh env KEY=Wert [KEY2=Wert2 …] | bash deploy.sh env KEY (Wert wird verdeckt abgefragt)
cmd_env() {
    [[ $# -gt 0 ]] || fail "Aufruf: bash deploy.sh env KEY=Wert … oder bash deploy.sh env KEY"
    remote "test -f $DEPLOY_PATH/.env" || fail "Auf dem Server gibt es noch keine .env – zuerst deployen."

    local pairs=() arg value
    for arg in "$@"; do
        if [[ "$arg" == *=* ]]; then
            pairs+=("${arg%%=*}" "${arg#*=}")
        else
            [[ -t 0 ]] || fail "Für $arg fehlt der Wert (KEY=Wert)."
            read -rsp "Wert für $arg (wird nicht angezeigt): " value
            echo
            pairs+=("$arg" "$value")
        fi
    done

    for ((i = 0; i < ${#pairs[@]}; i += 2)); do
        [[ "${pairs[i]}" =~ ^[A-Z][A-Z0-9_]*$ ]] || fail "Ungültiger Name: ${pairs[i]}"
    done

    step "Server-.env aktualisieren"
    set_remote_env "$(php -r '$v = []; for ($i = 1; $i < $argc; $i += 2) { $v[$argv[$i]] = $argv[$i + 1]; } echo json_encode((object) $v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);' -- "${pairs[@]}")"
    artisan "optimize:clear" >/dev/null
}

case "${1:-deploy}" in
    deploy | --skip-tests) cmd_deploy "${1:-}" ;;
    check) cmd_check ;;
    link) cmd_link ;;
    env) shift; cmd_env "$@" ;;
    artisan) shift; [[ $# -gt 0 ]] || fail "Aufruf: bash deploy.sh artisan <befehl>"; artisan "$*" ;;
    logs) in_app "tail -n ${2:-50} storage/logs/laravel.log 2>/dev/null || echo 'Kein Log vorhanden.'" ;;
    *) fail "Unbekannter Befehl: $1 (deploy | --skip-tests | check | link | env | artisan | logs)" ;;
esac
