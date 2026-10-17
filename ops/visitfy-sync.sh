#!/usr/bin/env bash
# visitfy-sync.sh – holt den aktuellen Live-Stand von visitfy.de nach ~/scratch.
# Nur lesend: auf dem Server wird nichts geändert.
# Listet zusätzlich Websites sowie Datenbank- und Tabellennamen (keine Inhalte), um die Rundgang-Zahl im CRM zu finden.
# Zugangsdaten (config.*.php, *.env, .htpasswd) und Kundenanfragen (inquiries.json) werden NICHT kopiert.
# Aufruf: bash ~/scratch/visitfy-sync.sh [user@host] [/pfad/zum/webroot]
set -euo pipefail

HOST="${1:-root@visitfy.de}"
ROOT="${2:-}"
OUT="$HOME/scratch"

if [ -z "$ROOT" ]; then
  echo "→ Suche das Webverzeichnis von visitfy.de auf $HOST …"
  ROOT="$(ssh "$HOST" 'bash -s' <<'REMOTE'
for f in /etc/apache2/sites-enabled/* /etc/httpd/conf.d/*.conf; do
  [ -f "$f" ] || continue
  grep -hiE '^[[:space:]]*DocumentRoot' "$f" | awk '{print $2}' | tr -d '"'
done | sort -u | while read -r d; do
  if [ -f "$d/pages/partner-handler.php" ]; then echo "${d%/}"; fi
done | sort -u
REMOTE
)"
  COUNT="$(printf '%s\n' "$ROOT" | grep -c . || true)"
  if [ "$COUNT" != "1" ]; then
    echo "✗ Webverzeichnis nicht eindeutig gefunden ($COUNT Treffer):"
    printf '%s\n' "$ROOT"
    echo "  Bitte mit Pfad aufrufen: bash ~/scratch/visitfy-sync.sh $HOST /pfad/zum/webroot"
    exit 1
  fi
fi
echo "✓ Webverzeichnis: $ROOT"

echo "→ Sammle Server-Infos …"
ssh "$HOST" "ROOT='$ROOT' bash -s" > "$OUT/visitfy-live-info.txt" 2>&1 <<'REMOTE' || true
echo "## Webroot: $ROOT"
echo "## Stand: $(date -Is)"
echo; echo "## Apache / PHP"
(apache2 -v || httpd -v) 2>/dev/null | head -1
php -v 2>/dev/null | head -1
echo; echo "## Apache-Module"
(apache2ctl -M || httpd -M) 2>/dev/null | grep -iE 'rewrite|headers|expires|deflate|brotli|http2|ssl|php' | tr -s ' ' | tr '\n' ' '; echo
echo; echo "## PHP-Erweiterungen"
php -m 2>/dev/null | grep -iE 'sqlite|pdo|curl|mbstring|intl|gd|imagick|zip|opcache' | tr '\n' ' '; echo
echo; echo "## VHosts mit visitfy (ohne Kommentare und Umgebungsvariablen)"
for f in $(grep -rliE 'visitfy' /etc/apache2/sites-enabled/ /etc/httpd/conf.d/ 2>/dev/null); do
  echo "### $f"
  grep -vE '^[[:space:]]*(#|$)' "$f" | grep -viE 'SetEnv|PassEnv|password|secret|token|apikey|api_key'
done
echo; echo "## Statuscodes (vom Server aus)"
for u in http://visitfy.de/ http://www.visitfy.de/ https://www.visitfy.de/ https://visitfy.de/ https://visitfy.de/index.php https://visitfy.de/de https://visitfy.de/pages/partner.php http://visitfy.de/pages/kontakt.php https://visitfy.de/robots.txt https://visitfy.de/sitemap.xml https://visitfy.de/gibt-es-nicht; do
  printf '%-45s ' "$u"; curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' --max-time 10 "$u" || echo "fehler"
done
echo; echo "## Response-Header Startseite"
curl -sI --max-time 10 https://visitfy.de/ | grep -iE '^(server|cf-|x-|content-encoding|cache-control|strict|vary|link)' || true
echo; echo "## Zertifikate"
(certbot certificates 2>/dev/null | grep -iE -A3 'visitfy') || echo "certbot: keine Angaben"
echo; echo "## Datenbank / Docker vorhanden?"
for b in mysql mariadb sqlite3 docker; do printf '%s: ' "$b"; command -v "$b" || echo nein; done
echo; echo "## Besitzer/Rechte im Webroot"
stat -c '%U:%G %a %n' "$ROOT" "$ROOT/index.php" "$ROOT/assets/data" 2>/dev/null || true
echo; echo "## Git im Webroot?"
if [ -d "$ROOT/.git" ]; then (cd "$ROOT" && git log --oneline -3 && git status --short | head -40); else echo "kein .git"; fi
echo; echo "## Alle Websites auf dem Server (Konfigdatei: ServerName/Alias/DocumentRoot)"
for f in /etc/apache2/sites-enabled/* /etc/httpd/conf.d/*.conf; do
  [ -f "$f" ] || continue
  printf '%s: ' "$(basename "$f")"
  awk 'tolower($1)=="servername"||tolower($1)=="serveralias"||tolower($1)=="documentroot"{printf "%s %s  ", $1, $2} END{print ""}' "$f"
done
echo; echo "## Datenbanken und Tabellen (nur Namen und ungefähre Zeilenzahl, keine Inhalte)"
if command -v mysql >/dev/null 2>&1; then
  mysql --batch --skip-column-names -e "SELECT table_schema, table_name, table_rows FROM information_schema.tables WHERE table_schema NOT IN ('mysql','information_schema','performance_schema','sys') ORDER BY table_schema, table_name" 2>&1 | head -400
  echo; echo "## Spalten möglicher Rundgang-Tabellen (nur Namen)"
  mysql --batch --skip-column-names -e "SELECT table_schema, table_name, column_name, data_type FROM information_schema.columns WHERE table_schema NOT IN ('mysql','information_schema','performance_schema','sys') AND table_name REGEXP 'tour|rundgang|projekt|project|auftrag|order|deal|lead|kunde|customer|company|firma|invoice|rechnung|scan|space' ORDER BY table_schema, table_name, ordinal_position" 2>&1 | head -400
else
  echo "mysql-Client nicht gefunden"
fi

echo; echo "## Dateien (neueste zuerst, ohne Zugangsdaten)"
cd "$ROOT" && find . -type f ! -path './.git/*' ! -name 'config.*.php' ! -name 'inquiries.json' ! -name '*.env' -printf '%TY-%Tm-%Td %TH:%TM %9s %p\n' | sort -r | head -250
REMOTE

echo "→ Lade die Live-Dateien (ohne Zugangsdaten und Kundenanfragen) …"
ssh "$HOST" "tar czf - -C '$ROOT' --exclude=./.git --exclude='config.*.php' --exclude='inquiries.json' --exclude='*.env' --exclude='.htpasswd' ." > "$OUT/visitfy-live.tgz"
echo "✓ Fertig: $(du -h "$OUT/visitfy-live.tgz" | cut -f1) in ~/scratch/visitfy-live.tgz"
echo "  Infos: ~/scratch/visitfy-live-info.txt – gib Claude jetzt Bescheid."
