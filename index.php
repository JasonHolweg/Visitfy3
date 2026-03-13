<?php
/**
 * Visitfy3 – index.php
 * Main router: ?page=... with strict whitelist (LFI protection).
 * Default: homepage.
 */

/* ── Security: whitelisted pages ────────────────────────── */
$allowed = [
  'about'       => 'pages/about.php',
    'partner'     => 'pages/partner.php',
    'faq'         => 'pages/faq.php',
    'kontakt'     => 'pages/kontakt.php',
    'impressum'   => 'pages/impressum.php',
    'datenschutz' => 'pages/datenschutz.php',
];

$page = isset($_GET['page']) ? (string)$_GET['page'] : '';

if ($page !== '' && isset($allowed[$page])) {
    require __DIR__ . '/' . $allowed[$page];
    exit;
}

/* ── Homepage ────────────────────────────────────────────── */
require __DIR__ . '/partials/cms.php';
$root      = visitfy_base_path();

$contentConfig = visitfy_load_json(__DIR__ . '/assets/data/content.json', []);

$pageTitle = (string)visitfy_get($contentConfig, 'seo.home_title', 'Visitfy | 360° Rundgänge für moderne Unternehmen');
$pageDesc  = (string)visitfy_get($contentConfig, 'seo.home_desc', 'Visitfy entwickelt professionelle 360° virtuelle Rundgänge für Unternehmen jeder Branche – realistisch, hochwertig und sofort einsatzbereit für Website und Google Business.');

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';

/* Load tours data */
$toursJson = file_get_contents(__DIR__ . '/assets/data/tours.json');
$tours     = $toursJson ? json_decode($toursJson, true) : [];
if (!is_array($tours)) $tours = [];

/* Load client logos for marquee */
$clientLogoFiles = glob(__DIR__ . '/assets/img/client-logos/*.{png,jpg,jpeg,webp,avif,svg}', GLOB_BRACE);
if (!is_array($clientLogoFiles)) {
  $clientLogoFiles = [];
}
natsort($clientLogoFiles);
$clientLogoFiles = array_values($clientLogoFiles);

$marqueeLogoFiles = $clientLogoFiles;
if (count($clientLogoFiles) > 0) {
  /* Ensure enough logos for seamless marquee: duplicate set 5x, then double for CSS loop */
  $repeatedLogos = $clientLogoFiles;
  while (count($repeatedLogos) < count($clientLogoFiles) * 5) {
    $repeatedLogos = array_merge($repeatedLogos, $clientLogoFiles);
  }
  /* Double the full set so CSS translateX(-50%) creates seamless loop */
  $marqueeLogoFiles = array_merge($repeatedLogos, $repeatedLogos);
}

$heroWords = visitfy_get($contentConfig, 'hero.rotating_words', ['SICHTBARKEIT.', 'VERTRAUEN.', 'ANFRAGEN.']);
if (!is_array($heroWords) || !$heroWords) {
  $heroWords = ['SICHTBARKEIT.', 'VERTRAUEN.', 'ANFRAGEN.'];
}
$heroWords = array_values(array_map(static fn($v) => trim((string)$v), $heroWords));
$heroWords = array_values(array_filter($heroWords, static fn($v) => $v !== ''));
if (!$heroWords) {
  $heroWords = ['SICHTBARKEIT.', 'VERTRAUEN.', 'ANFRAGEN.'];
}

$kpiItems = visitfy_get($contentConfig, 'kpi.items', []);
if (!is_array($kpiItems) || !$kpiItems) {
  $kpiItems = [
    ['target' => '40', 'suffix' => '%', 'label' => '+ längere Verweildauer auf Ihrer Website'],
    ['target' => '5', 'suffix' => ' Tage', 'label' => 'Ø bis Ihr Rundgang live geht'],
    ['target' => '420', 'suffix' => '+', 'label' => 'umgesetzte Rundgänge'],
    ['target' => '98', 'suffix' => '%', 'label' => 'zufriedene Kunden'],
  ];
}

?>

<!-- ══════════════════════════════════════════════════════════
     INTRO / PRELOADER OVERLAY
══════════════════════════════════════════════════════════ -->
<div id="intro" role="presentation" aria-hidden="true">
  <canvas id="intro-canvas"></canvas>
  <div id="intro-text">
    <img src="<?= htmlspecialchars(visitfy_url('assets/img/visitfy-logo.svg'), ENT_QUOTES, 'UTF-8') ?>" alt="Visitfy" class="intro-logo-mark">
    <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'intro.tagline', '360° Rundgänge die begeistern'), ENT_QUOTES, 'UTF-8') ?></p>
    <p class="scroll-hint"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'intro.hint', 'Klicken zum Fortfahren'), ENT_QUOTES, 'UTF-8') ?></p>
  </div>
  <button id="skip-btn" type="button"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'intro.skip_button', 'Überspringen ↓'), ENT_QUOTES, 'UTF-8') ?></button>
