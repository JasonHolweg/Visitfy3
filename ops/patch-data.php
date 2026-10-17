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

/* ── 1b) Lieferzeit: „max. 5 Tage“ statt „3 Tage“ in allen Texten + KPI ── */
if (is_file($contentFile)) {
    $content = json_decode((string)file_get_contents($contentFile), true);
    $rules = [
        '/Ø\s*3\s*Tage\s+bis\s+live/u'                                   => 'max. 5 Tage bis online',
        '/in\s+unter\s+3\s+Tagen\s+live/iu'                               => 'in maximal 5 Tagen online',
        '/In\s+3\s+Werktagen\s+live/u'                                     => 'In max. 5 Tagen online',
        '/In\s+nur\s+3\s+Tagen/u'                                          => 'In maximal 5 Tagen',
        '/In\s+3\s+Tagen\s+live/u'                                         => 'In max. 5 Tagen online',
        '/im\s+Durchschnitt\s+innerhalb\s+von\s+[35]\s+Werktagen\s+live/u' => 'spätestens 5 Tage später online',
    ];
    $changes = 0;
    $walk = function (&$node) use (&$walk, $rules, &$changes) {
        if (is_array($node)) {
            foreach ($node as &$child) {
                $walk($child);
            }
            unset($child);
        } elseif (is_string($node)) {
            $new = preg_replace(array_keys($rules), array_values($rules), $node);
            if (is_string($new) && $new !== $node) {
                $node = $new;
                $changes++;
            }
        }
    };
    $walk($content);
    foreach ((array)($content['kpi']['items'] ?? []) as $i => $item) {
        $label = (string)($item['label'] ?? '');
        $suffix = trim((string)($item['suffix'] ?? ''));
        $want = ['target' => '5', 'suffix' => 'Tage', 'label' => 'maximal, bis Ihr Rundgang online ist'];
        if ((preg_match('/live\s+geht|online\s+ist/u', $label) || ((string)($item['target'] ?? '') === '3' && preg_match('/^Tag/u', $suffix)))
            && array_intersect_key($item, $want) != $want) {
            $content['kpi']['items'][$i] = array_merge($item, $want);
            $changes++;
        }
    }
    if ($changes > 0) {
        write_json($contentFile, $content);
        out("content.json: Lieferzeit auf „max. 5 Tage“ angepasst ($changes Stellen)");
    }
    $left = [];
    array_walk_recursive($content, function ($v, $k) use (&$left) {
        if (is_string($v) && preg_match('/\b3\s*(Werk)?[Tt]age/u', $v)) {
            $left[] = mb_substr($v, 0, 90);
        }
    });
    foreach ($left as $l) {
        out("Hinweis – noch „3 Tage“ im Text: „{$l}“");
    }
}

