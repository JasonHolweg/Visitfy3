<?php
/**
 * Visitfy3 – index.php
 * Main router: ?page=... with strict whitelist (LFI protection).
 * Default: homepage.
 */

/* ── Security: whitelisted pages ────────────────────────── */
$allowed = [
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
$root      = '';
$pageTitle = 'Visitfy | 360° Rundgänge für moderne Unternehmen';
$pageDesc  = 'Visitfy entwickelt professionelle 360° virtuelle Rundgänge für Unternehmen jeder Branche – realistisch, hochwertig und sofort einsatzbereit für Website und Google Business.';

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
?>

<!-- ══════════════════════════════════════════════════════════
     INTRO / PRELOADER OVERLAY
══════════════════════════════════════════════════════════ -->
<div id="intro" role="presentation" aria-hidden="true">
  <canvas id="intro-canvas"></canvas>
  <div id="intro-text">
    <img src="assets/img/visitfy-logo.svg" alt="Visitfy" class="intro-logo-mark">
    <p>360° Rundgänge die begeistern</p>
    <p class="scroll-hint">Klicken zum Fortfahren</p>
  </div>
  <button id="skip-btn" type="button">Überspringen ↓</button>
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
        <p class="hero-eyebrow">360° Rundgänge für moderne Unternehmen</p>
        <h1 id="hero-heading" class="hero-rotating-text" aria-label="MEHR SICHTBARKEIT. MEHR VERTRAUEN. MEHR ANFRAGEN.">
          <span class="hero-rotating-prefix">MEHR</span><br>
          <span class="hero-rotating-word" data-hero-rotate-word>SICHTBARKEIT.</span>
        </h1>
        <p class="hero-desc">
          Visitfy entwickelt hochwertige 360° Erlebnisse für Unternehmen jeder Branche –
          realistisch, hochwertig und sofort einsatzbereit für Website und Google&nbsp;Business.
        </p>
        <div class="hero-actions">
          <a href="pages/kontakt.php" class="btn btn-primary">Beratung anfragen</a>
          <a href="#tours" class="btn btn-secondary">Unsere Ergebnisse</a>
        </div>
      </div>
    </div>

    <div class="hero-scroll" aria-hidden="true">Scroll</div>
  </section>


  <!-- ══ 2) KPI / COUNT-UP ══════════════════════════════════ -->
  <section class="section kpi-section" id="kpi" aria-labelledby="kpi-heading">
    <div class="container">
      <p class="section-eyebrow fade-up">Messbare Ergebnisse</p>
      <h2 class="section-title fade-up delay-1" id="kpi-heading">Zahlen, die überzeugen</h2>

      <div class="kpi-grid">
        <div class="kpi-card glass fade-up delay-1">
          <div class="kpi-number" data-countup data-target="40" data-suffix="%">0%</div>
          <div class="kpi-label">+ längere Verweildauer auf Ihrer Website</div>
        </div>
        <div class="kpi-card glass fade-up delay-2">
          <div class="kpi-number" data-countup data-target="5" data-suffix=" Tage">0 Tage</div>
          <div class="kpi-label">Ø bis Ihr Rundgang live geht</div>
        </div>
        <div class="kpi-card glass fade-up delay-3">
          <div class="kpi-number" data-countup data-target="420" data-suffix="+">0+</div>
          <div class="kpi-label">umgesetzte Rundgänge</div>
        </div>
        <div class="kpi-card glass fade-up delay-4">
          <div class="kpi-number" data-countup data-target="98" data-suffix="%">0%</div>
          <div class="kpi-label">zufriedene Kunden</div>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 3) VALUE PROPOSITION CARDS ════════════════════════ -->
  <section class="section" aria-labelledby="value-heading">
    <div class="container">
      <p class="section-eyebrow fade-up">Warum 360°?</p>
      <h2 class="section-title fade-up delay-1" id="value-heading">
        360° Rundgänge für jede Branche –<br>und jede Location
      </h2>
      <p class="section-sub fade-up delay-2">
        Ein virtueller Rundgang schafft unmittelbar Nähe – noch bevor der erste echte Kontakt stattfindet.
      </p>

      <div class="features-grid">
        <article class="feature-card glass fade-up delay-1">
          <div class="feature-icon" aria-hidden="true">🏛️</div>
          <h3>Räume verkaufen</h3>
          <p>
            Virtuelle Rundgänge lassen Kunden Ihre Räume erleben, bevor sie die Tür öffnen.
            Das senkt die Hemmschwelle und steigert die Konversionsrate von Interessenten zu echten Besuchern.
          </p>
        </article>
        <article class="feature-card glass fade-up delay-2">
          <div class="feature-icon" aria-hidden="true">🤝</div>
          <h3>Vertrauen aufbauen</h3>
          <p>
            Transparenz erzeugt Vertrauen. Wer Ihre Location bereits virtuell besucht hat, kommt mit einem
            positiven Vorurteil und deutlich höherer Kaufbereitschaft.
          </p>
        </article>
        <article class="feature-card glass fade-up delay-3">
          <div class="feature-icon" aria-hidden="true">✨</div>
          <h3>Atmosphäre erlebbar machen</h3>
          <p>
            Ein Foto zeigt einen Raum. Ein 360° Rundgang lässt Ihre Gäste Ihre Atmosphäre, Ihr Licht,
            Ihr Raumgefühl wirklich spüren – rund um die Uhr, von überall.
          </p>
        </article>
        <article class="feature-card glass fade-up delay-4">
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


  <!-- ══ 4) SCROLL-STACK – EXAMPLE TOURS ════════════════════ -->
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

      <!-- Scroll-Stack: each item is CSS sticky, JS adds rotation transform -->
      <div class="stack-container" aria-label="Rundgang-Beispiele">
