#!/usr/bin/env python3
"""
Visitfy3 – ops/build-package.py
Baut ein Deploy-Paket aus den Änderungen seit einem Basis-Commit, abgeglichen mit dem Live-Stand.

  python3 -I ops/build-package.py --live <entpackter-live-stand> --base <commit> --out <paket.tgz> [--merged a,b]

Regeln je geänderter Datei:
  Live == Basis (oder beide fehlen)     → neue Version ins Paket, MANIFEST erwartet den Live-Stand
  Live == neue Version                   → schon live, übersprungen
  Live weicht ab und ist in --merged     → neue Version (bereits mit dem Live-Stand zusammengeführt)
  Live weicht ab, nicht zusammengeführt  → Abbruch mit Liste (erst zusammenführen)
Laufzeitdaten, Zugangsdaten und Rechtsseiten werden nie überschrieben (siehe NEVER_DEPLOY);
die nötigen Anpassungen daran macht ops/patch-data.php auf dem Server.
"""
import argparse, hashlib, io, os, subprocess, sys, tarfile, time

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

NEVER_DEPLOY_PREFIX = ('ops/', '.github/', 'assets/data/stats/')
NEVER_DEPLOY = {
    '.gitignore', 'LICENSE', 'robots.txt',
    'assets/data/content.json', 'assets/data/tours.json', 'assets/data/inquiries.json',
    'assets/data/script-config.json', 'assets/data/content.json.bak',
    'pages/impressum.php', 'pages/datenschutz.php',
    'config.mail.php', 'config.admin.php', 'config.turnstile.php',
}
NEVER_DEPLOY_SUFFIX = ('.md',)


def sha(data):
    return hashlib.sha256(data).hexdigest() if data is not None else '-'


def git(*args):
    return subprocess.run(['git', '-C', REPO, *args], check=True, capture_output=True).stdout


def git_file(rev, path):
    try:
        return git('show', f'{rev}:{path}')
    except subprocess.CalledProcessError:
        return None


def read(path):
    return open(path, 'rb').read() if os.path.isfile(path) else None


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--live', required=True)
    ap.add_argument('--base', required=True)
    ap.add_argument('--out', required=True)
    ap.add_argument('--merged', default='')
    a = ap.parse_args()
    merged = {m.strip() for m in a.merged.split(',') if m.strip()}

    changed = git('diff', '--name-only', '--no-renames', a.base, '--').decode().split()
    changed += git('ls-files', '--others', '--exclude-standard').decode().split()
    changed = sorted(set(changed))

    plan, skipped, drift = [], [], []
    for path in changed:
        if path in NEVER_DEPLOY or path.startswith(NEVER_DEPLOY_PREFIX) or path.endswith(NEVER_DEPLOY_SUFFIX):
            skipped.append((path, 'nie deployen'))
            continue
        new = read(os.path.join(REPO, path))
        if new is None:
            skipped.append((path, 'im Repo gelöscht – bleibt live liegen'))
            continue
        base = git_file(a.base, path)
        live = read(os.path.join(a.live, path))
        if sha(live) == sha(new):
            skipped.append((path, 'schon live'))
        elif sha(live) == sha(base) or path in merged:
            plan.append((path, sha(live), sha(new), new))
        else:
            drift.append(path)

    if drift:
        print('✗ Auf dem Server geändert (erst zusammenführen, dann mit --merged bauen):')
        for p in drift:
            print('   ', p)
        sys.exit(3)

    manifest = ''.join(f'{p}\t{exp}\t{new}\n' for p, exp, new, _ in plan)
    with tarfile.open(a.out, 'w:gz') as tar:
        def add(name, data, mode=0o644):
            info = tarfile.TarInfo(name)
            info.size = len(data)
            info.mode = mode
            info.mtime = int(time.time())
            info.uid = info.gid = 0
            info.uname = info.gname = 'root'
            tar.addfile(info, io.BytesIO(data))
        for p, _, _, data in plan:
            mtime = int(os.path.getmtime(os.path.join(REPO, p)))
            info = tarfile.TarInfo(p)
            info.size, info.mode, info.mtime = len(data), 0o644, mtime
            info.uid = info.gid = 0
            tar.addfile(info, io.BytesIO(data))
        add('_deploy/MANIFEST', manifest.encode())
        add('_deploy/remote-apply.sh', read(os.path.join(REPO, 'ops/remote-apply.sh')), 0o755)
        add('_deploy/patch-data.php', read(os.path.join(REPO, 'ops/patch-data.php')))
        add('_deploy/BUILD', f'base={a.base}\nhead={git("rev-parse", "HEAD").decode().strip()}\nbuilt={time.strftime("%Y-%m-%d %H:%M:%S")}\n'.encode())

    print(f'✓ Paket: {a.out} – {len(plan)} Dateien')
    for p, exp, _, _ in plan:
        print(f'   {"neu  " if exp == "-" else "ändern"} {p}')
    if skipped:
        print('  Nicht im Paket:')
        for p, why in skipped:
            print(f'   – {p} ({why})')


if __name__ == '__main__':
    main()