</div>

<!-- ══════════════════════════════════════════════════════════
     MAIN CONTENT (hidden until intro exits)
══════════════════════════════════════════════════════════ -->
<div id="main-content" style="opacity:0;">

  <!-- ══ 1) HERO ════════════════════════════════════════════ -->
  <section class="hero" aria-labelledby="hero-heading">
    <canvas id="hero-canvas" aria-hidden="true"></canvas>
    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="container hero-content">
      <div class="hero-panel fade-up">
        <p class="hero-eyebrow"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'hero.eyebrow', '360° Rundgänge für moderne Unternehmen'), ENT_QUOTES, 'UTF-8') ?></p>
        <h1 id="hero-heading" class="hero-rotating-text" aria-label="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'hero.prefix', 'MEHR') . ' ' . implode(' ', $heroWords), ENT_QUOTES, 'UTF-8') ?>">
          <span class="hero-rotating-prefix"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'hero.prefix', 'MEHR'), ENT_QUOTES, 'UTF-8') ?></span><br>
          <span class="hero-rotating-word" data-hero-rotate-word><?= htmlspecialchars((string)($heroWords[0] ?? 'SICHTBARKEIT.'), ENT_QUOTES, 'UTF-8') ?></span>
        </h1>
        <p class="hero-desc">
          <?= htmlspecialchars((string)visitfy_get($contentConfig, 'hero.desc', 'Visitfy entwickelt hochwertige 360° Erlebnisse für Unternehmen jeder Branche – realistisch, hochwertig und sofort einsatzbereit für Website und Google Business.'), ENT_QUOTES, 'UTF-8') ?>
        </p>
        <div class="hero-actions">
          <a href="<?= htmlspecialchars(visitfy_url((string)visitfy_get($contentConfig, 'hero.button_primary_link', 'pages/kontakt.php')), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary js-btnfx-hero-primary"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'hero.button_primary_text', 'Beratung anfragen'), ENT_QUOTES, 'UTF-8') ?></a>
          <a href="<?= htmlspecialchars(visitfy_url((string)visitfy_get($contentConfig, 'hero.button_secondary_link', '#tours')), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary js-btnfx-hero-secondary"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'hero.button_secondary_text', 'Unsere Ergebnisse'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </div>
    </div>

    <div class="hero-scroll" aria-hidden="true">Scroll</div>
  </section>


  <!-- ══ 2) GERÄTE MOCKUP ═════════════════════════════════ -->
<?php
  $mockupDesktop = trim((string)visitfy_get($contentConfig, 'mockup.desktop_img', ''));
  $mockupTablet  = trim((string)visitfy_get($contentConfig, 'mockup.tablet_img', ''));
  $mockupPhone   = trim((string)visitfy_get($contentConfig, 'mockup.phone_img', ''));
?>
  <section class="section mockup-section" id="mockup" aria-labelledby="mockup-heading">
    <div class="container">
      <p class="section-eyebrow fade-up text-center"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'mockup_text.eyebrow', 'So sieht es aus'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1 text-center" id="mockup-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'mockup_text.title', 'Ihr Rundgang – auf jedem Gerät'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'mockup_text.sub', 'Ob Desktop, Tablet oder Smartphone – Ihr 360° Rundgang sieht überall perfekt aus.'), ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="mockup-devices fade-up delay-3">
        <!-- Laptop Mockup -->
        <div class="mockup-laptop">
          <div class="mockup-laptop-screen">
<?php if ($mockupDesktop !== '' && is_file(__DIR__ . '/' . $mockupDesktop)): ?>
            <img src="<?= htmlspecialchars(visitfy_url($mockupDesktop), ENT_QUOTES, 'UTF-8') ?>" alt="Rundgang auf Laptop" loading="lazy" decoding="async">
<?php else: ?>
            <div class="mockup-placeholder" aria-label="Laptop-Vorschau">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M2 17h20"/><path d="M6 21h12"/></svg>
              <span>Laptop-Bild einfügen</span>
            </div>
<?php endif; ?>
          </div>
        </div>
        <!-- Tablet Mockup -->
        <div class="mockup-tablet">
          <div class="mockup-tablet-screen">