/* ── 1c) Gesamtzahl Rundgänge aus dem CRM (nur wenn config.crm.php funktioniert) ── */
if (is_file($root . '/config.crm.php') && is_file($root . '/partials/crm.php') && is_file($contentFile)) {
    require_once $root . '/partials/crm.php';
    $tours = visitfy_crm_fetch_tours();
    if ($tours === null) {
        out('CRM: keine Zahl erhalten – Texte bleiben unverändert (config.crm.php prüfen)');
    } else {
        out("CRM: $tours Rundgänge");
        $content = json_decode((string)file_get_contents($contentFile), true);
        $changes = 0;
        foreach ((array)($content['kpi']['items'] ?? []) as $i => $item) {
            $label = (string)($item['label'] ?? '');
            if (preg_match('/Rundgänge|Projekte/u', $label) && !preg_match('/online|live/u', $label) && ($item['target'] ?? '') !== '{rundgaenge}') {
                $content['kpi']['items'][$i]['target'] = '{rundgaenge}';
                $content['kpi']['items'][$i]['suffix'] = '';
                $changes++;
            }
        }
        foreach (['paragraph_1', 'paragraph_2', 'paragraph_3'] as $key) {
            $text = (string)($content['about'][$key] ?? '');
            $new = preg_replace('/über\s+\d+\s+abgeschlossenen\s+Projekten/u', '{rundgaenge} umgesetzten Rundgängen', $text);
            if (is_string($new) && $new !== $text) {
                $content['about'][$key] = $new;
                $changes++;
            }
        }
        if ($changes > 0) {
            write_json($contentFile, $content);
            out("content.json: Rundgang-Zahl kommt jetzt aus dem CRM ($changes Stellen)");
        }
    }
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

/* ── 3) robots.txt: genau eine Sitemap-Zeile, Rest bleibt ── */
$robotsFile = $root . '/robots.txt';
$robots = is_file($robotsFile) ? (string)file_get_contents($robotsFile) : "User-agent: *\nDisallow: /admin/\nAllow: /\n";
$robotsNew = preg_replace('/^Sitemap:.*$/mi', '', $robots);
$robotsNew = rtrim(preg_replace("/\n{3,}/", "\n\n", $robotsNew)) . "\n\nSitemap: https://visitfy.de/sitemap.xml\n";
if ($robotsNew !== $robots) {
    file_put_contents($robotsFile, $robotsNew, LOCK_EX);
    out('robots.txt: Sitemap-Zeile gesetzt');
}

/* ── 4) Rechtsseiten: neue URL-Helfer + noindex (Inhalt bleibt unangetastet) ── */
foreach (['impressum', 'datenschutz'] as $key) {
    $file = $root . '/pages/' . $key . '.php';
    if (!is_file($file)) {
        continue;
    }
    $src = (string)file_get_contents($file);
    $new = preg_replace(
        '/^\$root = \(realpath\(\$_SERVER\[\'SCRIPT_FILENAME\'\] \?\? \'\'\) !== __FILE__\) \? visitfy_base_path\(\) : \'\.\.\/\';[ \t]*$/m',
        "\$root = visitfy_site_root();\n\$canonicalPath = visitfy_page_url('$key');",
        $src,
        1
    );
    if (strpos($new, '$pageRobots') === false) {
        $new = preg_replace('/^(\$pageDesc\s*=.*;)[ \t]*$/m', "$1\n\$pageRobots = 'noindex, follow';", $new, 1);
    }
    if ($new !== null && $new !== $src) {
        file_put_contents($file, $new, LOCK_EX);
        out("pages/$key.php: saubere URL + noindex");
    } elseif (strpos($src, 'visitfy_site_root()') === false) {
        out("pages/$key.php: Kopfzeilen nicht erkannt – unverändert (Seite funktioniert trotzdem)");
    }
}

/* ── 5) Datenschutz: Absatz zur cookielosen Reichweitenmessung (partials/stats.php), einmalig ── */
$dsFile = $root . '/pages/datenschutz.php';
if (is_file($dsFile)) {
    $ds = (string)file_get_contents($dsFile);
    $anchor = '<h2>4. Eingebettete Inhalte Dritter</h2>';
    if (strpos($ds, 'Reichweitenmessung ohne Cookies') !== false) {
        // schon vorhanden
    } elseif (substr_count($ds, $anchor) !== 1) {
        out('pages/datenschutz.php: Abschnitt 4 nicht gefunden – Absatz zur Statistik bitte von Hand ergänzen');
    } else {
        $block = <<<'HTML'
<h2>3.3 Reichweitenmessung ohne Cookies</h2>
        <p>
          Um zu verstehen, wie unsere Website genutzt wird, zählt unser eigener Server die Seitenaufrufe.
          Dafür werden keine Cookies gesetzt, keine Mess-Skripte ausgeführt und keine Informationen auf
          Ihrem Endgerät gespeichert oder ausgelesen. Es werden keine Daten an Dritte übermittelt.
        </p>
        <p>
          Gespeichert werden je Aufruf nur: die aufgerufene Seite, der Zeitpunkt, die Herkunft (z. B. „Google“,
          „ChatGPT“, „direkt“ oder die Domain der verweisenden Website, ohne deren genaue Adresse), der
          Gerätetyp (Computer, Tablet oder Smartphone) sowie
          ein Kurzwert, mit dem Aufrufe desselben Tages zusammengefasst werden. Dieser Kurzwert wird aus
          IP-Adresse und Browserkennung zusammen mit einem täglich neu erzeugten Zufallswert berechnet;
          die IP-Adresse selbst wird dabei nicht gespeichert. Der Zufallswert wird nach spätestens zwei
          Tagen gelöscht, danach lassen sich die Einträge keiner Person mehr zuordnen. Bei abgeschickten
          Anfragen wird zusätzlich nur gezählt, dass eine Anfrage eingegangen ist – ohne deren Inhalt.
        </p>
        <p>
          Zweck ist die Verbesserung unseres Angebots. Rechtsgrundlage: Art. 6 Abs. 1 lit. f DSGVO
          (berechtigtes Interesse an einer datensparsamen Reichweitenmessung).
        </p>


HTML;
        $ds = str_replace($anchor, rtrim($block) . "\n\n        " . $anchor, $ds);
        file_put_contents($dsFile, $ds, LOCK_EX);
        out('pages/datenschutz.php: Abschnitt 3.3 „Reichweitenmessung ohne Cookies“ ergänzt');
    }
}

exit(0);
