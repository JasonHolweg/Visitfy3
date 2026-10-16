#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════
#  Visitfy3 – ops/remote-apply.sh
#  Läuft AUF DEM SERVER (als root), aufgerufen von ops/visitfy-deploy.sh.
#
#  remote-apply.sh <webroot> <entpacktes-paket> [check|apply]
#
#  1. Prüft jede Datei aus _deploy/MANIFEST: Der Live-Stand muss dem Stand entsprechen,
#     gegen den das Paket gebaut wurde (sonst wurde seitdem auf dem Server etwas geändert → Abbruch).
#  2. Sichert alle betroffenen Dateien nach /root/visitfy-backups/<zeit>/.
#  3. Kopiert die Dateien, passt Daten an (_deploy/patch-data.php), setzt Besitzer/Rechte.
#  4. Prüft die Live-Seite. Schlägt eine Kernprüfung fehl, wird automatisch zurückgerollt.
# ══════════════════════════════════════════════════════════════
set -uo pipefail

ROOT="${1:?Webroot fehlt}"
PKG="${2:?Paketverzeichnis fehlt}"
MODE="${3:-apply}"
SITE="${VISITFY_SITE:-https://visitfy.de}"
MANIFEST="$PKG/_deploy/MANIFEST"
TS="$(date +%Y%m%d-%H%M%S)"
BACKUP="/root/visitfy-backups/$TS"

red()   { printf '\033[31m%s\033[0m\n' "$*"; }
green() { printf '\033[32m%s\033[0m\n' "$*"; }
info()  { printf '  %s\n' "$*"; }

[ -f "$ROOT/index.php" ] || { red "✗ $ROOT sieht nicht nach dem Visitfy-Webroot aus."; exit 2; }
[ -f "$MANIFEST" ] || { red "✗ MANIFEST fehlt im Paket."; exit 2; }

# ── 1) Konfliktprüfung ──────────────────────────────────────
conflicts=0
files=()
while IFS=$'\t' read -r path expect new; do
  [ -z "${path:-}" ] && continue
  files+=("$path")
  live="-"
  [ -f "$ROOT/$path" ] && live="$(sha256sum "$ROOT/$path" | cut -d' ' -f1)"
  if [ "$live" != "$expect" ] && [ "$live" != "$new" ]; then
    red "✗ Konflikt: $path wurde auf dem Server geändert, seit das Paket gebaut wurde."
    conflicts=$((conflicts + 1))
  fi
done < "$MANIFEST"

if [ "$conflicts" -gt 0 ]; then
  red "Abbruch: $conflicts Datei(en) passen nicht zum erwarteten Stand. Auf dem Server wurde nichts verändert."
  exit 3
fi
green "✓ ${#files[@]} Dateien geprüft, keine Konflikte."
[ "$MODE" = "check" ] && exit 0

# ── 2) Sicherung ────────────────────────────────────────────
mkdir -p "$BACKUP"
existing=()
newfiles=()
for path in "${files[@]}" assets/data/content.json assets/data/script-config.json robots.txt pages/impressum.php pages/datenschutz.php; do
  if [ -f "$ROOT/$path" ]; then existing+=("$path"); else newfiles+=("$path"); fi
done
# Duplikate entfernen
mapfile -t existing < <(printf '%s\n' "${existing[@]}" | sort -u)
( cd "$ROOT" && tar czf "$BACKUP/files.tgz" -- "${existing[@]}" ) || { red "✗ Sicherung fehlgeschlagen – Abbruch."; exit 4; }
printf '%s\n' "${newfiles[@]}" | sort -u > "$BACKUP/new-files.txt"
green "✓ Sicherung: $BACKUP (${#existing[@]} Dateien)"

rollback() {
  red "↺ Rolle zurück …"
  ( cd "$ROOT" && tar xzf "$BACKUP/files.tgz" )
  while IFS= read -r f; do
    [ -n "$f" ] && [ -f "$ROOT/$f" ] && rm -f -- "$ROOT/$f"
  done < "$BACKUP/new-files.txt"
  red "↺ Alter Stand wiederhergestellt (Sicherung bleibt in $BACKUP)."
}

# ── 3) Dateien einspielen ───────────────────────────────────
OWNER="$(stat -c '%U:%G' "$ROOT/index.php")"
PHPUSER=""
[ -f "$ROOT/assets/data/inquiries.json" ] && PHPUSER="$(stat -c '%U' "$ROOT/assets/data/inquiries.json")"
if [ -z "$PHPUSER" ] || [ "$PHPUSER" = "root" ]; then
  PHPUSER="$(ps -eo user=,comm= | awk '$2 ~ /php-fpm|apache2|httpd/ && $1 != "root" {print $1; exit}')"
fi
[ -z "$PHPUSER" ] && PHPUSER="www-data"

for path in "${files[@]}"; do
  dir="$(dirname "$ROOT/$path")"
  if [ ! -d "$dir" ]; then mkdir -p "$dir" && chown "$OWNER" "$dir" && chmod 755 "$dir"; fi
  cp -- "$PKG/$path" "$ROOT/$path" && chown "$OWNER" "$ROOT/$path" && chmod 644 "$ROOT/$path" || { red "✗ Kopieren fehlgeschlagen: $path"; rollback; exit 5; }
done
green "✓ ${#files[@]} Dateien eingespielt (Besitzer $OWNER)"