<?php if ($mockupTablet !== '' && is_file(__DIR__ . '/' . $mockupTablet)): ?>
            <img src="<?= htmlspecialchars(visitfy_url($mockupTablet), ENT_QUOTES, 'UTF-8') ?>" alt="Rundgang auf Tablet" loading="lazy" decoding="async">
<?php else: ?>
            <div class="mockup-placeholder" aria-label="Tablet-Vorschau">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="19" r="1"/></svg>
              <span>Tablet</span>
            </div>
<?php endif; ?>
          </div>
        </div>
        <!-- Phone Mockup -->
        <div class="mockup-phone">
          <div class="mockup-phone-screen">
<?php if ($mockupPhone !== '' && is_file(__DIR__ . '/' . $mockupPhone)): ?>
            <img src="<?= htmlspecialchars(visitfy_url($mockupPhone), ENT_QUOTES, 'UTF-8') ?>" alt="Rundgang auf Smartphone" loading="lazy" decoding="async">
<?php else: ?>
            <div class="mockup-placeholder" aria-label="Smartphone-Vorschau">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="3"/><path d="M12 18h.01"/></svg>
              <span>Phone</span>
            </div>
<?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 3) VORTEILE VERGLEICH ══════════════════════════════ -->
<?php
  $vergleichNegItems = visitfy_get($contentConfig, 'vergleich.negative_items', ['Nur begrenzte Perspektive', 'Kein Erkundungserlebnis', 'Weniger vertrauenswürdig', 'Nicht interaktiv']);
  if (!is_array($vergleichNegItems) || !$vergleichNegItems) $vergleichNegItems = ['Nur begrenzte Perspektive', 'Kein Erkundungserlebnis', 'Weniger vertrauenswürdig', 'Nicht interaktiv'];
  $vergleichPosItems = visitfy_get($contentConfig, 'vergleich.positive_items', ['Freie Rundumsicht', 'Interaktives Erleben', 'Stärkt Vertrauen', 'Mehr Anfragen']);
  if (!is_array($vergleichPosItems) || !$vergleichPosItems) $vergleichPosItems = ['Freie Rundumsicht', 'Interaktives Erleben', 'Stärkt Vertrauen', 'Mehr Anfragen'];
?>
  <section class="section compare-section" id="vergleich" aria-labelledby="compare-heading">
    <div class="container">
      <p class="section-eyebrow fade-up text-center"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'vergleich.eyebrow', 'Der Unterschied'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1 text-center" id="compare-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'vergleich.title', 'Nur Fotos vs. 360° Rundgang'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'vergleich.sub', 'Sehen Sie selbst, wie ein 360° Rundgang Ihre Präsenz verändert.'), ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="compare-grid fade-up delay-3">
        <!-- Nur Fotos -->
        <div class="compare-card compare-card--without">
          <div class="compare-card-header">
            <span class="compare-badge compare-badge--negative" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
              <?= htmlspecialchars((string)visitfy_get($contentConfig, 'vergleich.badge_negative', 'Nur Fotos'), ENT_QUOTES, 'UTF-8') ?>
            </span>
          </div>
          <ul class="compare-list compare-list--negative">
<?php foreach ($vergleichNegItems as $negItem): ?>
            <li>
              <span class="compare-check compare-check--muted" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              <?= htmlspecialchars(trim((string)$negItem), ENT_QUOTES, 'UTF-8') ?>
            </li>
<?php endforeach; ?>
          </ul>
        </div>
        <!-- 360° Rundgang -->
        <div class="compare-card compare-card--with">
          <div class="compare-card-header">
            <span class="compare-badge compare-badge--positive" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              <?= htmlspecialchars((string)visitfy_get($contentConfig, 'vergleich.badge_positive', '360° Rundgang'), ENT_QUOTES, 'UTF-8') ?>
            </span>
          </div>
          <ul class="compare-list compare-list--positive">
<?php foreach ($vergleichPosItems as $posItem): ?>
            <li>
              <span class="compare-check compare-check--green" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              <?= htmlspecialchars(trim((string)$posItem), ENT_QUOTES, 'UTF-8') ?>
            </li>
