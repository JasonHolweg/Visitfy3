<?php
/**
 * Visitfy3 – pages/about.php
 * About Us page with mission, differentiators and team.
 */
$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? '' : '../';
$pageTitle = 'Über uns | Visitfy – 360° Rundgänge';
$pageDesc  = 'Lerne Visitfy kennen: Mission, Qualitätsanspruch und das Team hinter professionellen 360° Rundgängen.';

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content">

  <section class="page-hero">
    <div class="container">
      <p class="section-eyebrow fade-up">ÜBER UNS</p>
      <h1 class="fade-up delay-1">Über Visitfy</h1>
      <p class="fade-up delay-2">
        Visitfy ist ein spezialisierter Anbieter für professionelle 360° virtuelle Rundgänge.
      </p>
    </div>
  </section>

  <section class="section" aria-labelledby="about-visitfy-heading">
    <div class="container">
      <h2 class="section-title fade-up" id="about-visitfy-heading">Über Visitfy</h2>

      <div class="about-grid">
        <div class="about-text fade-up delay-1">
          <p>
            Visitfy ist ein spezialisierter Anbieter für professionelle 360° virtuelle Rundgänge.
            Wir unterstützen Unternehmen jeder Branche dabei, ihre Location digital erlebbar zu machen –
            von der ersten Planung bis zur nahtlosen Integration in Website und Google Business.
          </p>
          <p>
            Unsere Rundgänge sind mehr als Panoramas: Sie sind durchdachte, emotional wirkungsvolle
            Erlebnisse, die Vertrauen aufbauen und qualifizierte Anfragen generieren. Jedes Projekt entsteht
            mit dem Anspruch, die einzigartige Atmosphäre Ihrer Location so authentisch wie möglich einzufangen.
          </p>
          <p>
            Mit über 420 abgeschlossenen Projekten und einem Team, das Gastro-, Immobilien- und Tech-Know-how
            vereint, stehen wir für Qualität, Schnelligkeit und echten Mehrwert.
          </p>
        </div>

        <div class="fade-up delay-2">
          <div class="glass about-panel">
            <h3 class="about-difference-title">Der Visitfy-Unterschied:<br>Erleben statt nur sehen.</h3>

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

          <div class="glass about-panel">
            <div class="about-features" style="margin-top:0;">
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

  <section class="section" aria-labelledby="about-team-heading">
    <div class="container">
      <p class="section-eyebrow fade-up">TEAM</p>
      <h2 class="section-title fade-up delay-1" id="about-team-heading">Menschen hinter Visitfy</h2>

      <div class="about-team-grid">
        <article class="about-team-card glass fade-up delay-1">
          <p class="about-team-role">Geschäftsführer</p>
          <h3>Kristian Meister</h3>
          <p>
            Kristian führt Visitfy mit klarem Fokus auf Qualität, Verlässlichkeit und messbare Ergebnisse.
            Er begleitet Projekte strategisch von der ersten Anfrage bis zur erfolgreichen Live-Schaltung.
          </p>
        </article>

        <article class="about-team-card glass fade-up delay-2">
          <p class="about-team-role">Entwickler</p>
          <h3>Jason Holweg</h3>
          <p>
            Jason entwickelt die technischen Grundlagen und sorgt dafür, dass die digitalen Erlebnisse
            performant, modern und nahtlos integrierbar sind.
          </p>
          <p>
            <a class="jason-gradient-link" href="https://jasonholweg.de" target="_blank" rel="noopener noreferrer">jasonholweg.de</a>
          </p>
        </article>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