# Statistik-Ordner: muss für PHP beschreibbar sein
mkdir -p "$ROOT/assets/data/stats"
chown "$PHPUSER" "$ROOT/assets/data/stats" 2>/dev/null || true
chmod 755 "$ROOT/assets/data/stats"
printf 'Require all denied\n' > "$ROOT/assets/data/stats/.htaccess"
chown "$PHPUSER" "$ROOT/assets/data/stats/.htaccess" 2>/dev/null || true
info "Statistik-Ordner gehört $PHPUSER"

# Daten anpassen (SEO-Titel, Intro-Zeiten, robots.txt, Rechtsseiten)
if [ -f "$PKG/_deploy/patch-data.php" ]; then
  php "$PKG/_deploy/patch-data.php" "$ROOT" || { red "✗ Datenanpassung fehlgeschlagen"; rollback; exit 6; }
fi

# OPcache leeren, falls aktiv (sonst laufen evtl. alte PHP-Dateien weiter)
for svc in php8.4-fpm php8.3-fpm php8.2-fpm php8.1-fpm php8.0-fpm php7.4-fpm php-fpm; do
  systemctl is-active --quiet "$svc" 2>/dev/null && systemctl reload "$svc" 2>/dev/null && info "$svc neu geladen"
done
if systemctl is-active --quiet apache2 2>/dev/null; then apachectl -t >/dev/null 2>&1 && systemctl reload apache2 && info "Apache neu geladen"; fi
sleep 1

# ── 4) Live-Prüfung ─────────────────────────────────────────
fails=0
check() { # name url erwarteter_code [muster|ziel-url] [browser]
  local name="$1" url="$2" want="$3" pattern="${4:-}" as="${5:-curl}" body code redirect ok
  body="$(mktemp)"
  if [ "$as" = "browser" ]; then
    # als Browser, damit die Statistik den Aufruf zählt (Pfad wird als /__deploy-check gespeichert)
    code="$(curl -s -o "$body" -w '%{http_code}' --max-time 20 -A 'Mozilla/5.0 (Macintosh; Deploy-Check) AppleWebKit/537.36 Chrome/141.0 Safari/537.36' "$url")"
  else
    code="$(curl -s -o "$body" -w '%{http_code}' --max-time 20 -H 'Cache-Control: no-cache' "$url")"
  fi
  redirect=""
  [ "${code:0:1}" = "3" ] && redirect="$(curl -s -o /dev/null -w '%{redirect_url}' --max-time 20 "$url")"
  ok=1
  [ "$code" = "$want" ] || ok=0
  if [ -n "$pattern" ]; then
    if [ "${want:0:1}" = "3" ]; then
      [ "$redirect" = "$pattern" ] || ok=0
    else
      grep -q -- "$pattern" "$body" || ok=0
    fi
  fi
  if [ "$ok" = "1" ]; then
    green "  ✓ $name: $code ${redirect:+→ $redirect}"
  else
    red "  ✗ $name: $code ${redirect:+→ $redirect} (erwartet $want ${pattern:+/ $pattern})"
    fails=$((fails + 1))
  fi
  rm -f "$body"
}

echo "→ Prüfe die Live-Seite …"
check "Startseite"            "$SITE/"                  200 'application/ld+json'
check "Partner werden"        "$SITE/partner-werden"    200 'pw-hero'
check "Kontakt"               "$SITE/kontakt"           200 '<form'
check "FAQ"                   "$SITE/faq"               200 'FAQPage'
check "Über uns"              "$SITE/ueber-uns"         200 '<h1'
check "Impressum"             "$SITE/impressum"         200 '<h1'
check "Datenschutz"           "$SITE/datenschutz"       200 '<h1'
check "Sitemap"               "$SITE/sitemap.xml"       200 '<urlset'
check "CSS"                   "$SITE/assets/css/style.css" 200
check "Alte FAQ-Adresse"      "$SITE/pages/faq.php"     301 "$SITE/faq"
check "Alte Partner-Adresse"  "$SITE/pages/partner.php" 301 "$SITE/partner-werden"
check "Nicht vorhandene Seite" "$SITE/gibt-es-nicht-$TS" 404
check "Statistik-Testaufruf"  "$SITE/?__deploy=check"   200 'application/ld+json' browser

if [ "$fails" -gt 0 ]; then
  red "✗ $fails Prüfung(en) fehlgeschlagen."
  rollback
  sleep 1
  code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$SITE/")"
  info "Startseite nach dem Zurückrollen: HTTP $code"
  exit 7
fi

# Zusätzliche Hinweise (kein Abbruch)
echo "→ Weitere Prüfungen (nur Hinweise) …"
for u in "http://visitfy.de/" "http://www.visitfy.de/" "https://www.visitfy.de/"; do
  r="$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' --max-time 15 "$u" 2>/dev/null || echo 'Fehler')"
  info "$u → $r"
done
statsfile="$ROOT/assets/data/stats/$(date -u +%Y-%m).jsonl"
if [ -f "$statsfile" ] && grep -q '__deploy-check' "$statsfile"; then
  green "  ✓ Statistik schreibt Daten"
else
  red "  ! Statistik konnte nicht schreiben – Rechte von $ROOT/assets/data/stats prüfen (PHP-Benutzer?)"
fi

# Suchmaschinen über IndexNow informieren (Bing, Yandex …)
if [ -f "$ROOT/tools/indexnow.php" ]; then
  ( cd "$ROOT" && php tools/indexnow.php ) || info "IndexNow-Meldung fehlgeschlagen (nicht schlimm, später erneut: php tools/indexnow.php)"
fi

green "✓ Fertig. Sicherung: $BACKUP"
info "Zurückrollen bei Bedarf: cd $ROOT && tar xzf $BACKUP/files.tgz"
exit 0
