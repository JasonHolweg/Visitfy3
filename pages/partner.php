<?php
/**
 * Visitfy3 – pages/partner.php
 * Partner-werden Seite: Zahlen, Netzwerk-Karte, Verdienst-Rechner,
 * Partner-Dashboard, Empfehlungsprogramm, FAQ, Formular.
 * Zahlen + Standorte: partials/partner-network.php
 */
require __DIR__ . '/../partials/cms.php';
require_once __DIR__ . '/../partials/turnstile.php';
$root = visitfy_site_root();
$canonicalPath = visitfy_page_url('partner');
$contentConfig = visitfy_load_json(__DIR__ . '/../assets/data/content.json', []);
$net = require __DIR__ . '/../partials/partner-network.php';
$map = require __DIR__ . '/../partials/germany-map-paths.php';
$voicesRaw = require __DIR__ . '/../partials/partner-testimonials.php';

$h = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$c = static fn(string $key, string $default): string => htmlspecialchars((string)visitfy_get($contentConfig, 'partner.' . $key, $default), ENT_QUOTES, 'UTF-8');
$eur = static fn(float $v): string => number_format($v, 0, ',', '.');

$partners   = (int)$net['stats']['partners'];
$avgRevenue = (int)$net['stats']['avg_revenue'];
$avgTours   = (int)$net['stats']['avg_tours'];
$perTour    = $avgTours > 0 ? $avgRevenue / $avgTours : 0;
$refPct     = (int)$net['referral']['percent'];
$refSales   = (int)$net['referral']['sales'];
$refPerSale = $perTour * $refPct / 100;
$refBonus   = $refPerSale * $refSales;

$activeNames  = array_column($net['active'], 'name');
$openNames    = array_column($net['open'], 'name');
$activeStates = array_values(array_unique(array_column($net['active'], 'region')));
$regions      = count($activeStates);
$stateCount   = count($net['states']);
$freeStates   = array_values(array_diff($net['states'], $activeStates));
$prio = array_flip($net['states_priority'] ?? []);
usort($freeStates, static fn($a, $b) => ($prio[$a] ?? 99) <=> ($prio[$b] ?? 99));
$freeCount = count($freeStates);

$hq = $net['active'][0];
foreach ($net['active'] as $p) { if (!empty($p['hq'])) { $hq = $p; break; } }
[$mapW, $mapH] = array_map('floatval', array_slice(explode(' ', $map['viewbox']), 2));
$pct = static fn(float $v, float $total): string => (string)round($v / $total * 100, 2);

/* Bogen von der Zentrale (Flensburg) zu jedem Partner */
$arc = static function (array $from, array $to): string {
    $mx = ($from['x'] + $to['x']) / 2;
    $my = ($from['y'] + $to['y']) / 2;
    $dx = $to['x'] - $from['x'];
    $dy = $to['y'] - $from['y'];
    $len = max(1, sqrt($dx * $dx + $dy * $dy));
    $bend = min(90, $len * 0.22);
    $cx = $mx + (-$dy / $len) * $bend;
    $cy = $my + ($dx / $len) * $bend;
    return sprintf('M%.1f %.1fQ%.1f %.1f %.1f %.1f', $from['x'], $from['y'], $cx, $cy, $to['x'], $to['y']);
};

$listDe = static function (array $items): string {
    if (count($items) < 2) return implode('', $items);
    $last = array_pop($items);
    return implode(', ', $items) . ' und ' . $last;
};

/* Beispielwerte für das Dashboard (aus den echten Durchschnitten abgeleitet) */
$demoRefDone    = round($refPerSale * $refSales);   // geworbener Partner, beide Verkäufe erledigt
$demoRefHalf    = round($refPerSale);               // geworbener Partner, erster Verkauf erledigt
$demoRefTotal   = $demoRefDone + $demoRefHalf;

/* Rechner-Skala: Beschriftungen exakt auf ihren Wert setzen */
$rangeMin = 1;
$rangeMax = 30;
$rangePos = static function (int $v) use ($rangeMin, $rangeMax): string {
    $f = ($v - $rangeMin) / ($rangeMax - $rangeMin);
    // Daumen ist 22 px breit: Position entspricht der Daumenmitte
    return sprintf('calc(%.3f%% + %.2fpx)', $f * 100, 11 - $f * 22);
};

/* Partnerstimmen: live nur vollständige, freigegebene Einträge.
   Platzhalter erscheinen ausschließlich in der lokalen Vorschau (Umgebungsvariable VISITFY_PREVIEW=1). */
$voicePreview = getenv('VISITFY_PREVIEW') === '1';
$voicePrompts = [
    'Wie bist du zu Visitfy gekommen, und was hat sich seitdem verändert?',
    'Wie schnell hattest du deine ersten Kunden?',
    'Was gefällt dir an der Arbeit als Partner am meisten?',
    'Wie erlebst du den Support und das Dashboard im Alltag?',
    'Was würdest du jemandem sagen, der überlegt, Partner zu werden?',
];
$voices = [];
$voicesReal = 0;
foreach ($voicesRaw as $i => $v) {
    $foto = trim((string)($v['foto'] ?? ''));
    $complete = !empty($v['freigegeben'])
        && trim((string)($v['name'] ?? '')) !== ''
        && trim((string)($v['zitat'] ?? '')) !== ''
        && (int)($v['umsatz'] ?? 0) > 0
        && $foto !== '' && is_file(__DIR__ . '/../' . ltrim($foto, '/'));
    if ($complete) {
        $voices[] = [
            'name' => trim((string)$v['name']), 'stadt' => trim((string)($v['stadt'] ?? '')), 'seit' => trim((string)($v['seit'] ?? '')),
            'zitat' => trim((string)$v['zitat']), 'umsatz' => (int)round((int)$v['umsatz'], -2), 'foto' => ltrim($foto, '/'), 'platzhalter' => false,
        ];
        $voicesReal++;
    } elseif ($voicePreview) {
        $voices[] = [
            'name' => 'Name folgt', 'stadt' => trim((string)($v['stadt'] ?? '')) ?: 'Stadt folgt', 'seit' => 'Partner seit …',
            'zitat' => 'Platzhalter für das Zitat. Frage dazu: ' . $voicePrompts[$i % count($voicePrompts)],
            'umsatz' => 0, 'foto' => '', 'platzhalter' => true,
        ];
    }
}
$voiceSilhouette = '<svg viewBox="0 0 64 64" aria-hidden="true" focusable="false"><circle cx="32" cy="24" r="11"/><path d="M12 58c1.5-11 9.5-18 20-18s18.5 7 20 18z"/></svg>';