<?php foreach ($tours as $i => $tour):
  $title  = htmlspecialchars($tour['title']       ?? 'Rundgang', ENT_QUOTES, 'UTF-8');
  $tag    = htmlspecialchars($tour['tag']         ?? '',         ENT_QUOTES, 'UTF-8');
  $desc   = htmlspecialchars($tour['description'] ?? '',         ENT_QUOTES, 'UTF-8');
  $url    = $tour['matterportUrl'] ?? '';
  /* Validate: only allow matterport.com URLs */
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
            <!-- 16:9 iFrame with lazy-load -->
            <div class="iframe-wrap">
<?php if ($safeUrl): ?>
              <div class="iframe-placeholder"
                   data-src="<?= $safeUrl ?>"
                   data-title="360° Rundgang – <?= $title ?>"
                   role="button"
                   tabindex="0"
                   aria-label="<?= $title ?> laden">
              </div>
<?php else: ?>
              <div class="iframe-placeholder" aria-label="Kein Rundgang verfügbar"></div>
<?php endif; ?>
            </div>
          </div>
        </article>
<?php endforeach; ?>
      </div><!-- /.stack-container -->
    </div>
  </section>


  <!-- ══ 5) LOGO MARQUEE ════════════════════════════════════ -->
  <section class="marquee-section" aria-label="Unsere Kunden">
    <p class="marquee-label">Vertrauen von führenden Unternehmen</p>
    <div class="marquee-track-wrap">
      <div class="marquee-track" aria-hidden="true">
