# ══════════════════════════════════════════════════════════════
#  Visitfy3 – ops/visitfy-ssh.sh
#  Gemeinsame SSH-Verbindung für visitfy-sync.sh und visitfy-deploy.sh
#  (wird von beiden eingebunden, nicht direkt aufrufen).
#
#  visitfy.de läuft über Cloudflare. Cloudflare leitet nur Webseiten-Aufrufe weiter,
#  kein SSH – deshalb muss die Verbindung direkt an die IP des Servers gehen.
#  Nach dem ersten erfolgreichen Verbinden wird das Ziel in ~/scratch/.visitfy-ssh gemerkt.
#
#  Eingabe: IP (→ root@IP), root@IP, root@IP:Port oder ein Kürzel aus ~/.ssh/config
# ══════════════════════════════════════════════════════════════

VISITFY_SSH_SAVE="$HOME/scratch/.visitfy-ssh"
VISITFY_SSH_TARGET=""
VISITFY_SSH_LABEL=""
VISITFY_SSH_OPTS=()

vssh() { ssh "${VISITFY_SSH_OPTS[@]}" "$VISITFY_SSH_TARGET" "$@"; }

visitfy_ssh_parse() {
  local t="$1" port="${SSH_PORT:-}" colons
  t="$(printf '%s' "$t" | tr -d '[:space:]')"
  t="${t#ssh://}"
  colons="$(printf '%s' "$t" | tr -cd ':' | wc -c | tr -d ' ')"
  # Port am Ende nur bei genau einem Doppelpunkt (sonst ist es eine IPv6-Adresse)
  if [ "$colons" = "1" ] && [[ "$t" =~ ^(.+):([0-9]+)$ ]]; then
    t="${BASH_REMATCH[1]}"
    port="${BASH_REMATCH[2]}"
  fi
  # Nur IP oder Hostname ohne Benutzer → als root anmelden
  if [[ "$t" != *@* ]] && [[ "$t" == *.* || "$t" == *:* ]]; then
    t="root@$t"
  fi
  VISITFY_SSH_TARGET="$t"
  VISITFY_SSH_LABEL="$t${port:+ (Port $port)}"
  VISITFY_SSH_OPTS=(
    -o ConnectTimeout=10
    -o ServerAliveInterval=20
    -o StrictHostKeyChecking=accept-new
    -o ControlMaster=auto
    -o "ControlPath=/tmp/visitfy-ssh-%C"
    -o ControlPersist=120
  )
  if [ -n "$port" ]; then
    VISITFY_SSH_OPTS+=(-p "$port")
  fi
}

# Läuft der Name über Cloudflare? (dann geht kein SSH)
visitfy_ssh_is_proxied() {
  local host="${1##*@}"
  host="${host%%:*}"
  case "$(printf '%s' "$host" | tr '[:upper:]' '[:lower:]')" in
    visitfy.de|www.visitfy.de) return 0 ;;
  esac
  return 1
}

visitfy_ssh_setup() {          # $1: optionales Ziel
  local input="${1:-}" from_saved=0
  if [ -z "$input" ] && [ -s "$VISITFY_SSH_SAVE" ]; then
    input="$(head -1 "$VISITFY_SSH_SAVE")"
    from_saved=1
  fi
  while :; do
    if [ -z "$input" ] || visitfy_ssh_is_proxied "$input"; then
      echo
      if [ -n "$input" ]; then
        echo "  „${input##*@}“ läuft über Cloudflare – darüber geht kein SSH, nur direkt mit der IP des Servers."
      else
        echo "  Einmalig nötig: die IP des Servers, auf dem visitfy.de liegt (SSH geht nicht über Cloudflare)."
      fi
      echo "  Zu finden bei Cloudflare: visitfy.de → DNS → Einträge → A-Eintrag „visitfy.de“ (Spalte Inhalt)"
      echo "  oder in der Hetzner Console beim Server (Öffentliche IP)."
      printf '  Server-IP (oder root@IP:Port, SSH-Kürzel): '
      read -r input </dev/tty || input=""
      [ -n "$input" ] || { echo "✗ Abgebrochen."; exit 1; }
      from_saved=0
      continue
    fi
    visitfy_ssh_parse "$input"
    echo "→ Verbinde mit $VISITFY_SSH_LABEL …"
    if vssh true; then
      printf '%s\n' "$input" > "$VISITFY_SSH_SAVE"
      echo "✓ Verbunden (gemerkt in ~/scratch/.visitfy-ssh)"
      return 0
    fi
    echo "✗ Keine Verbindung zu $VISITFY_SSH_LABEL."
    if [ "$from_saved" = "1" ]; then
      echo "  Die gemerkte Adresse funktioniert nicht mehr."
    else
      echo "  Mögliche Gründe: Tippfehler in der IP, SSH läuft auf einem anderen Port (dann root@IP:Port),"
      echo "  oder die Hetzner-Firewall lässt SSH nur von bestimmten IPs zu."
    fi
    printf '  Nochmal eingeben (leer = abbrechen): '
    read -r input </dev/tty || input=""
    [ -n "$input" ] || { echo "✗ Abgebrochen."; exit 1; }
    from_saved=0
  done
}
