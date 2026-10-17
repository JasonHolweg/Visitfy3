#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════
#  Visitfy3 – ops/visitfy-deploy.sh  (läuft auf dem Mac, nutzt deinen SSH-Zugang)
#
#  bash ~/scratch/visitfy-deploy.sh            → prüfen, sichern, einspielen, testen
#  MODE=check bash ~/scratch/visitfy-deploy.sh → nur prüfen, nichts ändern
#
#  Ablauf auf dem Server (ops/remote-apply.sh):
#   1. Konfliktprüfung gegen den Stand, auf dem das Paket aufbaut
#   2. Sicherung aller betroffenen Dateien nach /root/visitfy-backups/<zeit>/
#   3. Einspielen + gezielte Datenanpassung (SEO-Titel, Intro, robots.txt, Rechtsseiten)
#   4. Live-Prüfung der wichtigsten Adressen – bei Fehlern automatisches Zurückrollen
# ══════════════════════════════════════════════════════════════
set -euo pipefail

HOST="${1:-root@visitfy.de}"
PKG="${PKG:-$HOME/scratch/visitfy-phase0.tgz}"
MODE="${MODE:-apply}"
ROOT="${ROOT:-}"

[ -f "$PKG" ] || { echo "✗ Paket nicht gefunden: $PKG"; exit 1; }

if [ -z "$ROOT" ]; then
  ROOT="$(ssh "$HOST" 'bash -s' <<'REMOTE'
for f in /etc/apache2/sites-enabled/* /etc/httpd/conf.d/*.conf; do
  [ -f "$f" ] || continue
  grep -hiE '^[[:space:]]*DocumentRoot' "$f" | awk '{print $2}' | tr -d '"'
done | sort -u | while read -r d; do
  if [ -f "$d/pages/partner-handler.php" ]; then echo "${d%/}"; fi
done | sort -u
REMOTE
)"
  if [ "$(printf '%s\n' "$ROOT" | grep -c .)" != "1" ]; then
    echo "✗ Webverzeichnis nicht eindeutig: $ROOT"
    echo "  Aufruf mit Pfad: ROOT=/pfad/zum/webroot bash ~/scratch/visitfy-deploy.sh"
    exit 1
  fi
fi
echo "✓ Webverzeichnis: $ROOT"

TMP="/tmp/visitfy-deploy-$(date +%Y%m%d%H%M%S)"
echo "→ Lade Paket hoch …"
ssh "$HOST" "mkdir -p '$TMP' && tar xzf - -C '$TMP'" < "$PKG"

echo "→ Starte auf dem Server ($MODE) …"
set +e
ssh "$HOST" "bash '$TMP/_deploy/remote-apply.sh' '$ROOT' '$TMP' '$MODE'"
rc=$?
set -e
ssh "$HOST" "rm -rf -- '$TMP'" || true

case "$rc" in
  0) echo "✓ Erledigt. Gib Claude kurz Bescheid." ;;
  3) echo "✗ Abgebrochen: Auf dem Server wurde seit dem Abgleich etwas geändert. Nichts verändert – sag Claude Bescheid." ;;
  7) echo "✗ Eine Prüfung ist fehlgeschlagen, alter Stand wurde wiederhergestellt. Ausgabe oben an Claude schicken." ;;
  *) echo "✗ Fehler (Code $rc). Ausgabe oben an Claude schicken." ;;
esac
exit "$rc"