/* FAQ: sichtbar auf der Seite UND als FAQPage-Schema (beides muss identisch sein) */
$faq = [
    [
        'q' => 'Wie viel verdienen Visitfy-Partner?',
        'a' => 'Unsere ' . $partners . ' aktiven Partner machen im Schnitt ' . $eur($avgRevenue) . ' € Umsatz pro Monat (Stand ' . $net['stand'] . '). Das entspricht rund ' . $avgTours . ' verkauften 360°-Rundgängen im Monat bzw. ca. ' . $eur($perTour) . ' € Umsatz pro Rundgang. Einige Partner liegen deutlich darüber. Es handelt sich um Umsatz, nicht um Gewinn; das Ergebnis hängt von deinem Einsatz und deiner Region ab.',
    ],
    [
        'q' => 'In welchen Städten gibt es schon Visitfy-Partner?',
        'a' => 'Aktuell gibt es Visitfy-Partner in ' . $listDe($activeNames) . '. Das sind ' . $partners . ' Standorte in ' . $regions . ' Bundesländern.',
    ],
    [
        'q' => 'Wo sucht Visitfy neue Partner?',
        'a' => 'Überall, wo es noch keinen Visitfy-Partner gibt. In ' . $freeCount . ' von ' . $stateCount . ' Bundesländern ist bisher kein einziger Partner aktiv: ' . $listDe($freeStates) . '. Besonders im Süden suchen wir gerade, zum Beispiel in ' . $listDe($openNames) . '.',
    ],
    [
        'q' => 'Brauche ich viel Startkapital?',
        'a' => 'Nein. Du brauchst kein Büro, kein Lager und keine teure Ausstattung. Was zählt, sind dein Einsatz und dein Wille, wirklich loszulegen.',
    ],
    [
        'q' => 'Was ist das Visitfy Partner-Dashboard?',
        'a' => 'Jeder Partner erhält ein eigenes Dashboard mit Buchhaltungsfunktionen: Du schreibst dort deine Rechnungen, legst Firmen bzw. Kunden an und empfiehlst weitere Partner.',
    ],
    [
        'q' => 'Wie funktioniert das Empfehlungsprogramm?',
        'a' => 'Wenn du einen neuen Partner für Visitfy gewinnst, erhältst du ' . $refPct . ' % vom Umsatz seiner ersten ' . $refSales . ' Verkäufe. Bei einem durchschnittlichen Rundgangswert sind das rund ' . $eur($refBonus) . ' € pro geworbenem Partner.',
    ],
    [
        'q' => 'Wie werde ich Visitfy-Partner?',
        'a' => 'Fülle das Formular auf dieser Seite aus und nenne uns deine Stadt oder Region. Wir melden uns persönlich bei dir und vereinbaren ein unverbindliches Erstgespräch.',
    ],
];

$pageTitle = 'Partner werden bei Visitfy | 360°-Rundgänge verkaufen';
$pageDesc  = 'Visitfy-Partner machen im Schnitt ' . $eur($avgRevenue) . ' € Umsatz im Monat mit rund ' . $avgTours . ' 360°-Rundgängen. ' . $partners . ' Partner deutschlandweit, viele Regionen sind noch frei, vor allem im Süden.';
$pageOgImg = 'assets/img/og-partner.png';
$extraScripts = ['assets/js/partner.js'];

