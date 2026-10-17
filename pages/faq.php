<?php
/**
 * Visitfy3 – pages/faq.php
 * FAQ with Accordion (Vanilla JS)
 */
require __DIR__ . '/../partials/cms.php';
$root = visitfy_site_root();
$canonicalPath = visitfy_page_url('faq');
$pageTitle = '360°-Rundgang: Fragen zu Kosten, Ablauf und Einbindung | Visitfy';
$pageDesc  = 'Was kostet ein virtueller 360°-Rundgang, wie lange dauert die Erstellung und wo lässt er sich einbinden? Antworten auf die häufigsten Fragen.';

/* Fragen und Antworten: sichtbar auf der Seite UND als FAQPage-Schema */
$faqs = [
    [
        'q' => 'Was ist ein 360° Rundgang?',
        'a' => '<p>Ein 360° Rundgang ist eine interaktive, virtuelle Begehung Ihrer Räume. Mit modernster Kameratechnik werden alle Bereiche Ihrer Location fotorealistisch erfasst und zu einem nahtlosen, immersiven Erlebnis zusammengesetzt.</p><p>Besucher können sich frei durch Ihre Räume bewegen, Bereiche erkunden und optional eingebettete Informationen wie Menüs, Preise oder Kontaktoptionen abrufen – direkt im Rundgang, rund um die Uhr, von jedem Gerät aus.</p>',
    ],
    [
        'q' => 'Welche Vorteile bietet ein 360° Rundgang?',
        'a' => '<p>Ein 360° Rundgang bietet Ihrem Unternehmen messbare Mehrwerte:</p><ul><li>Stärkeres Vertrauen bei potenziellen Kunden noch vor dem ersten Besuch</li><li>Höhere Sichtbarkeit auf Google Maps und in der organischen Suche</li><li>Mehr qualifizierte Anfragen durch Interessenten, die bereits überzeugt sind</li><li>24/7 verfügbares, interaktives Schaufenster für Ihre Location</li><li>Interaktive Hotspots: Menüs, Preislisten, Videos, Kontaktformulare</li><li>Einbindung auf Website, Booking-Portalen und Social Media</li></ul>',
    ],
    [
        'q' => 'Für welche Branchen eignet sich ein 360° Rundgang?',
        'a' => '<p>Grundsätzlich für jede Location, die Kunden physisch besuchen oder vorab erleben sollen. Besonders bewährt haben sich Rundgänge in folgenden Branchen:</p><ul><li>Gastronomie (Restaurants, Cafés, Bars, Eventlocations)</li><li>Hotels &amp; Wellness (Zimmer, Spa, Konferenzräume)</li><li>Immobilien (Verkauf, Vermietung, Neubauprojekte)</li><li>Einzelhandel &amp; Showrooms</li><li>Praxen &amp; medizinische Einrichtungen</li><li>Fitnessstudios, Sportanlagen &amp; Freizeiteinrichtungen</li><li>Bildungseinrichtungen &amp; Coworking-Spaces</li></ul>',
    ],
    [
        'q' => 'Was kostet ein 360° Rundgang?',
        'a' => '<p>Die Kosten richten sich nach Größe und Raumanzahl Ihrer Location sowie dem gewünschten Funktionsumfang (z. B. interaktive Hotspots, Branding, mehrsprachige Beschriftungen).</p><p>Für ein transparentes, individuelles Angebot beraten wir Sie gerne persönlich und unverbindlich – einfach das Kontaktformular nutzen.</p>',
    ],
    [
        'q' => 'Wie lange dauert die Erstellung eines Rundgangs?',
        'a' => '<p>Nach dem Briefing-Gespräch und der Aufnahme vor Ort ist Ihr Rundgang spätestens 5 Tage später online. Für größere Locations oder Sonderwünsche kalkulieren wir gemeinsam den Zeitplan.</p>',
    ],
    [
        'q' => 'Wo kann ich den Rundgang einbinden?',
        'a' => '<p>Überall dort, wo Ihre Kunden sind:</p><ul><li>Auf Ihrer eigenen Website (per iFrame, ein Klick)</li><li>Direkt in Ihrem Google Business Profil</li><li>In Buchungsportalen wie Booking.com oder OpenTable</li><li>Auf Social Media als Link oder eingebetteter Post</li><li>In E-Mail-Newslettern als Preview-Link</li></ul>',
    ],
];

require_once __DIR__ . '/../partials/schema.php';
$faqUrl = visitfy_page_url('faq', true);
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@graph' => [
        visitfy_schema_webpage($faqUrl, $pageTitle, $pageDesc, [['Start', visitfy_page_url('home', true)], ['FAQ', $faqUrl]]),
        visitfy_schema_faq($faqs, $faqUrl . '#faq'),
        ['@type' => 'Organization', '@id' => visitfy_schema_org_id(), 'name' => 'Visitfy', 'url' => VISITFY_SITE_URL . '/'],
    ],
]];

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
<?php foreach ($faqs as $i => $f): ?>
        <div class="faq-item">
          <button class="faq-question" aria-expanded="false" aria-controls="faq-a<?= $i ?>" id="faq-q<?= $i ?>">
            <?= htmlspecialchars($f['q'], ENT_QUOTES, 'UTF-8') ?>
            <span class="faq-arrow" aria-hidden="true">▼</span>
          </button>
          <div class="faq-answer" role="region" id="faq-a<?= $i ?>" aria-labelledby="faq-q<?= $i ?>">
            <div class="faq-answer-inner">
              <?= $f['a'] ?>
            </div>
          </div>
        </div>
<?php endforeach; ?>
      </div><!-- /.faq-list -->

      <div class="text-center" style="margin-top:4rem">
        <p style="color:var(--text-dim);margin-bottom:1.5rem">Noch Fragen? Wir helfen gerne persönlich weiter.</p>
        <a href="<?= htmlspecialchars(visitfy_page_url('kontakt'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Jetzt Kontakt aufnehmen</a>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