<?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 4) WARUM 360° ══════════════════════════════════════ -->
<?php
  $w360Cards = visitfy_get($contentConfig, 'warum360.cards', []);
  if (!is_array($w360Cards) || !$w360Cards) {
    $w360Cards = [
      ['emoji' => '🏛️', 'title' => 'Räume verkaufen', 'text' => 'Virtuelle Rundgänge lassen Kunden Ihre Räume erleben, bevor sie die Tür öffnen. Das senkt die Hemmschwelle und steigert die Konversionsrate von Interessenten zu echten Besuchern.'],
      ['emoji' => '🤝', 'title' => 'Vertrauen aufbauen', 'text' => 'Transparenz erzeugt Vertrauen. Wer Ihre Location bereits virtuell besucht hat, kommt mit einem positiven Vorurteil und deutlich höherer Kaufbereitschaft.'],
      ['emoji' => '✨', 'title' => 'Atmosphäre erlebbar machen', 'text' => 'Ein Foto zeigt einen Raum. Ein 360° Rundgang lässt Ihre Gäste Ihre Atmosphäre, Ihr Licht, Ihr Raumgefühl wirklich spüren – rund um die Uhr, von überall.'],
      ['emoji' => '🔍', 'title' => 'Transparenz schaffen', 'text' => 'Hochwertige 360° Präsenz auf Google Business stärkt Ihre Auffindbarkeit und zeigt potenziellen Kunden genau, was sie erwartet – ehrlich, realistisch, professionell.'],
    ];
  }
  $bentoClasses = ['bento-wide slide-left', 'bento-square slide-right', 'bento-square slide-left', 'bento-wide slide-right'];
  $bentoDelays  = ['0s', '0.15s', '0.3s', '0.45s'];
?>
  <section class="section" aria-labelledby="value-heading">
    <div class="container">
      <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'warum360.eyebrow', 'Warum 360°?'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1" id="value-heading">
        <?= nl2br(htmlspecialchars((string)visitfy_get($contentConfig, 'warum360.title', "360° Rundgänge für jede Branche –\nund jede Location"), ENT_QUOTES, 'UTF-8')) ?>
      </h2>
      <p class="section-sub fade-up delay-2">
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'warum360.sub', 'Ein virtueller Rundgang schafft unmittelbar Nähe – noch bevor der erste echte Kontakt stattfindet.'), ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="bento-grid">
<?php foreach ($w360Cards as $wi => $wCard):
  $bentoClass = $bentoClasses[$wi % 4] ?? 'bento-wide slide-left';
  $bentoDelay = $bentoDelays[$wi % 4] ?? '0s';
?>
        <article class="feature-card glass <?= $bentoClass ?>" style="--slide-delay: <?= $bentoDelay ?>">
          <div class="feature-icon" aria-hidden="true"><?= htmlspecialchars((string)($wCard['emoji'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
          <h3><?= htmlspecialchars((string)($wCard['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)($wCard['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </article>
<?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ══ 5) LIVE DEMOS ══════════════════════════════════════ -->
  <section class="section scroll-stack-section" id="tours" aria-labelledby="tours-heading">
    <div class="container">
      <div class="scroll-stack-intro">
        <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'tours_text.eyebrow', 'Live-Demos'), ENT_QUOTES, 'UTF-8') ?></p>
        <h2 class="section-title fade-up delay-1" id="tours-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'tours_text.title', 'Beispiel-Rundgänge'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="section-sub fade-up delay-2" style="margin:0 auto">
          <?= htmlspecialchars((string)visitfy_get($contentConfig, 'tours_text.sub', 'Erlebe unsere Arbeit direkt – immersiv, interaktiv und hochauflösend. Scrolle durch die Rundgänge.'), ENT_QUOTES, 'UTF-8') ?>
        </p>
      </div>
    </div>

    <!-- Spacer: JS sets height so there's enough scroll room -->
    <div class="stack-spacer">
      <div class="stack-viewport">
        <div class="stack-container" aria-label="Rundgang-Beispiele">
<?php foreach ($tours as $i => $tour):
  $title  = htmlspecialchars($tour['title']       ?? 'Rundgang', ENT_QUOTES, 'UTF-8');
  $tag    = htmlspecialchars($tour['tag']         ?? '',         ENT_QUOTES, 'UTF-8');
  $desc   = htmlspecialchars($tour['description'] ?? '',         ENT_QUOTES, 'UTF-8');
  $url    = $tour['matterportUrl'] ?? '';
  $safeUrl = '';
  if (preg_match('#^https://my\.matterport\.com/show/\?m=[a-zA-Z0-9]+$#', $url)) {
      $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
  }
  $num = $i + 1;
?>
        <article class="stack-item" aria-label="<?= $title ?>">
          <div class="stack-card">
            <div class="stack-card-header">
              <div>
                <p style="font-size:0.72rem;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.3rem">Rundgang <?= sprintf('%02d', $num) ?></p>
                <h3 class="stack-card-title"><?= $title ?></h3>
              </div>
<?php if ($tag): ?>
              <span class="stack-card-tag"><?= $tag ?></span>
<?php endif; ?>
            </div>
<?php if ($desc): ?>
            <p class="stack-card-desc"><?= $desc ?></p>
<?php endif; ?>
            <div class="iframe-wrap">
<?php if ($safeUrl): ?>
              <iframe
                src="<?= $safeUrl ?>"
                title="360° Rundgang – <?= $title ?>"
                allow="fullscreen; xr-spatial-tracking;"
                allowfullscreen
                referrerpolicy="strict-origin-when-cross-origin"
                sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox allow-presentation"
                loading="eager"
              ></iframe>
<?php else: ?>
              <div class="iframe-placeholder" aria-label="Kein Rundgang verfügbar"></div>
<?php endif; ?>
            </div>
          </div>
        </article>
<?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 6) MESSBARE ERGEBNISSE (KPI) ══════════════════════ -->
  <section class="section kpi-section" id="kpi" aria-labelledby="kpi-heading">
    <div class="container">
      <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kpi.eyebrow', 'Messbare Ergebnisse'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1" id="kpi-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kpi.title', 'Zahlen, die überzeugen'), ENT_QUOTES, 'UTF-8') ?></h2>

      <div class="kpi-grid">