$pageUrl = visitfy_page_url('partner', true);
require_once __DIR__ . '/../partials/schema.php';
$webPage = visitfy_schema_webpage($pageUrl, $pageTitle, $pageDesc, [
    ['Start', visitfy_page_url('home', true)],
    ['Partner werden', $pageUrl],
]);
$webPage['primaryImageOfPage'] = VISITFY_SITE_URL . '/' . $pageOgImg;
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@graph' => [
        visitfy_schema_organization($contentConfig),
        visitfy_schema_website(),
        $webPage,
        visitfy_schema_faq($faq, $pageUrl . '#faq'),
    ],
]];

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content" class="pw">

  <!-- ── HERO ─────────────────────────────────────────────── -->
  <section class="pw-hero" aria-labelledby="pw-hero-title">
    <div class="container pw-hero-inner">
      <p class="section-eyebrow fade-up">Das Visitfy-Partnerprogramm</p>

      <h1 id="pw-hero-title" class="pw-hero-title fade-up delay-1">
        <span class="pw-hero-kicker">Unsere Partner machen im Schnitt</span>
        <span class="pw-hero-number">
          <span data-countup data-target="<?= $avgRevenue ?>"><?= $eur($avgRevenue) ?></span><span class="pw-hero-currency">&nbsp;€</span>
        </span>
        <span class="pw-hero-kicker">Umsatz. Jeden Monat.<sup><a href="#pw-footnote" aria-label="Hinweis zu den Zahlen">*</a></sup></span>
      </h1>

      <p class="pw-hero-sub fade-up delay-2">
        Mit rund <?= $avgTours ?> verkauften 360°-Rundgängen im Monat. Die besten Partner liegen deutlich darüber.
        Du bringst den Einsatz mit, wir das System, das funktioniert.
      </p>

      <div class="pw-hero-actions fade-up delay-3">
        <a href="#partner-form" class="btn btn-primary js-btnfx-partner">Jetzt Partner werden</a>
        <a href="#pw-map" class="btn btn-secondary">Partner-Karte ansehen</a>
      </div>

      <dl class="pw-stats fade-up delay-4">
        <div class="pw-stat">
          <dt>Aktive Partner</dt>
          <dd><span data-countup data-target="<?= $partners ?>"><?= $partners ?></span></dd>
        </div>
        <div class="pw-stat">
          <dt>Ø Rundgänge pro Monat</dt>
          <dd><span data-countup data-target="<?= $avgTours ?>"><?= $avgTours ?></span></dd>
        </div>
        <div class="pw-stat">
          <dt>Ø Umsatz pro Rundgang</dt>
          <dd>~<span data-countup data-target="<?= round($perTour) ?>"><?= $eur($perTour) ?></span>&nbsp;€</dd>
        </div>
        <div class="pw-stat">
          <dt>Empfehlungsprämie</dt>
          <dd><span data-countup data-target="<?= $refPct ?>"><?= $refPct ?></span>&nbsp;%</dd>
        </div>
      </dl>
    </div>
  </section>

  <!-- ── KARTE ────────────────────────────────────────────── -->
  <section class="section pw-map-section" id="pw-map" aria-labelledby="pw-map-title">
    <div class="container pw-map-layout">
      <div class="pw-map-copy">
        <p class="section-eyebrow pw-left fade-up">Das Visitfy-Netzwerk</p>
        <h2 class="section-title pw-left fade-up delay-1" id="pw-map-title"><?= $partners ?> Partner.<br>Von Flensburg bis Stuttgart.</h2>
        <p class="pw-copy fade-up delay-2">
          Jeder leuchtende Punkt ist ein Partner, der mit Visitfy sein eigenes Geschäft aufgebaut hat.
          Wo noch kein Punkt leuchtet, ist dein Gebiet frei. <strong>Am dringendsten suchen wir im Süden.</strong>
        </p>

        <ul class="pw-city-list fade-up delay-3" aria-label="Aktive Partner-Standorte">
          <?php foreach ($net['active'] as $i => $p): ?>
            <li>
              <button type="button" class="pw-city" data-pin="a<?= $i ?>">
                <span class="pw-city-dot" aria-hidden="true"></span><?= $h($p['name']) ?>
              </button>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="pw-legend fade-up delay-4" aria-hidden="true">
          <span><i class="pw-legend-dot"></i>Aktiver Partner</span>
          <span><i class="pw-legend-dot pw-legend-dot--open"></i>Freie Region</span>
        </div>
      </div>

      <div class="pw-map-wrap">
        <figure class="pw-map" role="img" aria-label="Deutschlandkarte mit <?= $partners ?> Visitfy-Partnern von Flensburg bis Stuttgart. Freie Regionen unter anderem in <?= $h($listDe(array_merge($openNames, array_slice(array_column($net['free'], 'name'), 0, 8)))) ?>.">
          <svg viewBox="<?= $h($map['viewbox']) ?>" class="pw-map-svg" aria-hidden="true" focusable="false">
            <defs>
              <pattern id="pwDots" width="6.5" height="6.5" patternUnits="userSpaceOnUse">
                <circle cx="3.25" cy="3.25" r="1.05" fill="#ffffff" />
              </pattern>
              <linearGradient id="pwArcGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#ffffff" stop-opacity="0.55" />
                <stop offset="1" stop-color="#ffffff" stop-opacity="0.12" />
              </linearGradient>
              <path id="pwDE" d="<?= $map['outline'] ?>" pathLength="1" />
            </defs>

            <g class="pw-map-base">
              <use href="#pwDE" class="pw-map-dots" fill="url(#pwDots)" />
              <path class="pw-map-states" d="<?= $map['states'] ?>" />
              <use href="#pwDE" class="pw-map-outline" />
            </g>

            <g class="pw-arcs">
              <?php foreach ($net['active'] as $i => $p): if (!empty($p['hq'])) continue; ?>
                <path d="<?= $arc($hq, $p) ?>" pathLength="1" style="--i:<?= $i ?>" />
              <?php endforeach; ?>
            </g>

            <g class="pw-pins-free">
              <?php foreach ($net['free'] as $i => $p): ?>
                <g transform="translate(<?= $p['x'] ?> <?= $p['y'] ?>)">
                  <g class="pw-pin pw-pin--free" style="--i:<?= $i ?>;--t:<?= ($i * 7) % 11 ?>">
                    <circle class="pw-pin-ring" r="5" />
                  </g>
                </g>
              <?php endforeach; ?>
            </g>

            <g class="pw-pins-open">
              <?php foreach ($net['open'] as $i => $p): ?>
                <g transform="translate(<?= $p['x'] ?> <?= $p['y'] ?>)">
                  <g class="pw-pin pw-pin--open" style="--i:<?= $i ?>">
                    <circle class="pw-pin-ring" r="8" />
                    <circle class="pw-pin-core" r="2.4" />
                  </g>
                </g>
              <?php endforeach; ?>
            </g>

            <g class="pw-pins">
              <?php foreach ($net['active'] as $i => $p): ?>
                <g transform="translate(<?= $p['x'] ?> <?= $p['y'] ?>)">
                  <g class="pw-pin<?= !empty($p['hq']) ? ' pw-pin--hq' : '' ?>" data-pin-id="a<?= $i ?>" style="--i:<?= $i ?>">
                    <circle class="pw-pin-pulse" r="5" />
                    <circle class="pw-pin-halo" r="8" />
                    <circle class="pw-pin-core" r="4" />
                  </g>
                </g>
              <?php endforeach; ?>
            </g>
          </svg>

          <!-- HTML-Labels über der Karte (scharf auf allen Displays) -->
          <div class="pw-map-labels" aria-hidden="true">
            <?php foreach ($net['active'] as $i => $p): ?>
              <span class="pw-label" data-label-id="a<?= $i ?>" style="left:<?= $pct($p['x'], $mapW) ?>%;top:<?= $pct($p['y'], $mapH) ?>%"><?= $h($p['name']) ?></span>
            <?php endforeach; ?>
            <?php foreach ($net['open'] as $i => $p): ?>
              <span class="pw-label pw-label--open" style="left:<?= $pct($p['x'], $mapW) ?>%;top:<?= $pct($p['y'], $mapH) ?>%;--i:<?= $i ?>"><?= $h($p['name']) ?></span>
            <?php endforeach; ?>
          </div>
        </figure>
      </div>
    </div>

    <!-- Freie Bundesländer -->
    <div class="container">
      <div class="pw-free fade-up">
        <p class="pw-free-figure">
          <span class="pw-free-num" data-countup data-target="<?= $freeCount ?>"><?= $freeCount ?></span>
          <span class="pw-free-of">von <?= $stateCount ?><br>Bundesländern</span>
        </p>
        <div class="pw-free-body">
          <h3 class="pw-free-title">haben noch keinen einzigen Visitfy-Partner.</h3>
          <p class="pw-copy">
            Dort ist jede Stadt noch zu haben, und wer jetzt startet, ist in seiner Region der Erste.
            Besonders gesucht: <?= $h($listDe(array_slice($openNames, 0, 5))) ?>.
          </p>
          <ul class="pw-chips" aria-label="Bundesländer ohne Visitfy-Partner">
            <?php foreach ($freeStates as $state): ?><li><?= $h($state) ?></li><?php endforeach; ?>
          </ul>
          <a href="#partner-form" class="btn btn-primary js-btnfx-partner" data-prefill-city>Meine Region anfragen</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ── RECHNER ──────────────────────────────────────────── -->
  <section class="section pw-calc-section" aria-labelledby="pw-calc-title">
    <div class="container">
      <p class="section-eyebrow fade-up">Dein Potenzial</p>
      <h2 class="section-title fade-up delay-1" id="pw-calc-title">Was ist für dich drin?</h2>
      <p class="section-sub fade-up delay-2">Schieb den Regler. Gerechnet wird mit dem echten Durchschnittsumsatz unserer Partner pro Rundgang.</p>

      <div class="pw-calc glass fade-up delay-3" data-pw-calc data-per-tour="<?= $h(round($perTour, 3)) ?>" data-avg="<?= $avgTours ?>">
        <div class="pw-calc-control">
          <label for="pw-tours" class="pw-calc-label">
            Verkaufte Rundgänge pro Monat
            <output for="pw-tours" class="pw-calc-tours" data-out="tours"><?= $avgTours ?></output>
          </label>
          <div class="pw-range">
            <input type="range" id="pw-tours" min="<?= $rangeMin ?>" max="<?= $rangeMax ?>" step="1" value="<?= $avgTours ?>" aria-describedby="pw-calc-hint">
            <span class="pw-range-avg" style="left:<?= $rangePos($avgTours) ?>" aria-hidden="true">Ø Partner</span>
          </div>
          <div class="pw-range-scale" aria-hidden="true">
            <?php foreach ([1, 10, 20, 30] as $tick): ?>
              <span style="left:<?= $rangePos($tick) ?>"><?= $tick ?></span>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="pw-calc-results">
          <div class="pw-calc-result">
            <span class="pw-calc-result-label">Umsatz pro Monat</span>
            <span class="pw-calc-result-value"><span data-out="month"><?= $eur($avgRevenue) ?></span>&nbsp;€</span>
          </div>
          <div class="pw-calc-result pw-calc-result--year">
            <span class="pw-calc-result-label">Umsatz pro Jahr</span>
            <span class="pw-calc-result-value"><span data-out="year"><?= $eur($avgRevenue * 12) ?></span>&nbsp;€</span>
          </div>
          <p class="pw-calc-note" data-out="badge" aria-live="polite">Genau der Schnitt unserer Partner.</p>
        </div>
        <p class="pw-calc-hint" id="pw-calc-hint">Beispielrechnung mit Ø ~<?= $eur($perTour) ?> € Umsatz pro Rundgang. Umsatz, nicht Gewinn. Keine Verdienstgarantie.</p>
      </div>
    </div>
  </section>

  <!-- ── DASHBOARD ────────────────────────────────────────── -->
  <section class="section pw-dash-section" aria-labelledby="pw-dash-title">
    <div class="container pw-dash-layout">
      <div class="pw-dash-copy">
        <p class="section-eyebrow pw-left fade-up">Dein Partner-Dashboard</p>
        <h2 class="section-title pw-left fade-up delay-1" id="pw-dash-title">Dein Business.<br>Ein Dashboard.</h2>
        <p class="pw-copy fade-up delay-2">Jeder Partner bekommt sein eigenes Buchhaltungs-Tool. Kein Excel-Chaos, keine Zusatzsoftware. Alles, was du zum Verkaufen und Abrechnen brauchst, an einem Ort.</p>

        <div class="pw-tabs fade-up delay-3" role="tablist" aria-label="Funktionen des Partner-Dashboards" data-pw-tabs>
          <button type="button" class="pw-tab is-active" role="tab" id="pw-tab-invoices" aria-selected="true" aria-controls="pw-dash-panel" data-view="invoices">
            <span class="pw-tab-title">Rechnungen schreiben</span>
            <span class="pw-tab-text"><span>Direkt im Dashboard erstellen und sehen, was offen und was bezahlt ist.</span></span>
            <span class="pw-tab-progress" aria-hidden="true"><i></i></span>
          </button>
          <button type="button" class="pw-tab" role="tab" id="pw-tab-companies" aria-selected="false" aria-controls="pw-dash-panel" data-view="companies" tabindex="-1">
            <span class="pw-tab-title">Firmen anlegen</span>
            <span class="pw-tab-text"><span>Alle deine Kunden sauber an einem Ort.</span></span>
            <span class="pw-tab-progress" aria-hidden="true"><i></i></span>
          </button>
          <button type="button" class="pw-tab" role="tab" id="pw-tab-referrals" aria-selected="false" aria-controls="pw-dash-panel" data-view="referrals" tabindex="-1">
            <span class="pw-tab-title">Partner empfehlen</span>
            <span class="pw-tab-text"><span>Neue Partner ins Netzwerk bringen und die Prämien im Blick behalten.</span></span>
            <span class="pw-tab-progress" aria-hidden="true"><i></i></span>
          </button>
        </div>
      </div>

      <div class="pw-dash-stage" data-pw-reveal data-pw-dash>
        <div class="pw-dash" id="pw-dash-panel" role="tabpanel" aria-labelledby="pw-tab-invoices">
          <div class="pw-dash-bar" aria-hidden="true"><i></i><i></i><i></i><span>partner.visitfy.de</span></div>
          <div class="pw-dash-body">
            <nav class="pw-dash-nav" aria-hidden="true">
              <span class="pw-dash-brand">
                <img src="<?= $h($root) ?>assets/img/logo-white.svg" alt="" width="34" height="30">
                <small>Partner</small>
              </span>
              <span data-nav="invoices" class="is-active">Rechnungen</span>
              <span data-nav="companies">Firmen</span>
              <span data-nav="referrals">Empfehlungen</span>
              <span class="pw-dash-nav-muted">Einstellungen</span>
            </nav>

            <div class="pw-dash-main">
              <!-- Ansicht: Rechnungen -->
              <div class="pw-view is-active" data-view="invoices" data-toast-title="Rechnung bezahlt" data-toast-text="Hotel Fördeblick · 790 €">
                <div class="pw-dash-head"><b>Rechnungen</b><span>Oktober 2026</span><em>+ Neue Rechnung</em></div>
                <div class="pw-dash-kpis">
                  <div><small>Umsatz Oktober</small><b>8.560 €</b><em class="up">+12 % zum Vormonat</em></div>
                  <div><small>Bezahlt</small><b>7.870 €</b><em>11 Rechnungen</em></div>
                  <div><small>Offen</small><b>690 €</b><em>1 Rechnung</em></div>
                </div>
                <div class="pw-dash-chart">
                  <svg viewBox="0 0 300 80" preserveAspectRatio="none" aria-hidden="true">
                    <defs><linearGradient id="pwChartFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".18"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient></defs>
                    <path class="pw-chart-area" d="M0 68 C30 64 45 60 70 55 S110 50 130 42 S175 40 195 30 S245 22 300 8 V80 H0Z" fill="url(#pwChartFill)"/>
                    <path class="pw-chart-line" d="M0 68 C30 64 45 60 70 55 S110 50 130 42 S175 40 195 30 S245 22 300 8" pathLength="1"/>
                  </svg>
                </div>
                <ul class="pw-dash-rows pw-dash-rows--invoices">
                  <li><span>RE-2026-041</span><span>Hotel Fördeblick</span><span>790 €</span><i class="ok">Bezahlt</i></li>
                  <li><span>RE-2026-040</span><span>Autohaus Nordlicht</span><span>690 €</span><i>Offen</i></li>
                  <li><span>RE-2026-039</span><span>Physio am Hafen</span><span>640 €</span><i class="ok">Bezahlt</i></li>
                  <li><span>RE-2026-038</span><span>Weingut am Hang</span><span>820 €</span><i class="ok">Bezahlt</i></li>
                </ul>
              </div>

              <!-- Ansicht: Firmen -->
              <div class="pw-view" data-view="companies" aria-hidden="true" data-toast-title="Firma angelegt" data-toast-text="Café Hafenblick · Flensburg">
                <div class="pw-dash-head"><b>Firmen</b><span>24 Kunden</span><em>+ Firma anlegen</em></div>
                <div class="pw-dash-search">Firmen durchsuchen …</div>
                <ul class="pw-dash-rows pw-dash-rows--companies">
                  <li class="is-new"><span>Café Hafenblick</span><span>Gastronomie</span><span>Flensburg</span><i>Neu</i></li>
                  <li><span>Hotel Fördeblick</span><span>Hotel</span><span>Glücksburg</span><i></i></li>
                  <li><span>Autohaus Nordlicht</span><span>Autohaus</span><span>Schleswig</span><i></i></li>
                  <li><span>Physio am Hafen</span><span>Praxis</span><span>Kiel</span><i></i></li>
                  <li><span>Weingut am Hang</span><span>Weingut</span><span>Rheingau</span><i></i></li>
                </ul>
              </div>

              <!-- Ansicht: Empfehlungen -->
              <div class="pw-view" data-view="referrals" aria-hidden="true" data-toast-title="Prämie gutgeschrieben" data-toast-text="Mia K. · 1. Verkauf · <?= $eur($demoRefHalf) ?> €">
                <div class="pw-dash-head"><b>Empfehlungen</b><span>2 geworbene Partner</span><em>Partner empfehlen</em></div>
                <div class="pw-dash-kpis pw-dash-kpis--two">
                  <div><small>Prämien gesamt</small><b><?= $eur($demoRefTotal) ?> €</b><em class="up"><?= $refPct ?> % der ersten <?= $refSales ?> Verkäufe</em></div>
                  <div><small>Geworbene Partner</small><b>2</b><em>1 davon komplett</em></div>
                </div>
                <ul class="pw-dash-rows pw-dash-rows--refs">
                  <li>
                    <span>Jonas P.</span>
                    <span class="pw-dash-steps" aria-label="2 von 2 Verkäufen"><b class="on"></b><b class="on"></b></span>
                    <span><?= $eur($demoRefDone) ?> €</span><i class="ok">Abgeschlossen</i>
                  </li>
                  <li>
                    <span>Mia K.</span>
                    <span class="pw-dash-steps" aria-label="1 von 2 Verkäufen"><b class="on"></b><b></b></span>
                    <span><?= $eur($demoRefHalf) ?> €</span><i>1 von <?= $refSales ?></i>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="pw-dash-toast" aria-hidden="true" data-toast>
          <span class="pw-dash-toast-icon">✓</span>
          <div><b data-toast-out="title">Rechnung bezahlt</b><small data-toast-out="text">Hotel Fördeblick · 790 €</small></div>
        </div>
        <p class="pw-dash-caption">Beispielansicht mit Demodaten</p>
      </div>
    </div>
  </section>

  <!-- ── EMPFEHLUNGSPROGRAMM ──────────────────────────────── -->
  <section class="section pw-ref-section" aria-labelledby="pw-ref-title" data-pw-reveal data-pw-ref-section>
    <div class="container">
      <p class="section-eyebrow fade-up">Empfehlungsprogramm</p>
      <h2 class="section-title fade-up delay-1" id="pw-ref-title">Empfiehl Visitfy.<br>Verdien mit.</h2>
      <p class="section-sub fade-up delay-2">Du kennst jemanden, der gut verkaufen kann? Bring ihn als Partner ins Netzwerk und verdiene an seinen ersten Verkäufen mit.</p>

      <div class="pw-ref-grid">
        <div class="pw-ref-hero glass fade-up delay-2">
          <div class="pw-ref-ring">
            <svg viewBox="0 0 120 120" aria-hidden="true">
              <circle cx="60" cy="60" r="52" class="pw-ref-ring-bg"/>
              <circle cx="60" cy="60" r="52" class="pw-ref-ring-fg" pathLength="100" style="--pct:<?= $refPct ?>" data-ref-ring/>
            </svg>
            <span class="pw-ref-pct"><span data-ref-pct data-target="<?= $refPct ?>"><?= $refPct ?></span><small>%</small></span>
          </div>
          <p class="pw-ref-claim">vom Umsatz der ersten <?= $refSales ?> Verkäufe jedes Partners, den du wirbst.</p>
        </div>

        <div class="pw-ledger glass fade-up delay-3" data-pw-ref data-bonus="<?= $h(round($refBonus, 2)) ?>">
          <div class="pw-ledger-head">
            <span>Gutschrift Empfehlungsprämie</span>
            <span class="pw-ledger-tag">Beispiel</span>
          </div>
          <table class="pw-ledger-table">
            <thead>
              <tr><th scope="col">Posten</th><th scope="col">Umsatz</th><th scope="col">Satz</th><th scope="col">Prämie</th></tr>
            </thead>
            <tbody>
              <?php for ($s = 1; $s <= $refSales; $s++): ?>
                <tr style="--r:<?= $s ?>">
                  <th scope="row"><?= $s ?>. Verkauf<span class="pw-ledger-long"> deines geworbenen Partners</span></th>
                  <td>~<?= $eur($perTour) ?> €</td>
                  <td><?= $refPct ?> %</td>
                  <td>~<?= $eur($refPerSale) ?> €</td>
                </tr>
              <?php endfor; ?>
            </tbody>
            <tfoot>
              <tr><th scope="row" colspan="3">Deine Prämie pro geworbenem Partner</th><td>≈ <?= $eur($refBonus) ?> €</td></tr>
            </tfoot>
          </table>

          <div class="pw-ledger-calc">
            <span>Bei</span>
            <div class="pw-stepper">
              <button type="button" data-step="-1" aria-label="Einen Partner weniger">−</button>
              <output data-out="count" aria-live="polite">3</output>
              <button type="button" data-step="1" aria-label="Einen Partner mehr">+</button>
            </div>
            <span>geworbenen Partnern</span>
            <b class="pw-ledger-sum">≈ <span data-out="sum"><?= $eur(round($refBonus) * 3) ?></span> €</b>
          </div>
          <p class="pw-ledger-note">Gerechnet mit dem Ø-Umsatz von ~<?= $eur($perTour) ?> € pro Rundgang.</p>
        </div>
      </div>
    </div>
  </section>

