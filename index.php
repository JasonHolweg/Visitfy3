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
if (count($clientLogoFiles) > 0 && count($clientLogoFiles) < 10) {
  $marqueeLogoFiles = array_merge($clientLogoFiles, $clientLogoFiles);
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
  <section class="section mockup-section" id="mockup" aria-labelledby="mockup-heading">
    <div class="container">
      <p class="section-eyebrow fade-up text-center">So sieht es aus</p>
      <h2 class="section-title fade-up delay-1 text-center" id="mockup-heading">Ihr Rundgang – auf jedem Gerät</h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        Ob Desktop, Tablet oder Smartphone – Ihr 360° Rundgang sieht überall perfekt aus.
      </p>

      <div class="mockup-devices fade-up delay-3">
        <!-- Laptop Mockup -->
        <div class="mockup-laptop">
          <div class="mockup-laptop-screen">
            <!-- Bild hier einfügen: z.B. <img src="assets/img/mockup-laptop.png" alt="Rundgang auf Laptop"> -->
            <div class="mockup-placeholder" aria-label="Laptop-Vorschau">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M2 17h20"/><path d="M6 21h12"/></svg>
              <span>Laptop-Bild einfügen</span>
            </div>
          </div>
        </div>
        <!-- Tablet Mockup -->
        <div class="mockup-tablet">
          <div class="mockup-tablet-screen">
            <!-- Bild hier einfügen: z.B. <img src="assets/img/mockup-tablet.png" alt="Rundgang auf Tablet"> -->
            <div class="mockup-placeholder" aria-label="Tablet-Vorschau">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="19" r="1"/></svg>
              <span>Tablet</span>
            </div>
          </div>
        </div>
        <!-- Phone Mockup -->
        <div class="mockup-phone">
          <div class="mockup-phone-screen">
            <!-- Bild hier einfügen: z.B. <img src="assets/img/mockup-phone.png" alt="Rundgang auf Smartphone"> -->
            <div class="mockup-placeholder" aria-label="Smartphone-Vorschau">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="3"/><path d="M12 18h.01"/></svg>
              <span>Phone</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 3) VORTEILE VERGLEICH ══════════════════════════════ -->
  <section class="section compare-section" id="vergleich" aria-labelledby="compare-heading">
    <div class="container">
      <p class="section-eyebrow fade-up text-center">Der Unterschied</p>
      <h2 class="section-title fade-up delay-1 text-center" id="compare-heading">Nur Fotos vs. 360° Rundgang</h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        Sehen Sie selbst, wie ein 360° Rundgang Ihre Präsenz verändert.
      </p>

      <div class="compare-grid fade-up delay-3">
        <!-- Nur Fotos -->
        <div class="compare-card compare-card--without">
          <div class="compare-card-header">
            <span class="compare-badge compare-badge--negative" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
              Nur Fotos
            </span>
          </div>
          <ul class="compare-list compare-list--negative">
            <li>
              <span class="compare-check compare-check--muted" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              Nur begrenzte Perspektive
            </li>
            <li>
              <span class="compare-check compare-check--muted" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              Kein Erkundungserlebnis
            </li>
            <li>
              <span class="compare-check compare-check--muted" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              Weniger vertrauenswürdig
            </li>
            <li>
              <span class="compare-check compare-check--muted" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              Nicht interaktiv
            </li>
          </ul>
        </div>
        <!-- 360° Rundgang -->
        <div class="compare-card compare-card--with">
          <div class="compare-card-header">
            <span class="compare-badge compare-badge--positive" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              360° Rundgang
            </span>
          </div>
          <ul class="compare-list compare-list--positive">
            <li>
              <span class="compare-check compare-check--green" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              Freie Rundumsicht
            </li>
            <li>
              <span class="compare-check compare-check--green" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              Interaktives Erleben
            </li>
            <li>
              <span class="compare-check compare-check--green" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              Stärkt Vertrauen
            </li>
            <li>
              <span class="compare-check compare-check--green" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              Mehr Anfragen
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 4) WARUM 360° ══════════════════════════════════════ -->
  <section class="section" aria-labelledby="value-heading">
    <div class="container">
      <p class="section-eyebrow fade-up">Warum 360°?</p>
      <h2 class="section-title fade-up delay-1" id="value-heading">
        360° Rundgänge für jede Branche –<br>und jede Location
      </h2>
      <p class="section-sub fade-up delay-2">
        Ein virtueller Rundgang schafft unmittelbar Nähe – noch bevor der erste echte Kontakt stattfindet.
      </p>

      <div class="bento-grid">
        <article class="feature-card glass bento-wide slide-left" style="--slide-delay: 0s">
          <div class="feature-icon" aria-hidden="true">🏛️</div>
          <h3>Räume verkaufen</h3>
          <p>
            Virtuelle Rundgänge lassen Kunden Ihre Räume erleben, bevor sie die Tür öffnen.
            Das senkt die Hemmschwelle und steigert die Konversionsrate von Interessenten zu echten Besuchern.
          </p>
        </article>
        <article class="feature-card glass bento-square slide-right" style="--slide-delay: 0.15s">
          <div class="feature-icon" aria-hidden="true">🤝</div>
          <h3>Vertrauen aufbauen</h3>
          <p>
            Transparenz erzeugt Vertrauen. Wer Ihre Location bereits virtuell besucht hat, kommt mit einem
            positiven Vorurteil und deutlich höherer Kaufbereitschaft.
          </p>
        </article>
        <article class="feature-card glass bento-square slide-left" style="--slide-delay: 0.3s">
          <div class="feature-icon" aria-hidden="true">✨</div>
          <h3>Atmosphäre erlebbar machen</h3>
          <p>
            Ein Foto zeigt einen Raum. Ein 360° Rundgang lässt Ihre Gäste Ihre Atmosphäre, Ihr Licht,
            Ihr Raumgefühl wirklich spüren – rund um die Uhr, von überall.
          </p>
        </article>
        <article class="feature-card glass bento-wide slide-right" style="--slide-delay: 0.45s">
          <div class="feature-icon" aria-hidden="true">🔍</div>
          <h3>Transparenz schaffen</h3>
          <p>
            Hochwertige 360° Präsenz auf Google Business stärkt Ihre Auffindbarkeit und zeigt potenziellen
            Kunden genau, was sie erwartet – ehrlich, realistisch, professionell.
          </p>
        </article>
      </div>
    </div>
  </section>


  <!-- ══ 5) LIVE DEMOS ══════════════════════════════════════ -->
  <section class="section scroll-stack-section" id="tours" aria-labelledby="tours-heading">
    <div class="container">
      <div class="scroll-stack-intro">
        <p class="section-eyebrow fade-up">Live-Demos</p>
        <h2 class="section-title fade-up delay-1" id="tours-heading">Beispiel-Rundgänge</h2>
        <p class="section-sub fade-up delay-2" style="margin:0 auto">
          Erlebe unsere Arbeit direkt – immersiv, interaktiv und hochauflösend.
          Scrolle durch die Rundgänge.
        </p>
      </div>

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
  <section class="section" id="case-studies" aria-labelledby="cases-heading">
    <div class="container">
      <p class="section-eyebrow fade-up text-center">Erfolgsgeschichten</p>
      <h2 class="section-title fade-up delay-1 text-center" id="cases-heading">Mini Case Studies</h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        Echte Ergebnisse unserer Kunden – in Zahlen und Fakten.
      </p>

      <div class="cases-grid">
        <article class="case-card glass fade-up delay-1">
          <div class="case-card-tag">Gastronomie</div>
          <h3 class="case-card-title">Flora Kaffee &amp; Eisbar</h3>
          <p class="case-card-desc">
            Zwei Standorte, zwei Rundgänge – und eine messbar gesunkene Hemmschwelle für neue Gäste.
          </p>
          <div class="case-card-results">
            <div class="case-stat">
              <span class="case-stat-value">+35%</span>
              <span class="case-stat-label">Neue Google-Aufrufe</span>
            </div>
            <div class="case-stat">
              <span class="case-stat-value">2×</span>
              <span class="case-stat-label">Standorte digitalisiert</span>
            </div>
          </div>
        </article>

        <article class="case-card glass fade-up delay-2">
          <div class="case-card-tag">Möbelhaus</div>
          <h3 class="case-card-title">Danbo Flensburg</h3>
          <p class="case-card-desc">
            Kunden erleben das gesamte Sortiment virtuell – und kommen gezielter in den Showroom.
          </p>
          <div class="case-card-results">
            <div class="case-stat">
              <span class="case-stat-value">+50%</span>
              <span class="case-stat-label">Längere Verweildauer</span>
            </div>
            <div class="case-stat">
              <span class="case-stat-value">↑</span>
              <span class="case-stat-label">Qualifizierte Anfragen</span>
            </div>
          </div>
        </article>

        <article class="case-card glass fade-up delay-3">
          <div class="case-card-tag">Hotel &amp; Lounge</div>
          <h3 class="case-card-title">Buddha Lounge</h3>
          <p class="case-card-desc">
            Ein 360° Rundgang im Sterne-Hotel: Gäste reservieren gezielter, weil sie die Atmosphäre bereits kennen.
          </p>
          <div class="case-card-results">
            <div class="case-stat">
              <span class="case-stat-value">+28%</span>
              <span class="case-stat-label">Mehr Online-Reservierungen</span>
            </div>
            <div class="case-stat">
              <span class="case-stat-value">24/7</span>
              <span class="case-stat-label">Virtuell erlebbar</span>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>


  <!-- ══ 8) KUNDENSTIMMEN ═══════════════════════════════════ -->
  <section class="section" aria-labelledby="testimonials-heading" style="background:var(--surface)">
    <div class="container">
      <p class="section-eyebrow fade-up">Kundenstimmen</p>
      <h2 class="section-title fade-up delay-1" id="testimonials-heading">Das sagen unsere Kunden</h2>

      <div class="testimonials-grid">
        <article class="testimonial-card glass fade-up delay-1">
          <div class="stars" aria-label="5 von 5 Sternen">
            <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
            <?php endfor; ?>
          </div>
          <p class="testimonial-text">
            Die Rundgänge haben unsere Hemmschwelle komplett gesenkt. Viele Gäste trauen sich
            jetzt erst rein, weil sie den Ort vorher online schon besucht haben.
          </p>
          <p class="testimonial-author">Flora Kaffee &amp; Eisbar</p>
          <p class="testimonial-company">2 Rundgänge – 2 Standorte</p>
        </article>

        <article class="testimonial-card glass fade-up delay-2">
          <div class="stars" aria-label="5 von 5 Sternen">
            <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
            <?php endfor; ?>
          </div>
          <p class="testimonial-text">
            Der Rundgang überzeugt neue Kunden schnell von unserem Inventar.
            Das hat unseren Online-Vertrieb spürbar unterstützt.
          </p>
          <p class="testimonial-author">Danbo Flensburg</p>
          <p class="testimonial-company">Digitale Möbelpräsentation</p>
        </article>

        <article class="testimonial-card glass fade-up delay-3">
          <div class="stars" aria-label="5 von 5 Sternen">
            <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
            <?php endfor; ?>
          </div>
          <p class="testimonial-text">
            Der perfekte erste Eindruck für unseren Gastbereich! Gäste reservieren jetzt
            gezielter, weil sie unsere Atmosphäre bereits kennen.
          </p>
          <p class="testimonial-author">Buddha Lounge</p>
          <p class="testimonial-company">360° Präsentation im Sterne-Hotel</p>
        </article>
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
  <section class="section" id="prozess" aria-labelledby="process-heading">
    <div class="container">
      <p class="section-eyebrow fade-up">Ablauf</p>
      <h2 class="section-title fade-up delay-1" id="process-heading">
        So entsteht Ihr professioneller<br>360° Rundgang
      </h2>

      <div class="process-steps">
        <div class="process-card glass fade-up delay-1">
          <p class="process-num">SCHRITT 01</p>
          <div class="process-icon" aria-hidden="true">🎯</div>
          <h3>Strategisches Briefing &amp; Planung</h3>
          <p>
            In einem persönlichen Gespräch definieren wir gemeinsam Ihre Ziele, den gewünschten Stil
            und den optimalen Aufnahmetag. Wir beraten Sie, welche Bereiche Ihrer Location besonders
            wirkungsvoll in Szene gesetzt werden. Kostenlos und unverbindlich.
          </p>
        </div>
        <div class="process-card glass fade-up delay-2">
          <p class="process-num">SCHRITT 02</p>
          <div class="process-icon" aria-hidden="true">📸</div>
          <h3>Professionelle 360° Aufnahme vor Ort</h3>
          <p>
            Unser Team kommt zu Ihnen und digitalisiert Ihre Location mit modernster Scan-Technologie.
            Sorgfältige Nachbearbeitung garantiert gestochen scharfe, stimmungsvolle Bilder, die Ihre
            Räume in bestem Licht zeigen.
          </p>
        </div>
        <div class="process-card glass fade-up delay-3">
          <p class="process-num">SCHRITT 03</p>
          <div class="process-icon" aria-hidden="true">🚀</div>
          <h3>Integration &amp; Live-Schaltung</h3>
          <p>
            Sie erhalten Ihren fertigen iFrame-Code zur Einbindung auf Ihrer Website und in Google Business.
            Wir begleiten Sie beim Launch und stehen für Fragen bereit – bis Ihr Rundgang reibungslos live ist.
          </p>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 10) KONTAKTFORMULAR ════════════════════════════════ -->
  <section class="section" id="kontakt" aria-labelledby="kontakt-heading" style="background:var(--surface)">
    <div class="container">
      <p class="section-eyebrow fade-up text-center">Kontakt</p>
      <h2 class="section-title fade-up delay-1 text-center" id="kontakt-heading">Jetzt unverbindlich anfragen</h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        Wir erstellen Ihnen ein klares Angebot mit Zeitplan und transparenten Kosten – kostenlos und persönlich.
      </p>

      <div class="contact-grid fade-up delay-3">
        <!-- Contact info -->
        <div class="contact-info">
          <h3>So erreichen Sie uns</h3>
          <p>
            Nutzen Sie das Formular für eine schnelle Anfrage – oder schreiben Sie uns
            direkt per E-Mail.
          </p>
          <p style="margin-top:1.5rem">
            <a href="mailto:info@visitfy.de">info@visitfy.de</a>
          </p>
          <div style="margin-top:2.5rem">
            <p style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem">Antwortzeit</p>
            <p>In der Regel innerhalb von 24 Stunden an Werktagen.</p>
          </div>
          <div style="margin-top:2rem">
            <p style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem">Standort</p>
            <p>Flensburg, Deutschland</p>
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
  <section class="section" id="faq" aria-labelledby="faq-heading">
    <div class="container">
      <p class="section-eyebrow fade-up text-center">Antworten</p>
      <h2 class="section-title fade-up delay-1 text-center" id="faq-heading">Häufige Fragen</h2>
      <p class="section-sub fade-up delay-2 text-center" style="margin-inline:auto">
        Alles Wichtige zu 360° Rundgängen, Ablauf, Kosten und Branchen auf einen Blick.
      </p>

      <div class="faq-list fade-up delay-3" style="margin-inline:auto">

        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            Was ist ein 360° Rundgang?
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region">
            <div class="faq-answer-inner">
              <p>
                Ein 360° Rundgang ist eine interaktive, virtuelle Begehung Ihrer Räume. Mit modernster
                Kameratechnik werden alle Bereiche Ihrer Location fotorealistisch erfasst und zu einem
                nahtlosen, immersiven Erlebnis zusammengesetzt.
              </p>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            Welche Vorteile bietet ein 360° Rundgang?
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region">
            <div class="faq-answer-inner">
              <p>Ein 360° Rundgang bietet Ihrem Unternehmen messbare Mehrwerte:</p>
              <ul>
                <li>Stärkeres Vertrauen bei potenziellen Kunden noch vor dem ersten Besuch</li>
                <li>Höhere Sichtbarkeit auf Google Maps und in der organischen Suche</li>
                <li>Mehr qualifizierte Anfragen durch Interessenten, die bereits überzeugt sind</li>
                <li>24/7 verfügbares, interaktives Schaufenster für Ihre Location</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            Für welche Branchen eignet sich ein 360° Rundgang?
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region">
            <div class="faq-answer-inner">
              <p>
                Grundsätzlich für jede Location, die Kunden physisch besuchen oder vorab erleben sollen:
                Gastronomie, Hotels, Immobilien, Einzelhandel, Praxen, Fitnessstudios und mehr.
              </p>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            Was kostet ein 360° Rundgang?
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region">
            <div class="faq-answer-inner">
              <p>
                Die Kosten richten sich nach Größe und Raumanzahl Ihrer Location. Für ein transparentes,
                individuelles Angebot beraten wir Sie gerne persönlich und unverbindlich.
              </p>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            Wie lange dauert die Erstellung?
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region">
            <div class="faq-answer-inner">
              <p>
                Nach Briefing und Aufnahme vor Ort ist Ihr Rundgang im Durchschnitt innerhalb von
                5 Werktagen live. Für größere Locations kalkulieren wir gemeinsam den Zeitplan.
              </p>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            Wo kann ich den Rundgang einbinden?
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region">
            <div class="faq-answer-inner">
              <p>Überall dort, wo Ihre Kunden sind:</p>
              <ul>
                <li>Auf Ihrer eigenen Website (per iFrame, ein Klick)</li>
                <li>Direkt in Ihrem Google Business Profil</li>
                <li>In Buchungsportalen und auf Social Media</li>
              </ul>
            </div>
          </div>
        </div>

      </div>

      <div class="text-center" style="margin-top:3rem">
        <a href="<?= htmlspecialchars(visitfy_url('pages/faq.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Alle Fragen ansehen</a>
      </div>
    </div>
  </section>

</div><!-- /#main-content -->

<?php require __DIR__ . '/partials/footer.php'; ?>
