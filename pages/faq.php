<?php
/**
 * Visitfy3 – pages/faq.php
 * FAQ with Accordion (Vanilla JS)
 */
$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? '' : '../';
$pageTitle = 'FAQ | Visitfy – 360° Rundgänge';
$pageDesc  = 'Häufig gestellte Fragen zu 360° virtuellen Rundgängen von Visitfy: Was ist ein Rundgang? Welche Vorteile? Welche Branchen? Was kostet es?';

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content">

  <section class="page-hero">
    <div class="container">
      <p class="section-eyebrow fade-up">Antworten</p>
      <h1 class="fade-up delay-1">Häufige Fragen</h1>
      <p class="fade-up delay-2">
        Alles Wichtige zu 360° Rundgängen, Ablauf, Kosten und Branchen auf einen Blick.
      </p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="faq-list fade-up">

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
              <p>
                Besucher können sich frei durch Ihre Räume bewegen, Bereiche erkunden und optional
                eingebettete Informationen wie Menüs, Preise oder Kontaktoptionen abrufen – direkt
                im Rundgang, rund um die Uhr, von jedem Gerät aus.
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
                <li>Interaktive Hotspots: Menüs, Preislisten, Videos, Kontaktformulare</li>
                <li>Einbindung auf Website, Booking-Portalen und Social Media</li>
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
                Grundsätzlich für jede Location, die Kunden physisch besuchen oder vorab erleben sollen.
                Besonders bewährt haben sich Rundgänge in folgenden Branchen:
              </p>
              <ul>
                <li>Gastronomie (Restaurants, Cafés, Bars, Eventlocations)</li>
                <li>Hotels &amp; Wellness (Zimmer, Spa, Konferenzräume)</li>
                <li>Immobilien (Verkauf, Vermietung, Neubauprojekte)</li>
                <li>Einzelhandel &amp; Showrooms</li>
                <li>Praxen &amp; medizinische Einrichtungen</li>
                <li>Fitnessstudios, Sportanlagen &amp; Freizeiteinrichtungen</li>
                <li>Bildungseinrichtungen &amp; Coworking-Spaces</li>
              </ul>
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
                Die Kosten richten sich nach Größe und Raumanzahl Ihrer Location sowie dem gewünschten
                Funktionsumfang (z. B. interaktive Hotspots, Branding, mehrsprachige Beschriftungen).
              </p>
              <p>
                Für ein transparentes, individuelles Angebot beraten wir Sie gerne persönlich und
                unverbindlich – einfach das Kontaktformular nutzen.
              </p>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" aria-expanded="false">
            Wie lange dauert die Erstellung eines Rundgangs?
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region">
            <div class="faq-answer-inner">
              <p>
                Nach dem Briefing-Gespräch und der Aufnahme vor Ort ist Ihr Rundgang im Durchschnitt
                innerhalb von 5 Werktagen live. Für größere Locations oder Sonderwünsche kalkulieren
                wir gemeinsam den Zeitplan.
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
                <li>In Buchungsportalen wie Booking.com oder OpenTable</li>
                <li>Auf Social Media als Link oder eingebetteter Post</li>
                <li>In E-Mail-Newslettern als Preview-Link</li>
              </ul>
            </div>
          </div>
        </div>

      </div><!-- /.faq-list -->

      <div class="text-center" style="margin-top:4rem">
        <p style="color:var(--text-dim);margin-bottom:1.5rem">Noch Fragen? Wir helfen gerne persönlich weiter.</p>
        <a href="kontakt.php" class="btn btn-primary">Jetzt Kontakt aufnehmen</a>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