<?php foreach ($kpiItems as $i => $kpi):
  $delayClass = 'delay-' . (($i % 4) + 1);
  $target = (string)($kpi['target'] ?? '0');
  $suffix = (string)($kpi['suffix'] ?? '');
  $label = (string)($kpi['label'] ?? '');
?>
        <div class="kpi-card glass fade-up <?= htmlspecialchars($delayClass, ENT_QUOTES, 'UTF-8') ?>">
          <div class="kpi-number" data-countup data-target="<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>" data-suffix="<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>">0<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="kpi-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
<?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ══ 7) MINI CASE STUDIES ═══════════════════════════════ -->
<?php
  $caseStudyItems = visitfy_get($contentConfig, 'cases.items', []);
  if (!is_array($caseStudyItems) || !$caseStudyItems) {
    $caseStudyItems = [
      ['tag' => 'Gastronomie', 'title' => 'Flora Kaffee &amp; Eisbar', 'desc' => 'Zwei Standorte, zwei Rundgänge – und eine messbar gesunkene Hemmschwelle für neue Gäste.', 'stat1_value' => '+35%', 'stat1_label' => 'Neue Google-Aufrufe', 'stat2_value' => '2×', 'stat2_label' => 'Standorte digitalisiert'],
      ['tag' => 'Möbelhaus', 'title' => 'Danbo Flensburg', 'desc' => 'Kunden erleben das gesamte Sortiment virtuell – und kommen gezielter in den Showroom.', 'stat1_value' => '+50%', 'stat1_label' => 'Längere Verweildauer', 'stat2_value' => '↑', 'stat2_label' => 'Qualifizierte Anfragen'],
      ['tag' => 'Hotel &amp; Lounge', 'title' => 'Buddha Lounge', 'desc' => 'Ein 360° Rundgang im Sterne-Hotel: Gäste reservieren gezielter, weil sie die Atmosphäre bereits kennen.', 'stat1_value' => '+28%', 'stat1_label' => 'Mehr Online-Reservierungen', 'stat2_value' => '24/7', 'stat2_label' => 'Virtuell erlebbar'],
    ];
  }
?>
  <section class="section" id="case-studies" aria-labelledby="cases-heading">
    <div class="container">
      <p class="section-eyebrow fade-up text-center"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'cases.eyebrow', 'Erfolgsgeschichten'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1 text-center" id="cases-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'cases.title', 'Mini Case Studies'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'cases.sub', 'Echte Ergebnisse unserer Kunden – in Zahlen und Fakten.'), ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="cases-grid">
<?php foreach ($caseStudyItems as $ci => $cItem):
  $delayC = 'delay-' . (($ci % 3) + 1);