<?php if ($voices): ?>
  <!-- ── PARTNERSTIMMEN (partials/partner-testimonials.php) ── -->
  <section class="section pw-voices" aria-labelledby="pw-voices-title">
    <div class="container">
      <div class="pw-voices-head">
        <p class="section-eyebrow pw-left fade-up">Stimmen aus dem Netzwerk</p>
        <h2 class="section-title pw-left fade-up delay-1" id="pw-voices-title">Das sagen unsere Partner.</h2>
      </div>

      <div class="pw-voices-stage fade-up delay-2" data-pw-voices>
        <div class="pw-voices-panels">
          <?php foreach ($voices as $i => $v): $on = $i === 0; ?>
            <figure class="pw-voice<?= $on ? ' is-active' : '' ?><?= $v['platzhalter'] ? ' is-placeholder' : '' ?>" id="pw-voice-<?= $i ?>" role="tabpanel" aria-labelledby="pw-voice-tab-<?= $i ?>">
              <div class="pw-voice-photo">
                <?php if ($v['foto'] !== ''): ?>
                  <img src="<?= $h($root . $v['foto']) ?>" alt="<?= $h($v['name']) ?>, Visitfy-Partner in <?= $h($v['stadt']) ?>" width="800" height="1000" loading="lazy" decoding="async">
                <?php else: ?>
                  <span class="pw-voice-ph"><?= $voiceSilhouette ?><small>Foto folgt</small></span>
                <?php endif; ?>
              </div>
              <div class="pw-voice-body">
                <blockquote class="pw-voice-quote"><p>„<?= $h($v['zitat']) ?>“</p></blockquote>
                <div class="pw-voice-foot">
                  <figcaption class="pw-voice-meta">
                    <b><?= $h($v['name']) ?></b>
                    <span><?= $h(implode(' · ', array_filter([$v['seit'], $v['stadt']]))) ?></span>
                  </figcaption>
                  <p class="pw-voice-figure">
                    <?php if ($v['umsatz'] > 0): ?>
                      <span class="pw-voice-num">ca. <span data-voice-num data-target="<?= $v['umsatz'] ?>"><?= $eur($v['umsatz']) ?></span>&nbsp;€</span>
                    <?php else: ?>
                      <span class="pw-voice-num">x.xxx&nbsp;€</span>
                    <?php endif; ?>
                    <small>Ø Umsatz pro Monat<sup><a href="#pw-footnote" aria-label="Hinweis zu den Zahlen">*</a></sup></small>
                  </p>
                </div>
              </div>
            </figure>
          <?php endforeach; ?>
        </div>

        <div class="pw-voices-nav">
          <div class="pw-voices-tabs" role="tablist" aria-label="Partnerstimmen" data-pw-voice-tabs style="--pw-voice-n:<?= count($voices) ?>">
            <?php foreach ($voices as $i => $v): $on = $i === 0; ?>
              <button type="button" class="pw-voice-tab<?= $on ? ' is-active' : '' ?>" role="tab" id="pw-voice-tab-<?= $i ?>" aria-controls="pw-voice-<?= $i ?>" aria-selected="<?= $on ? 'true' : 'false' ?>"<?= $on ? '' : ' tabindex="-1"' ?> aria-label="<?= $h($v['name'] . ', ' . $v['stadt']) ?>">
                <span class="pw-voice-tab-progress" aria-hidden="true"></span>
                <span class="pw-voice-thumb" aria-hidden="true">
                  <?php if ($v['foto'] !== ''): ?><img src="<?= $h($root . $v['foto']) ?>" alt="" width="72" height="72" loading="lazy" decoding="async"><?php else: ?><?= $voiceSilhouette ?><?php endif; ?>
                </span>
                <span class="pw-voice-tab-label" aria-hidden="true"><?= $h($v['stadt']) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <p class="pw-voices-note">
        <?php if ($voicesReal === count($voices)): ?>
          Echte Partner, eigene Angaben: Zitate und Umsätze stammen von den Partnern selbst und sind gerundet. Umsatz, nicht Gewinn.
        <?php else: ?>
          Vorschau mit Platzhaltern. Live erscheinen nur Partnerstimmen, die in partials/partner-testimonials.php freigegeben sind.
        <?php endif; ?>
      </p>
    </div>
  </section>