<?php if ($clientLogoFiles): ?>
<?php foreach ($clientLogoFiles as $logoPath):
  $fileName = basename($logoPath);
  $baseName = pathinfo($fileName, PATHINFO_FILENAME);
  $prettyName = preg_replace('/[-_]+/', ' ', $baseName);
  $prettyName = preg_replace('/\s+/', ' ', (string)$prettyName);
  $prettyName = trim((string)$prettyName);
  $altText = $prettyName !== '' ? $prettyName : 'Kundenlogo';
  $logoSrc = 'assets/img/client-logos/' . rawurlencode($fileName);
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


  <!-- ══ 6) PROCESS ═════════════════════════════════════════ -->
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


  <!-- ══ 7) TESTIMONIALS ════════════════════════════════════ -->
  <section class="section" aria-labelledby="testimonials-heading">
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


  <!-- ══ 8) ÜBER VISITFY + DER VISITFY-UNTERSCHIED ══════════ -->
  <section class="section" id="ueber" aria-labelledby="about-heading">
    <div class="container">
      <p class="section-eyebrow fade-up">Über uns</p>
      <h2 class="section-title fade-up delay-1" id="about-heading">Über Visitfy</h2>

      <div class="about-grid">
        <div class="about-text fade-up delay-2">
          <p>
            Visitfy ist ein spezialisierter Anbieter für professionelle 360° virtuelle Rundgänge.
            Wir unterstützen Unternehmen jeder Branche dabei, ihre Location digital erlebbar zu machen –
            von der ersten Planung bis zur nahtlosen Integration in Website und Google&nbsp;Business.
          </p>
          <p>
            Unsere Rundgänge sind mehr als Panoramas: Sie sind durchdachte, emotional wirkungsvolle
            Erlebnisse, die Vertrauen aufbauen und qualifizierte Anfragen generieren.
            Jedes Projekt entsteht mit dem Anspruch, die einzigartige Atmosphäre Ihrer Location
            so authentisch wie möglich einzufangen.
          </p>
          <p>
            Mit über 420 abgeschlossenen Projekten und einem Team, das Gastro-, Immobilien- und
            Tech-Know-how vereint, stehen wir für Qualität, Schnelligkeit und echten Mehrwert.
          </p>
        </div>

        <div class="fade-up delay-3">
          <div class="glass" style="padding:2rem;border-radius:var(--radius);margin-bottom:2rem">
            <h3 style="font-size:1.2rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:1.5rem;line-height:1.2">
              Der Visitfy-Unterschied:<br>Erleben statt nur sehen.
            </h3>

            <div class="about-features">
              <h3>Was uns auszeichnet</h3>
              <ul>
                <li>Emotionale Resonanz – Rundgänge, die berühren</li>
                <li>Visuelle Perfektion – Details, die überzeugen</li>
                <li>Individuelle Ästhetik – Ihr Stil, nicht unser Template</li>
                <li>Strategische Flexibilität – passt zu jeder Branche</li>
                <li>Signifikante Reichweite – Google, Web, Social, VR</li>
              </ul>
            </div>
          </div>

          <div class="glass" style="padding:2rem;border-radius:var(--radius)">
            <div class="about-features">
              <h3>Perfektion in jedem Detail</h3>
              <ul>
                <li>Expertise, die beeindruckt</li>
                <li>Qualität ohne Kompromisse</li>
                <li>Beratung auf höchstem Niveau</li>
                <li>Schnelle Umsetzung – Ø 5 Tage bis live</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ══ 9) FINAL CTA ═══════════════════════════════════════ -->
  <section class="cta-banner" aria-labelledby="cta-heading">
    <div class="container">
      <div class="cta-inner fade-up">
        <h2 id="cta-heading">
          Bereit, Ihre Location<br>digital erlebbar zu machen?
        </h2>
        <p>
          Wir erstellen Ihnen ein klares Angebot mit Zeitplan und transparenten Kosten –
          kostenlos, unverbindlich und persönlich.
        </p>
        <div class="cta-actions">
          <a href="pages/kontakt.php" class="btn btn-primary">Angebot anfragen</a>
          <a href="pages/faq.php" class="btn btn-secondary">Häufige Fragen</a>
        </div>
      </div>
    </div>
  </section>

</div><!-- /#main-content -->

<?php require __DIR__ . '/partials/footer.php'; ?>
