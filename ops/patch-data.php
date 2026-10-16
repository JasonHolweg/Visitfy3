<?php
/**
 * Visitfy3 – ops/patch-data.php
 * Läuft auf dem Server (aus ops/remote-apply.sh): passt Laufzeitdaten gezielt an,
 * statt sie zu überschreiben. Gibt jede Änderung aus.
 *
 *   php patch-data.php <webroot>
 */
if (PHP_SAPI !== 'cli') {
    exit(1);
}
$root = rtrim((string)($argv[1] ?? ''), '/');
if ($root === '' || !is_file($root . '/index.php')) {
    fwrite(STDERR, "Webroot fehlt oder falsch\n");
    exit(1);
}

function out(string $msg): void
{
    echo '  ' . $msg . "\n";
}

/** Wie admin_write_json(): gleiche Formatierung, Datei bleibt dieselbe (Besitzer/Rechte bleiben erhalten). */
function write_json(string $file, array $data): void
{
    $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (!is_string($encoded) || file_put_contents($file, $encoded . PHP_EOL, LOCK_EX) === false) {
        fwrite(STDERR, "Schreiben fehlgeschlagen: $file\n");
        exit(1);
    }
}

/* ── 1) SEO-Titel und -Beschreibung der Startseite (content.json) ── */
$contentFile = $root . '/assets/data/content.json';
if (is_file($contentFile)) {
    $content = json_decode((string)file_get_contents($contentFile), true);
    if (!is_array($content)) {
        fwrite(STDERR, "content.json ist kein gültiges JSON – nichts geändert\n");
        exit(1);
    }
    $seo = [
        'home_title' => '360°-Rundgang erstellen lassen für Ihr Unternehmen | Visitfy',
        'home_desc'  => 'Virtuelle 360°-Rundgänge für Gastronomie, Hotels, Praxen, Immobilien und Handel – vor Ort aufgenommen, schnell online, für Ihre Website und Google.',
    ];
    $changed = false;
    foreach ($seo as $key => $value) {
        $old = (string)($content['seo'][$key] ?? '');
        if ($old !== $value) {
            $content['seo'][$key] = $value;
            out("content.json seo.$key: „{$old}“ → „{$value}“");
            $changed = true;
        }
    }
    if ($changed) {
        write_json($contentFile, $content);
    }
} else {
    out('content.json nicht gefunden – SEO-Titel bleiben die Standardwerte aus index.php');
}

/* ── 2) Intro kürzer (nur wenn noch die alten Standardwerte gesetzt sind) ── */
$scriptFile = $root . '/assets/data/script-config.json';
if (is_file($scriptFile)) {
    $script = json_decode((string)file_get_contents($scriptFile), true);
    if (is_array($script)) {
        $changed = false;
        foreach (['intro_hold' => [1500, 900], 'fade_out_duration' => [1100, 600]] as $key => [$oldDefault, $new]) {
            if ((int)($script['intro'][$key] ?? $oldDefault) === $oldDefault) {
                $script['intro'][$key] = $new;
                out("script-config.json intro.$key: $oldDefault → $new");
                $changed = true;
            }
        }
        if ($changed) {
            write_json($scriptFile, $script);
        }
    }
}

/* ── 3) robots.txt: Sitemap-Zeile und /ping ausschließen, Rest bleibt ── */
$robotsFile = $root . '/robots.txt';
$robots = is_file($robotsFile) ? (string)file_get_contents($robotsFile) : "User-agent: *\nDisallow: /admin/\nAllow: /\n";
$robotsNew = $robots;
if (stripos($robotsNew, 'Disallow: /ping') === false) {
    $robotsNew = preg_replace('/^(User-agent:\s*\*\s*\R)/mi', "$1Disallow: /ping\n", $robotsNew, 1);
}
$robotsNew = preg_replace('/^Sitemap:.*$/mi', '', $robotsNew);
$robotsNew = rtrim(preg_replace("/\n{3,}/", "\n\n", $robotsNew)) . "\n\nSitemap: https://visitfy.de/sitemap.xml\n";
if ($robotsNew !== $robots) {
    file_put_contents($robotsFile, $robotsNew, LOCK_EX);
    out('robots.txt: /ping ausgeschlossen, Sitemap-Zeile gesetzt');
}

/* ── 4) Rechtsseiten: neue URL-Helfer + noindex (Inhalt bleibt unangetastet) ── */
foreach (['impressum', 'datenschutz'] as $key) {
    $file = $root . '/pages/' . $key . '.php';
    if (!is_file($file)) {
        continue;
    }
    $src = (string)file_get_contents($file);
    $new = preg_replace(
        '/^\$root = \(realpath\(\$_SERVER\[\'SCRIPT_FILENAME\'\] \?\? \'\'\) !== __FILE__\) \? visitfy_base_path\(\) : \'\.\.\/\';\s*$/m',
        "\$root = visitfy_site_root();\n\$canonicalPath = visitfy_page_url('$key');",
        $src,
        1
    );
    if (strpos($new, '$pageRobots') === false) {
        $new = preg_replace('/^(\$pageDesc\s*=.*;)\s*$/m', "$1\n\$pageRobots = 'noindex, follow';", $new, 1);
    }
    if ($new !== null && $new !== $src) {
        file_put_contents($file, $new, LOCK_EX);
        out("pages/$key.php: saubere URL + noindex");
    } elseif (strpos($src, 'visitfy_site_root()') === false) {
        out("pages/$key.php: Kopfzeilen nicht erkannt – unverändert (Seite funktioniert trotzdem)");
    }
}

exit(0);