<?php endif; ?>

  <!-- ── WARUM VISITFY (Texte aus dem Admin) ──────────────── -->
  <section class="section pw-model" aria-labelledby="partner-proof-heading">
    <div class="container">
      <div class="pw-model-head">
        <p class="section-eyebrow pw-left fade-up"><?= $c('proof_eyebrow', 'Warum Visitfy') ?></p>
        <h2 class="section-title pw-left fade-up delay-1" id="partner-proof-heading"><?= $c('proof_title', 'Was du von uns bekommst.') ?></h2>
        <p class="pw-copy fade-up delay-2"><?= $c('proof_sub', 'Ein funktionierendes System, ein klarer Markt und direkte Unterstützung.') ?></p>
      </div>

      <ul class="pw-model-list" data-pw-reveal>
        <li class="pw-model-row" style="--r:0">
          <h3><?= $c('card_1_title', 'Funktionierendes System') ?></h3>
          <p><?= $c('card_1_text', 'Wir haben’s getestet. Verfeinert. Wiederholt. Was du bekommst, ist nicht neu – sondern durchdacht, schlank und wirksam.') ?></p>
        </li>
        <li class="pw-model-row" style="--r:1">
          <h3><?= $c('card_2_title', 'Geringes Startkapital') ?></h3>
          <p><?= $c('card_2_text', 'Du brauchst kein Büro, kein Lager und keine teure Ausstattung. Starte smart – mit einem klaren Plan und wenig Risiko. Was zählt: dein Einsatz und dein Wille, wirklich loszulegen.') ?></p>
        </li>
        <li class="pw-model-row" style="--r:2">
          <h3><?= $c('card_3_title', 'Nicht allein. Nie.') ?></h3>
          <p><?= $c('card_3_text', 'Bei Fragen rufst du nicht irgendwo an – sondern direkt bei uns. Du bekommst echten Support, kein Ticketsystem. Persönlich. Ehrlich. Schnell.') ?></p>
        </li>
      </ul>
    </div>
  </section>

  <!-- ── FAQ (identisch mit FAQPage-Schema) ───────────────── -->
  <section class="section pw-faq-section" id="partner-faq" aria-labelledby="pw-faq-title">
    <div class="container">
      <p class="section-eyebrow fade-up">Häufige Fragen</p>
      <h2 class="section-title fade-up delay-1" id="pw-faq-title">Alles zur Visitfy-Partnerschaft</h2>
      <div class="faq-list fade-up delay-2">
        <?php foreach ($faq as $i => $f): ?>
          <div class="faq-item">
            <button class="faq-question" aria-expanded="false" aria-controls="pw-faq-a<?= $i ?>" id="pw-faq-q<?= $i ?>">
              <?= $h($f['q']) ?>
              <span class="faq-arrow" aria-hidden="true">▼</span>
            </button>
            <div class="faq-answer" role="region" id="pw-faq-a<?= $i ?>" aria-labelledby="pw-faq-q<?= $i ?>">
              <div class="faq-answer-inner"><p><?= $h($f['a']) ?></p></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── FORMULAR ─────────────────────────────────────────── -->
  <section class="section partner-form-section" id="partner-form" aria-labelledby="partner-form-heading">
    <div class="container">
      <div class="pw-form-layout">
        <div class="pw-form-intro">
          <p class="section-eyebrow pw-left fade-up"><?= $c('form_eyebrow', 'Jetzt Partner werden') ?></p>
          <h2 class="section-title pw-left fade-up delay-1" id="partner-form-heading"><?= $c('form_title', 'Lass uns sprechen.') ?></h2>
          <p class="pw-copy fade-up delay-2"><?= $c('fit_text', 'Du bist zuverlässig, kommunikativ und willst dir was Eigenes aufbauen? Dann bist du hier richtig. Fülle jetzt das kurze Formular aus – wir melden uns persönlich bei dir.') ?></p>
          <p class="pw-form-note fade-up delay-3"><?= $c('fit_note', 'Unverbindlich. Ohne Risiko. Nur echtes Interesse zählt.') ?></p>
        </div>

        <div class="contact-form-box fade-up delay-2">
          <form method="post" action="<?= $h($root) ?>pages/partner-handler.php" data-ajax novalidate>
            <!-- Honeypot -->
            <div class="form-honeypot" aria-hidden="true">
              <label for="hp_website">Website</label>
              <input type="text" id="hp_website" name="hp_website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="p_name"><?= $c('form_name_label', 'Name *') ?></label>
                <input type="text" id="p_name" name="name" required autocomplete="name" placeholder="<?= $c('form_name_placeholder', 'Max Mustermann') ?>">
              </div>
              <div class="form-group">
                <label for="p_firma"><?= $c('form_company_label', 'Firma') ?></label>
                <input type="text" id="p_firma" name="firma" autocomplete="organization" placeholder="<?= $c('form_company_placeholder', 'Muster GmbH') ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="p_email"><?= $c('form_email_label', 'E-Mail *') ?></label>
                <input type="email" id="p_email" name="email" required autocomplete="email" placeholder="<?= $c('form_email_placeholder', 'name@firma.de') ?>">
              </div>
              <div class="form-group">
                <label for="p_stadt">Stadt / Region *</label>
                <input type="text" id="p_stadt" name="stadt" required autocomplete="address-level2" maxlength="120" placeholder="z. B. München">
              </div>
            </div>

            <div class="form-group">
              <label for="p_rolle"><?= $c('form_role_label', 'Rolle / Bereich') ?></label>
              <select id="p_rolle" name="rolle">
                <option value=""><?= $c('form_role_placeholder', 'Bitte wählen…') ?></option>
                <option value="sales"><?= $c('form_role_option_1', 'Vertrieb / Sales') ?></option>
                <option value="agency"><?= $c('form_role_option_2', 'Agentur / Marketing') ?></option>
                <option value="self-employed"><?= $c('form_role_option_3', 'Selbstständig / Unternehmerisch') ?></option>
                <option value="other"><?= $c('form_role_option_4', 'Sonstiges') ?></option>
              </select>
            </div>

            <div class="form-group">
              <label for="p_nachricht"><?= $c('form_message_label', 'Nachricht *') ?></label>
              <textarea id="p_nachricht" name="nachricht" required rows="5" placeholder="<?= $c('form_message_placeholder', 'Erzähl uns kurz, warum du Partner werden möchtest…') ?>"></textarea>
            </div>

            <div class="form-check">
              <input type="checkbox" id="p_dsgvo" name="dsgvo" required>
              <label for="p_dsgvo">
                <?= $c('form_privacy_prefix', 'Ich habe die') ?>
                <a href="<?= $h(visitfy_page_url('datenschutz')) ?>"><?= $c('form_privacy_link', 'Datenschutzerklärung') ?></a>
                <?= $c('form_privacy_suffix', 'gelesen und bin mit der Verarbeitung meiner Daten zur Bearbeitung meiner Anfrage einverstanden. *') ?>
              </label>
            </div>

            <?= visitfy_turnstile_widget() ?>
            <button type="submit" class="btn btn-primary js-btnfx-partner" style="width:100%"><?= $c('form_submit_text', 'Jetzt Partner werden') ?></button>
            <div class="form-status" role="alert"></div>
          </form>
        </div>
      </div>

      <p class="pw-footnote" id="pw-footnote">
        * Durchschnittswerte aller <?= $partners ?> aktiven Visitfy-Partner, Stand <?= $h($net['stand']) ?>.
        Angegeben ist Umsatz, nicht Gewinn. Einzelne Partner liegen darüber oder darunter;
        das Ergebnis hängt von persönlichem Einsatz und Region ab. Keine Verdienstgarantie.
        <?php if ($voicesReal > 0): ?>Umsätze in den Partnerstimmen: eigene Angaben der jeweiligen Partner, gerundet.<?php endif; ?>
      </p>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