?>
        <article class="case-card glass fade-up <?= $delayC ?>">
          <div class="case-card-tag"><?= htmlspecialchars((string)($cItem['tag'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
          <h3 class="case-card-title"><?= htmlspecialchars((string)($cItem['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
          <p class="case-card-desc"><?= htmlspecialchars((string)($cItem['desc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          <div class="case-card-results">
            <div class="case-stat">
              <span class="case-stat-value"><?= htmlspecialchars((string)($cItem['stat1_value'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
              <span class="case-stat-label"><?= htmlspecialchars((string)($cItem['stat1_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="case-stat">
              <span class="case-stat-value"><?= htmlspecialchars((string)($cItem['stat2_value'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
              <span class="case-stat-label"><?= htmlspecialchars((string)($cItem['stat2_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
          </div>
        </article>
<?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ══ 8) KUNDENSTIMMEN ═══════════════════════════════════ -->
<?php
  $testimonialItems = visitfy_get($contentConfig, 'testimonials.items', []);
  if (!is_array($testimonialItems) || !$testimonialItems) {
    $testimonialItems = [
      ['text' => 'Die Rundgänge haben unsere Hemmschwelle komplett gesenkt. Viele Gäste trauen sich jetzt erst rein, weil sie den Ort vorher online schon besucht haben.', 'author' => 'Flora Kaffee & Eisbar', 'company' => '2 Rundgänge – 2 Standorte'],
      ['text' => 'Der Rundgang überzeugt neue Kunden schnell von unserem Inventar. Das hat unseren Online-Vertrieb spürbar unterstützt.', 'author' => 'Danbo Flensburg', 'company' => 'Digitale Möbelpräsentation'],
      ['text' => 'Der perfekte erste Eindruck für unseren Gastbereich! Gäste reservieren jetzt gezielter, weil sie unsere Atmosphäre bereits kennen.', 'author' => 'Buddha Lounge', 'company' => '360° Präsentation im Sterne-Hotel'],
    ];
  }
?>
  <section class="section" aria-labelledby="testimonials-heading" style="background:var(--surface)">
    <div class="container">
      <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'testimonials.eyebrow', 'Kundenstimmen'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1" id="testimonials-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'testimonials.title', 'Das sagen unsere Kunden'), ENT_QUOTES, 'UTF-8') ?></h2>

      <div class="testimonials-grid">
<?php foreach ($testimonialItems as $ti => $tItem):
  $delayT = 'delay-' . (($ti % 3) + 1);
?>
        <article class="testimonial-card glass fade-up <?= $delayT ?>">
          <div class="stars" aria-label="5 von 5 Sternen">
            <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
            <?php endfor; ?>
          </div>
          <p class="testimonial-text"><?= htmlspecialchars((string)($tItem['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          <p class="testimonial-author"><?= htmlspecialchars((string)($tItem['author'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          <p class="testimonial-company"><?= htmlspecialchars((string)($tItem['company'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </article>
<?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ══ LOGO MARQUEE ═══════════════════════════════════════ -->
  <section class="marquee-section" aria-label="Unsere Kunden">
    <p class="marquee-label"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'marquee.label', 'Vertrauen von führenden Unternehmen'), ENT_QUOTES, 'UTF-8') ?></p>
    <div class="marquee-track-wrap">
      <div class="marquee-track" aria-hidden="true">
<?php if ($marqueeLogoFiles): ?>
<?php foreach ($marqueeLogoFiles as $logoPath):
  $fileName = basename($logoPath);
  $baseName = pathinfo($fileName, PATHINFO_FILENAME);
  $prettyName = preg_replace('/[-_]+/', ' ', $baseName);
  $prettyName = preg_replace('/\s+/', ' ', (string)$prettyName);
  $prettyName = trim((string)$prettyName);
  $altText = $prettyName !== '' ? $prettyName : 'Kundenlogo';
  $logoSrc = visitfy_url('assets/img/client-logos/' . rawurlencode($fileName));
?>
        <span class="marquee-logo" title="<?= htmlspecialchars($altText, ENT_QUOTES, 'UTF-8') ?>">
          <img src="<?= htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8') ?>"
               alt="<?= htmlspecialchars($altText, ENT_QUOTES, 'UTF-8') ?>"
               loading="lazy"
               decoding="async">
        </span>
<?php endforeach; ?>
<?php else: ?>
        <span class="marquee-logo marquee-logo--empty">Keine Kundenlogos im Ordner assets/img/client-logos</span>
<?php endif; ?>
      </div>
    </div>
  </section>


  <!-- ══ 9) ABLAUF ══════════════════════════════════════════ -->
<?php
  $ablaufSteps = visitfy_get($contentConfig, 'ablauf.items', []);
  if (!is_array($ablaufSteps) || !$ablaufSteps) {
    $ablaufSteps = [
      ['emoji' => '🎯', 'title' => 'Strategisches Briefing & Planung', 'text' => 'In einem persönlichen Gespräch definieren wir gemeinsam Ihre Ziele, den gewünschten Stil und den optimalen Aufnahmetag. Wir beraten Sie, welche Bereiche Ihrer Location besonders wirkungsvoll in Szene gesetzt werden. Kostenlos und unverbindlich.'],
      ['emoji' => '📸', 'title' => 'Professionelle 360° Aufnahme vor Ort', 'text' => 'Unser Team kommt zu Ihnen und digitalisiert Ihre Location mit modernster Scan-Technologie. Sorgfältige Nachbearbeitung garantiert gestochen scharfe, stimmungsvolle Bilder, die Ihre Räume in bestem Licht zeigen.'],
      ['emoji' => '🚀', 'title' => 'Integration & Live-Schaltung', 'text' => 'Sie erhalten Ihren fertigen iFrame-Code zur Einbindung auf Ihrer Website und in Google Business. Wir begleiten Sie beim Launch und stehen für Fragen bereit – bis Ihr Rundgang reibungslos live ist.'],
    ];
  }
?>
  <section class="section" id="prozess" aria-labelledby="process-heading">
    <div class="container">
      <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'ablauf.eyebrow', 'Ablauf'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1" id="process-heading">
        <?= nl2br(htmlspecialchars((string)visitfy_get($contentConfig, 'ablauf.title', "So entsteht Ihr professioneller\n360° Rundgang"), ENT_QUOTES, 'UTF-8')) ?>
      </h2>

      <div class="process-steps">
<?php foreach ($ablaufSteps as $ai => $aStep):
  $delayA = 'delay-' . (($ai % 3) + 1);
  $stepNum = sprintf('%02d', $ai + 1);
?>
        <div class="process-card glass fade-up <?= $delayA ?>">
          <p class="process-num">SCHRITT <?= $stepNum ?></p>
          <div class="process-icon" aria-hidden="true"><?= htmlspecialchars((string)($aStep['emoji'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
          <h3><?= htmlspecialchars((string)($aStep['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)($aStep['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
<?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ══ 10) KONTAKTFORMULAR ════════════════════════════════ -->
  <section class="section" id="kontakt" aria-labelledby="kontakt-heading" style="background:var(--surface)">
    <div class="container">
      <p class="section-eyebrow fade-up text-center"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.eyebrow', 'Kontakt'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1 text-center" id="kontakt-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.title', 'Jetzt unverbindlich anfragen'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.sub', 'Wir erstellen Ihnen ein klares Angebot mit Zeitplan und transparenten Kosten – kostenlos und persönlich.'), ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="contact-grid fade-up delay-3">
        <!-- Contact info -->
        <div class="contact-info">
          <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.sidebar_heading', 'So erreichen Sie uns'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.sidebar_text', 'Nutzen Sie das Formular für eine schnelle Anfrage – oder schreiben Sie uns direkt per E-Mail.'), ENT_QUOTES, 'UTF-8') ?></p>
          <p style="margin-top:1.5rem">
            <a href="mailto:<?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.email', 'info@visitfy.de'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.email', 'info@visitfy.de'), ENT_QUOTES, 'UTF-8') ?></a>
          </p>
          <div style="margin-top:2.5rem">
            <p style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.response_label', 'Antwortzeit'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.response_text', 'In der Regel innerhalb von 24 Stunden an Werktagen.'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div style="margin-top:2rem">
            <p style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.location_label', 'Standort'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.location_text', 'Flensburg, Deutschland'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>

        <!-- Form -->
        <div class="contact-form-box">
          <form method="post" action="<?= htmlspecialchars(visitfy_url('pages/kontakt.php'), ENT_QUOTES, 'UTF-8') ?>" novalidate>
            <!-- Honeypot -->
            <div class="form-honeypot" aria-hidden="true">
              <label for="hp_website_home">Website</label>
              <input type="text" id="hp_website_home" name="hp_website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="h_name">Name *</label>
                <input type="text" id="h_name" name="name" required autocomplete="name" placeholder="Max Mustermann">
              </div>
              <div class="form-group">
                <label for="h_firma">Firma</label>
                <input type="text" id="h_firma" name="firma" autocomplete="organization" placeholder="Muster GmbH">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="h_email">E-Mail *</label>
                <input type="email" id="h_email" name="email" required autocomplete="email" placeholder="name@firma.de">
              </div>
              <div class="form-group">
                <label for="h_telefon">Telefon (optional)</label>
                <input type="tel" id="h_telefon" name="telefon" autocomplete="tel" placeholder="+49 …">
              </div>
            </div>

            <div class="form-group">
              <label for="h_branche">Branche</label>
              <select id="h_branche" name="branche">
                <option value="">Bitte wählen…</option>
                <option value="gastronomie">Gastronomie (Restaurant, Café, Bar)</option>
                <option value="hotel">Hotel &amp; Wellness</option>
                <option value="immobilien">Immobilien</option>
                <option value="einzelhandel">Einzelhandel &amp; Showroom</option>
                <option value="praxis">Praxis &amp; Medizin</option>
                <option value="fitness">Fitness &amp; Sport</option>
                <option value="sonstiges">Sonstiges</option>
              </select>
            </div>

            <div class="form-group">
              <label for="h_nachricht">Nachricht *</label>
              <textarea id="h_nachricht" name="nachricht" required rows="5"
                        placeholder="Beschreiben Sie kurz Ihre Location und was Sie sich vorstellen…"></textarea>
            </div>

            <div class="form-check">
              <input type="checkbox" id="h_dsgvo" name="dsgvo" required>
              <label for="h_dsgvo">
                Ich habe die <a href="<?= htmlspecialchars(visitfy_url('pages/datenschutz.php'), ENT_QUOTES, 'UTF-8') ?>">Datenschutzerklärung</a> gelesen und bin mit der
                Verarbeitung meiner Daten zur Bearbeitung meiner Anfrage einverstanden. *
              </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%">Anfrage absenden</button>
          </form>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 11) FAQ ════════════════════════════════════════════ -->
<?php
  $faqItems = visitfy_get($contentConfig, 'faq.items', []);
  if (!is_array($faqItems) || !$faqItems) {
    $faqItems = [
      ['question' => 'Was ist ein 360° Rundgang?', 'answer' => '<p>Ein 360° Rundgang ist eine interaktive, virtuelle Begehung Ihrer Räume. Mit modernster Kameratechnik werden alle Bereiche Ihrer Location fotorealistisch erfasst und zu einem nahtlosen, immersiven Erlebnis zusammengesetzt.</p>'],
      ['question' => 'Welche Vorteile bietet ein 360° Rundgang?', 'answer' => '<p>Ein 360° Rundgang bietet Ihrem Unternehmen messbare Mehrwerte:</p><ul><li>Stärkeres Vertrauen bei potenziellen Kunden noch vor dem ersten Besuch</li><li>Höhere Sichtbarkeit auf Google Maps und in der organischen Suche</li><li>Mehr qualifizierte Anfragen durch Interessenten, die bereits überzeugt sind</li><li>24/7 verfügbares, interaktives Schaufenster für Ihre Location</li></ul>'],
      ['question' => 'Für welche Branchen eignet sich ein 360° Rundgang?', 'answer' => '<p>Grundsätzlich für jede Location, die Kunden physisch besuchen oder vorab erleben sollen: Gastronomie, Hotels, Immobilien, Einzelhandel, Praxen, Fitnessstudios und mehr.</p>'],
      ['question' => 'Was kostet ein 360° Rundgang?', 'answer' => '<p>Die Kosten richten sich nach Größe und Raumanzahl Ihrer Location. Für ein transparentes, individuelles Angebot beraten wir Sie gerne persönlich und unverbindlich.</p>'],
      ['question' => 'Wie lange dauert die Erstellung?', 'answer' => '<p>Nach Briefing und Aufnahme vor Ort ist Ihr Rundgang im Durchschnitt innerhalb von 5 Werktagen live. Für größere Locations kalkulieren wir gemeinsam den Zeitplan.</p>'],
      ['question' => 'Wo kann ich den Rundgang einbinden?', 'answer' => '<p>Überall dort, wo Ihre Kunden sind:</p><ul><li>Auf Ihrer eigenen Website (per iFrame, ein Klick)</li><li>Direkt in Ihrem Google Business Profil</li><li>In Buchungsportalen und auf Social Media</li></ul>'],
    ];
  }
?>
  <section class="section" id="faq" aria-labelledby="faq-heading">
    <div class="container">
      <p class="section-eyebrow fade-up text-center"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'faq.eyebrow', 'Antworten'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1 text-center" id="faq-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'faq.title', 'Häufige Fragen'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'faq.sub', 'Alles Wichtige zu 360° Rundgängen, Ablauf, Kosten und Branchen auf einen Blick.'), ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="faq-list fade-up delay-3" style="margin-inline:auto">
<?php foreach ($faqItems as $fItem): ?>
        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            <?= htmlspecialchars((string)($fItem['question'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region">
            <div class="faq-answer-inner">
              <?= (string)($fItem['answer'] ?? '') ?>
            </div>
          </div>
        </div>
<?php endforeach; ?>
      </div>

      <div class="text-center" style="margin-top:3rem">
        <a href="<?= htmlspecialchars(visitfy_url('pages/faq.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'faq.button_text', 'Alle Fragen ansehen'), ENT_QUOTES, 'UTF-8') ?></a>
      </div>
    </div>
  </section>

</div><!-- /#main-content -->

<?php require __DIR__ . '/partials/footer.php'; ?>
