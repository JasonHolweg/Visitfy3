<?php
/**
 * Visitfy3 – pages/partner.php
 * Partner-werden Seite: Zahlen, Netzwerk-Karte, Verdienst-Rechner,
 * Partner-Dashboard, Empfehlungsprogramm, Formular.
 * Zahlen + Standorte: partials/partner-network.php
 */
require __DIR__ . '/../partials/cms.php';
require_once __DIR__ . '/../partials/turnstile.php';
$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? visitfy_base_path() : '../';
$contentConfig = visitfy_load_json(__DIR__ . '/../assets/data/content.json', []);
$net = require __DIR__ . '/../partials/partner-network.php';
$map = require __DIR__ . '/../partials/germany-map-paths.php';

$h = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$c = static fn(string $key, string $default): string => htmlspecialchars((string)visitfy_get($contentConfig, 'partner.' . $key, $default), ENT_QUOTES, 'UTF-8');
$eur = static fn(float $v): string => number_format($v, 0, ',', '.');

$partners   = (int)$net['stats']['partners'];
$avgRevenue = (int)$net['stats']['avg_revenue'];
$avgTours   = (int)$net['stats']['avg_tours'];
$perTour    = $avgTours > 0 ? $avgRevenue / $avgTours : 0;
$refPct     = (int)$net['referral']['percent'];
$refSales   = (int)$net['referral']['sales'];
$refBonus   = $perTour * $refSales * $refPct / 100;
$regions    = count(array_unique(array_column($net['active'], 'region')));
$openNames  = array_column($net['open'], 'name');

$hq = $net['active'][0];
foreach ($net['active'] as $p) { if (!empty($p['hq'])) { $hq = $p; break; } }
[$mapW, $mapH] = array_map('floatval', array_slice(explode(' ', $map['viewbox']), 2));

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

$activeNames = array_column($net['active'], 'name');
$listDe = static function (array $items): string {
    if (count($items) < 2) return implode('', $items);
    $last = array_pop($items);
    return implode(', ', $items) . ' und ' . $last;
};

/* FAQ: sichtbar auf der Seite UND als FAQPage-Schema (beides muss identisch sein) */
$faq = [
    [
        'q' => 'Wie viel verdienen Visitfy-Partner?',
        'a' => 'Unsere ' . $partners . ' aktiven Partner machen im Schnitt ' . $eur($avgRevenue) . ' € Umsatz pro Monat (Stand ' . $net['stand'] . '). Das entspricht rund ' . $avgTours . ' verkauften 360°-Rundgängen im Monat bzw. ca. ' . $eur($perTour) . ' € Umsatz pro Rundgang. Einige Partner liegen deutlich darüber. Es handelt sich um Umsatz, nicht um Gewinn – das Ergebnis hängt von deinem Einsatz und deiner Region ab.',
    ],
    [
        'q' => 'In welchen Städten gibt es schon Visitfy-Partner?',
        'a' => 'Aktuell gibt es Visitfy-Partner in ' . $listDe($activeNames) . '. Das sind ' . $partners . ' Standorte in ' . $regions . ' Bundesländern.',
    ],
    [
        'q' => 'Wo sucht Visitfy neue Partner?',
        'a' => 'Wir suchen deutschlandweit, mit besonderem Fokus auf Süddeutschland. In Bayern und Baden-Württemberg sind Regionen wie ' . $listDe($openNames) . ' noch frei. Wer dort startet, ist in seiner Region der erste Visitfy-Partner.',
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
$pageDesc  = 'Visitfy-Partner machen im Schnitt ' . $eur($avgRevenue) . ' € Umsatz im Monat mit rund ' . $avgTours . ' virtuellen 360°-Rundgängen. ' . $partners . ' Partner in ' . $regions . ' Bundesländern – Gebiete in Bayern und Baden-Württemberg noch frei.';
$canonicalPath = '/pages/partner.php';
$pageOgImg = 'assets/img/og-partner.png';
$extraScripts = ['assets/js/partner.js'];

$pageUrl = 'https://visitfy.de' . $canonicalPath;
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => 'https://visitfy.de/#organization',
            'name' => 'Visitfy',
            'url' => 'https://visitfy.de/',
            'logo' => 'https://visitfy.de/assets/img/logo.svg',
            'email' => 'info@visitfy.de',
            'description' => 'Visitfy erstellt virtuelle 360°-Rundgänge für Unternehmen und vertreibt sie über ein deutschlandweites Partnernetzwerk.',
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Flensburg', 'addressCountry' => 'DE'],
            'areaServed' => array_merge(
                [['@type' => 'Country', 'name' => 'Deutschland']],
                array_map(static fn($n) => ['@type' => 'City', 'name' => $n], $activeNames)
            ),
        ],
        [
            '@type' => 'WebPage',
            '@id' => $pageUrl . '#webpage',
            'url' => $pageUrl,
            'name' => $pageTitle,
            'description' => $pageDesc,
            'inLanguage' => 'de-DE',
            'isPartOf' => ['@type' => 'WebSite', '@id' => 'https://visitfy.de/#website', 'url' => 'https://visitfy.de/', 'name' => 'Visitfy'],
            'about' => ['@id' => 'https://visitfy.de/#organization'],
            'primaryImageOfPage' => 'https://visitfy.de/' . $pageOgImg,
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Start', 'item' => 'https://visitfy.de/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Partner werden', 'item' => $pageUrl],
                ],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $pageUrl . '#faq',
            'mainEntity' => array_map(static fn($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faq),
        ],
    ],
]];

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content" class="pw">

  <!-- ── HERO ─────────────────────────────────────────────── -->
  <section class="pw-hero" aria-labelledby="pw-hero-title">
    <div class="pw-hero-aurora" aria-hidden="true"><span></span><span></span></div>
    <div class="pw-hero-grid" aria-hidden="true"></div>

    <div class="container pw-hero-inner">
      <p class="pw-live-pill fade-up">
        <span class="pw-live-dot" aria-hidden="true"></span>
        <?= $partners ?> aktive Partner · <?= $regions ?> Bundesländer
      </p>

      <h1 id="pw-hero-title" class="pw-hero-title fade-up delay-1">
        <span class="pw-hero-kicker">Unsere Partner machen im Schnitt</span>
        <span class="pw-hero-number">
          <span data-countup data-target="<?= $avgRevenue ?>"><?= $eur($avgRevenue) ?></span><span class="pw-hero-currency">&nbsp;€</span>
        </span>
        <span class="pw-hero-kicker">Umsatz. Jeden Monat.<sup><a href="#pw-footnote" aria-label="Hinweis zu den Zahlen">*</a></sup></span>
      </h1>

      <p class="pw-hero-sub fade-up delay-2">
        Mit rund <?= $avgTours ?> verkauften 360°-Rundgängen im Monat. Und die Besten liegen deutlich darüber.
        Du bringst Einsatz mit – wir das System, das funktioniert.
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
          <dt>Ø Rundgänge / Monat</dt>
          <dd><span data-countup data-target="<?= $avgTours ?>"><?= $avgTours ?></span></dd>
        </div>
        <div class="pw-stat">
          <dt>Ø Umsatz / Rundgang</dt>
          <dd>~<span data-countup data-target="<?= round($perTour) ?>"><?= $eur($perTour) ?></span>&nbsp;€</dd>
        </div>
        <div class="pw-stat">
          <dt>Empfehlungsprämie</dt>
          <dd><span data-countup data-target="<?= $refPct ?>"><?= $refPct ?></span>&nbsp;%</dd>
        </div>
      </dl>
    </div>

    <a href="#pw-map" class="pw-scroll-cue" aria-label="Weiter zur Karte"><span></span></a>
  </section>

  <!-- ── KARTE ────────────────────────────────────────────── -->
  <section class="section pw-map-section" id="pw-map" aria-labelledby="pw-map-title" data-pw-map>
    <div class="container pw-map-layout">
      <div class="pw-map-copy">
        <p class="section-eyebrow pw-left fade-up">Das Visitfy-Netzwerk</p>
        <h2 class="section-title pw-left fade-up delay-1" id="pw-map-title"><?= $partners ?> Partner.<br>Von Flensburg bis Stuttgart.</h2>
        <p class="pw-copy fade-up delay-2">
          Jeder Punkt ist ein Unternehmer, der mit Visitfy sein eigenes Business aufgebaut hat.
          Im Norden sind wir stark – <strong>im Süden ist fast alles noch frei.</strong>
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
          <span><i class="pw-legend-dot pw-legend-dot--open"></i>Gebiet frei</span>
        </div>
      </div>

      <div class="pw-map-wrap">
        <figure class="pw-map" role="img" aria-label="Deutschlandkarte mit <?= $partners ?> Visitfy-Partnern, Schwerpunkt Norden; offene Gebiete in Süddeutschland: <?= $h(implode(', ', $openNames)) ?>.">
          <svg viewBox="<?= $h($map['viewbox']) ?>" class="pw-map-svg" aria-hidden="true" focusable="false">
            <defs>
              <pattern id="pwDots" width="6.5" height="6.5" patternUnits="userSpaceOnUse">
                <circle cx="3.25" cy="3.25" r="1.05" fill="#ffffff" />
              </pattern>
              <radialGradient id="pwSouthGlow" cx="50%" cy="50%" r="50%">
                <stop offset="0" stop-color="#ffffff" stop-opacity="0.22" />
                <stop offset="0.55" stop-color="#ffffff" stop-opacity="0.07" />
                <stop offset="1" stop-color="#ffffff" stop-opacity="0" />
              </radialGradient>
              <linearGradient id="pwArcGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#ffffff" stop-opacity="0.55" />
                <stop offset="1" stop-color="#ffffff" stop-opacity="0.12" />
              </linearGradient>
              <path id="pwDE" d="<?= $map['outline'] ?>" pathLength="1" />
              <clipPath id="pwClip"><use href="#pwDE" /></clipPath>
            </defs>

            <g class="pw-map-base">
              <use href="#pwDE" class="pw-map-dots" fill="url(#pwDots)" />
              <path class="pw-map-states" d="<?= $map['states'] ?>" />
              <use href="#pwDE" class="pw-map-outline" />
            </g>

            <g clip-path="url(#pwClip)">
              <ellipse class="pw-south-glow" cx="250" cy="610" rx="250" ry="140" fill="url(#pwSouthGlow)" />
              <rect class="pw-scan" x="0" y="0" width="<?= $mapW ?>" height="60" />
            </g>

            <g class="pw-arcs">
              <?php foreach ($net['active'] as $i => $p): if (!empty($p['hq'])) continue; ?>
                <path d="<?= $arc($hq, $p) ?>" pathLength="1" style="--i:<?= $i ?>" />
              <?php endforeach; ?>
            </g>

            <g class="pw-pins-open">
              <?php foreach ($net['open'] as $i => $p): ?>
                <g transform="translate(<?= $p['x'] ?> <?= $p['y'] ?>)">
                  <g class="pw-pin pw-pin--open" style="--i:<?= $i ?>">
                    <circle class="pw-pin-ring" r="8" />
                    <circle class="pw-pin-core" r="2.6" />
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
              <span class="pw-label" data-label-id="a<?= $i ?>" style="left:<?= round($p['x'] / $mapW * 100, 2) ?>%;top:<?= round($p['y'] / $mapH * 100, 2) ?>%"><?= $h($p['name']) ?></span>
            <?php endforeach; ?>
            <?php foreach ($net['open'] as $i => $p): ?>
              <span class="pw-label pw-label--open" style="left:<?= round($p['x'] / $mapW * 100, 2) ?>%;top:<?= round($p['y'] / $mapH * 100, 2) ?>%;--i:<?= $i ?>"><?= $h($p['name']) ?></span>
            <?php endforeach; ?>
            <span class="pw-map-tag" style="left:<?= round(250 / $mapW * 100, 2) ?>%;top:<?= round(735 / $mapH * 100, 2) ?>%">
              <span class="pw-live-dot"></span>Süden: Gebiete frei
            </span>
          </div>
        </figure>
      </div>
    </div>

    <!-- Süden-CTA -->
    <div class="container">
      <div class="pw-south glass fade-up">
        <div class="pw-south-copy">
          <p class="section-eyebrow pw-left">Wir wachsen Richtung Süden</p>
          <h3 class="pw-south-title">Bayern &amp; Baden-Württemberg sind offen.</h3>
          <p class="pw-copy">
            Riesige Märkte, kaum Wettbewerb, noch kein Visitfy-Partner. Wer jetzt startet,
            besetzt seine Region als Erster – mit allem, was im Norden schon funktioniert.
          </p>
        </div>
        <div class="pw-south-side">
          <ul class="pw-chips" aria-label="Offene Regionen">
            <?php foreach ($openNames as $n): ?><li><?= $h($n) ?></li><?php endforeach; ?>
            <li class="pw-chips-more">+ deine Stadt</li>
          </ul>
          <a href="#partner-form" class="btn btn-primary js-btnfx-partner" data-prefill-city>Meine Region sichern</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ── RECHNER ──────────────────────────────────────────── -->
  <section class="section pw-calc-section" aria-labelledby="pw-calc-title">
    <div class="container">
      <p class="section-eyebrow fade-up">Dein Potenzial</p>
      <h2 class="section-title fade-up delay-1" id="pw-calc-title">Was ist für dich drin?</h2>
      <p class="section-sub fade-up delay-2">Schieb den Regler. Gerechnet mit dem echten Durchschnittsumsatz unserer Partner pro Rundgang.</p>

      <div class="pw-calc glass fade-up delay-3" data-pw-calc data-per-tour="<?= $h(round($perTour, 3)) ?>" data-avg="<?= $avgTours ?>">
        <div class="pw-calc-control">
          <label for="pw-tours" class="pw-calc-label">
            Verkaufte Rundgänge pro Monat
            <output for="pw-tours" class="pw-calc-tours" data-out="tours"><?= $avgTours ?></output>
          </label>
          <div class="pw-range">
            <input type="range" id="pw-tours" min="1" max="30" step="1" value="<?= $avgTours ?>" aria-describedby="pw-calc-hint">
            <span class="pw-range-avg" style="left:calc(<?= round(($avgTours - 1) / 29 * 100, 2) ?>% + <?= round(11 - ($avgTours - 1) / 29 * 22, 2) ?>px)" aria-hidden="true">Ø Partner</span>
          </div>
          <div class="pw-range-scale" aria-hidden="true"><span>1</span><span>10</span><span>20</span><span>30</span></div>
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
          <p class="pw-calc-badge" data-out="badge" aria-live="polite">Genau der Schnitt unserer Partner.</p>
        </div>
        <p class="pw-calc-hint" id="pw-calc-hint">Beispielrechnung mit Ø ~<?= $eur($perTour) ?> € Umsatz pro Rundgang. Umsatz, nicht Gewinn – keine Verdienstgarantie.</p>
      </div>
    </div>
  </section>

  <!-- ── DASHBOARD ────────────────────────────────────────── -->
  <section class="section pw-dash-section" aria-labelledby="pw-dash-title">
    <div class="container pw-dash-layout">
      <div class="pw-dash-copy">
        <p class="section-eyebrow pw-left fade-up">Dein Partner-Dashboard</p>
        <h2 class="section-title pw-left fade-up delay-1" id="pw-dash-title">Dein Business.<br>Ein Dashboard.</h2>
        <p class="pw-copy fade-up delay-2">Jeder Partner bekommt sein eigenes Buchhaltungs-Tool. Kein Excel-Chaos, keine Zusatzsoftware – alles, was du zum Verkaufen und Abrechnen brauchst, an einem Ort.</p>

        <ul class="pw-features">
          <li class="fade-up delay-2">
            <span class="pw-feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5M10 13h6M10 17h6"/></svg></span>
            <div><h3>Rechnungen schreiben</h3><p>Direkt aus dem Dashboard erstellen und den Überblick behalten, was offen und was bezahlt ist.</p></div>
          </li>
          <li class="fade-up delay-3">
            <span class="pw-feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 21V7l8-4 8 4v14"/><path d="M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01"/></svg></span>
            <div><h3>Firmen anlegen</h3><p>Alle deine Kunden sauber an einem Ort – vom ersten Kontakt bis zum fertigen Rundgang.</p></div>
          </li>
          <li class="fade-up delay-4">
            <span class="pw-feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><circle cx="17" cy="16" r="3"/><path d="M10.5 9.5l4.2 4.4M3 20c.5-3 2.6-5 5-5"/></svg></span>
            <div><h3>Partner empfehlen</h3><p>Bring neue Partner ins Netzwerk und sieh deine Prämien direkt im Dashboard.</p></div>
          </li>
        </ul>
      </div>

      <div class="pw-dash-stage" data-pw-reveal>
        <div class="pw-dash" aria-hidden="true">
          <div class="pw-dash-bar"><i></i><i></i><i></i><span>partner.visitfy.de</span></div>
          <div class="pw-dash-body">
            <nav class="pw-dash-nav">
              <span class="pw-dash-logo">V</span>
              <span class="is-active">Übersicht</span>
              <span>Rechnungen</span>
              <span>Firmen</span>
              <span>Empfehlungen</span>
            </nav>
            <div class="pw-dash-main">
              <p class="pw-dash-hello">Guten Morgen 👋</p>
              <div class="pw-dash-kpis">
                <div><small>Umsatz Oktober</small><b>8.560 €</b><em>+12 %</em></div>
                <div><small>Rundgänge</small><b>12</b><em>diesen Monat</em></div>
                <div><small>Prämien</small><b>998 €</b><em>2 Empfehlungen</em></div>
              </div>
              <div class="pw-dash-chart">
                <svg viewBox="0 0 300 80" preserveAspectRatio="none">
                  <defs><linearGradient id="pwChartFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".18"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient></defs>
                  <path class="pw-chart-area" d="M0 68 C30 64 45 60 70 55 S110 50 130 42 S175 40 195 30 S245 22 300 8 V80 H0Z" fill="url(#pwChartFill)"/>
                  <path class="pw-chart-line" d="M0 68 C30 64 45 60 70 55 S110 50 130 42 S175 40 195 30 S245 22 300 8" pathLength="1"/>
                </svg>
              </div>
              <ul class="pw-dash-rows">
                <li><span>RE-2026-041</span><span>Hotel Fördeblick</span><span>790 €</span><i class="ok">Bezahlt</i></li>
                <li><span>RE-2026-040</span><span>Autohaus Nordlicht</span><span>690 €</span><i>Offen</i></li>
                <li><span>RE-2026-039</span><span>Physio am Hafen</span><span>640 €</span><i class="ok">Bezahlt</i></li>
                <li><span>RE-2026-038</span><span>Weingut am Hang</span><span>820 €</span><i class="ok">Bezahlt</i></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="pw-dash-toast" aria-hidden="true">
          <span class="pw-dash-toast-icon">✓</span>
          <div><b>Rechnung bezahlt</b><small>Hotel Fördeblick · 790 €</small></div>
        </div>
        <p class="pw-dash-caption">Beispielansicht mit Demodaten</p>
      </div>
    </div>
  </section>

  <!-- ── EMPFEHLUNGSPROGRAMM ──────────────────────────────── -->
  <section class="section pw-ref-section" aria-labelledby="pw-ref-title" data-pw-reveal>
    <div class="container">
      <p class="section-eyebrow fade-up">Empfehlungsprogramm</p>
      <h2 class="section-title fade-up delay-1" id="pw-ref-title">Empfiehl Visitfy.<br>Verdien mit.</h2>
      <p class="section-sub fade-up delay-2">Du kennst jemanden, der gut verkaufen kann? Bring ihn als Partner ins Netzwerk – und kassier mit.</p>

      <div class="pw-ref-grid">
        <div class="pw-ref-hero glass fade-up delay-2">
          <div class="pw-ref-ring">
            <svg viewBox="0 0 120 120" aria-hidden="true">
              <circle cx="60" cy="60" r="52" class="pw-ref-ring-bg"/>
              <circle cx="60" cy="60" r="52" class="pw-ref-ring-fg" pathLength="100" style="--pct:<?= $refPct ?>"/>
            </svg>
            <span class="pw-ref-pct"><?= $refPct ?><small>%</small></span>
          </div>
          <p class="pw-ref-claim">vom Umsatz der ersten <?= $refSales ?> Verkäufe jedes Partners, den du wirbst.</p>
        </div>

        <ol class="pw-ref-steps">
          <li class="glass fade-up delay-2"><span>01</span><div><h3>Du empfiehlst</h3><p>Du bringst jemanden mit, der Lust auf ein eigenes Business hat.</p></div></li>
          <li class="glass fade-up delay-3"><span>02</span><div><h3>Er wird Partner</h3><p>Wir übernehmen Onboarding und Support – du musst nichts erklären.</p></div></li>
          <li class="glass fade-up delay-4"><span>03</span><div><h3>Du verdienst mit</h3><p><?= $refPct ?> % vom Umsatz seiner ersten <?= $refSales ?> Verkäufe gehen an dich.</p></div></li>
        </ol>
      </div>

      <div class="pw-ref-calc glass fade-up" data-pw-ref data-bonus="<?= $h(round($refBonus, 2)) ?>">
        <p>Wenn du</p>
        <div class="pw-stepper">
          <button type="button" data-step="-1" aria-label="Einen Partner weniger">−</button>
          <output data-out="count" aria-live="polite">3</output>
          <button type="button" data-step="1" aria-label="Einen Partner mehr">+</button>
        </div>
        <p>Partner wirbst, sind das rund</p>
        <p class="pw-ref-sum"><span data-out="sum"><?= $eur(round($refBonus) * 3) ?></span>&nbsp;€</p>
        <p class="pw-ref-note">zusätzlich – bei Ø ~<?= $eur($perTour) ?> € pro Rundgang, also ca. <?= $eur($refBonus) ?> € pro geworbenem Partner.</p>
      </div>
    </div>
  </section>

  <!-- ── WARUM VISITFY (CMS) ──────────────────────────────── -->
  <section class="section partner-proof-section" aria-labelledby="partner-proof-heading">
    <div class="container">
      <div class="partner-proof-intro">
        <p class="section-eyebrow fade-up"><?= $c('proof_eyebrow', 'Warum Visitfy') ?></p>
        <h2 class="section-title fade-up delay-1" id="partner-proof-heading"><?= $c('proof_title', 'Ein Modell, das auf Klarheit statt Komplexität setzt.') ?></h2>
        <p class="section-sub fade-up delay-2"><?= $c('proof_sub', 'Ein funktionierendes System, ein klarer Markt und direkte Unterstützung: Genau darauf ist diese Partnerschaft ausgelegt.') ?></p>
      </div>

      <div class="partner-benefits">
        <article class="benefit-card glass fade-up delay-1">
          <p class="partner-card-kicker"><?= $c('card_1_kicker', 'System') ?></p>
          <h3><?= $c('card_1_title', 'Funktionierendes System') ?></h3>
          <p><?= $c('card_1_text', 'Wir haben’s getestet. Verfeinert. Wiederholt. Was du bekommst, ist nicht neu – sondern durchdacht, schlank und wirksam.') ?></p>
        </article>
        <article class="benefit-card glass fade-up delay-2">
          <p class="partner-card-kicker"><?= $c('card_2_kicker', 'Start') ?></p>
          <h3><?= $c('card_2_title', 'Geringes Startkapital') ?></h3>
          <p><?= $c('card_2_text', 'Du brauchst kein Büro, kein Lager und keine teure Ausstattung. Starte smart – mit einem klaren Plan und wenig Risiko. Was zählt: dein Einsatz und dein Wille, wirklich loszulegen.') ?></p>
        </article>
        <article class="benefit-card glass fade-up delay-3">
          <p class="partner-card-kicker"><?= $c('card_3_kicker', 'Support') ?></p>
          <h3><?= $c('card_3_title', 'Nicht allein. Nie.') ?></h3>
          <p><?= $c('card_3_text', 'Bei Fragen rufst du nicht irgendwo an – sondern direkt bei uns. Du bekommst echten Support, kein Ticketsystem. Persönlich. Ehrlich. Schnell.') ?></p>
        </article>
      </div>

      <div class="partner-fit-panel glass fade-up delay-4">
        <div>
          <p class="section-eyebrow pw-left"><?= $c('fit_eyebrow', 'Passt das zu dir?') ?></p>
          <h2 class="section-title pw-left"><?= $c('fit_title', 'Dein nächster Schritt kann klar und unkompliziert starten.') ?></h2>
        </div>
        <div class="partner-fit-copy">
          <p><?= $c('fit_text', 'Du bist zuverlässig, kommunikativ und willst dir was Eigenes aufbauen? Dann bist du hier richtig. Fülle jetzt das kurze Formular aus – wir melden uns persönlich bei dir.') ?></p>
          <p class="partner-fit-note"><?= $c('fit_note', 'Unverbindlich. Ohne Risiko. Nur echtes Interesse zählt.') ?></p>
        </div>
      </div>
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
      <div class="pw-form-wrap">
        <p class="section-eyebrow text-center fade-up"><?= $c('form_eyebrow', 'Jetzt Partner werden') ?></p>
        <h2 class="section-title text-center fade-up delay-1" id="partner-form-heading"><?= $c('form_title', 'Lass uns sprechen.') ?></h2>
        <p class="section-sub text-center fade-up delay-2" style="margin:0 auto 3rem">
          <?= $c('form_sub', 'Fülle das kurze Formular aus – wir melden uns persönlich bei dir.') ?>
        </p>

        <div class="contact-form-box fade-up delay-3">
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
                <a href="<?= $h($root) ?>pages/datenschutz.php"><?= $c('form_privacy_link', 'Datenschutzerklärung') ?></a>
                <?= $c('form_privacy_suffix', 'gelesen und bin mit der Verarbeitung meiner Daten zur Bearbeitung meiner Anfrage einverstanden. *') ?>
              </label>
            </div>

            <?= visitfy_turnstile_widget() ?>
            <button type="submit" class="btn btn-primary js-btnfx-partner" style="width:100%"><?= $c('form_submit_text', 'Jetzt Partner werden') ?></button>
            <div class="form-status" role="alert"></div>
          </form>
        </div>

        <ul class="pw-trust fade-up">
          <li>Persönliche Antwort, kein Bot</li>
          <li>Unverbindliches Erstgespräch</li>
          <li><?= $partners ?> Partner sind schon dabei</li>
        </ul>

        <p class="pw-footnote" id="pw-footnote">
          * Durchschnittswerte aller <?= $partners ?> aktiven Visitfy-Partner, Stand <?= $h($net['stand']) ?>.
          Angegeben ist Umsatz, nicht Gewinn. Einzelne Partner liegen darüber oder darunter;
          das Ergebnis hängt von persönlichem Einsatz und Region ab. Keine Verdienstgarantie.
        </p>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
