<?php
/**
 * Visitfy3 – pages/partner.php
 * Partner-werden Seite
 */
$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? '' : '../';
$pageTitle = 'Partner werden | Visitfy – 360° Rundgänge';
$pageDesc  = 'Werden Sie Visitfy-Partner: Agenturen, Fotografen, Locations und Marketingprofis profitieren von unserer 360°-Expertise. Jetzt Partner-Formular ausfüllen.';

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content">

  <!-- Page Hero -->
  <section class="page-hero">
    <div class="container">
      <p class="section-eyebrow fade-up">Kooperationen</p>
      <h1 class="fade-up delay-1">Partner werden</h1>
      <p class="fade-up delay-2">
        Gemeinsam mehr erreichen – für Agenturen, Fotografen, Locations und Marketingprofis.
      </p>
    </div>
  </section>

  <!-- Pitch / Intro -->
  <section class="section">
    <div class="container">
      <div style="max-width:720px;margin:0 auto;text-align:center">
        <p class="section-eyebrow fade-up">Warum Partner?</p>
        <h2 class="section-title fade-up delay-1">Gemeinsam wachsen</h2>
        <p class="section-sub fade-up delay-2" style="margin:0 auto 3rem">
          Ob Agentur, Fotograf, Eventlocation oder Marketingberater – als Visitfy-Partner
          erweitern Sie Ihr Angebot um hochwertige 360° Rundgänge und profitieren von
          einer starken Partnerschaft.
        </p>
      </div>

      <div class="partner-benefits">
        <article class="benefit-card glass fade-up delay-1">
          <h3>🏢 Agenturen &amp; Marketing</h3>
          <p>
            Ergänzen Sie Ihr Portfolio um ein Produkt mit echtem Mehrwert. Visitfy übernimmt
            die Produktion – Sie betreuen Ihre Kunden und erweitern Ihren Umsatz.
          </p>
        </article>
        <article class="benefit-card glass fade-up delay-2">
          <h3>📷 Fotografen &amp; Content Creator</h3>
          <p>
            Kombinieren Sie klassische Fotografie mit interaktiven 360°-Erlebnissen.
            Bieten Sie Kunden ein umfassendes Paket aus einer Hand.
          </p>
        </article>
        <article class="benefit-card glass fade-up delay-3">
          <h3>🏨 Locations &amp; Hotelbetriebe</h3>
          <p>
            Profitieren Sie als Referenzpartner und erhalten Sie exklusive Konditionen
            für eigene Rundgänge sowie Empfehlung an andere Betriebe.
          </p>
        </article>
        <article class="benefit-card glass fade-up delay-4">
          <h3>📊 Vertriebspartner</h3>
          <p>
            Vermitteln Sie Visitfy-Kunden in Ihrem Netzwerk und profitieren Sie von
            attraktiven Kooperationsmodellen. Details auf Anfrage.<!-- TODO: Provisionsdetails einfügen -->
          </p>
        </article>
      </div>

      <!-- Partner benefits list -->
      <div style="max-width:600px;margin:4rem auto 0">
        <h2 class="section-title text-center fade-up">Ihre Vorteile</h2>
        <ul class="about-features" style="margin-top:2rem">
          <li class="fade-up delay-1">Exklusive Partnerkonditionen für Projekte</li>
          <li class="fade-up delay-2">Gemeinschaftliches Marketing &amp; Cross-Promotion</li>
          <li class="fade-up delay-3">Technischer Support &amp; dedizierter Ansprechpartner</li>
          <li class="fade-up delay-4">Zugang zu Referenzmaterialien &amp; Case Studies</li>
          <li class="fade-up">Flexible Kooperationsmodelle (Provision / White-Label / Referenz)<!-- TODO: Modelle finalisieren --></li>
        </ul>
      </div>
    </div>
  </section>

  <!-- Partner Contact Form -->
  <section class="section" style="background:var(--surface);border-top:1px solid var(--line)">
    <div class="container">
      <div style="max-width:700px;margin:0 auto">
        <p class="section-eyebrow text-center fade-up">Jetzt durchstarten</p>
        <h2 class="section-title text-center fade-up delay-1">Partner-Anfrage stellen</h2>
        <p class="section-sub text-center fade-up delay-2" style="margin:0 auto 3rem">
          Füllen Sie das Formular aus – wir melden uns innerhalb von 48 Stunden.
        </p>

        <div class="contact-form-box fade-up delay-3">
          <form method="post" action="partner-handler.php" data-ajax novalidate>
            <!-- Honeypot -->
            <div class="form-honeypot" aria-hidden="true">
              <label for="hp_website">Website</label>
              <input type="text" id="hp_website" name="hp_website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="p_name">Name *</label>
                <input type="text" id="p_name" name="name" required autocomplete="name" placeholder="Max Mustermann">
              </div>
              <div class="form-group">
                <label for="p_firma">Firma</label>
                <input type="text" id="p_firma" name="firma" autocomplete="organization" placeholder="Muster GmbH">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="p_email">E-Mail *</label>
                <input type="email" id="p_email" name="email" required autocomplete="email" placeholder="name@firma.de">
              </div>
              <div class="form-group">
                <label for="p_rolle">Rolle / Bereich</label>
                <select id="p_rolle" name="rolle">
                  <option value="">Bitte wählen…</option>
                  <option value="agentur">Agentur / Marketing</option>
                  <option value="fotograf">Fotograf / Content Creator</option>
                  <option value="location">Location / Hotelbetrieb</option>
                  <option value="vertrieb">Vertriebspartner</option>
                  <option value="sonstiges">Sonstiges</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="p_nachricht">Nachricht *</label>
              <textarea id="p_nachricht" name="nachricht" required rows="5" placeholder="Beschreiben Sie kurz, wie eine Zusammenarbeit aussehen könnte…"></textarea>
            </div>

            <div class="form-check">
              <input type="checkbox" id="p_dsgvo" name="dsgvo" required>
              <label for="p_dsgvo">
                Ich habe die <a href="datenschutz.php">Datenschutzerklärung</a> gelesen und bin mit der
                Verarbeitung meiner Daten zur Bearbeitung meiner Anfrage einverstanden. *
              </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%">Partner-Anfrage absenden</button>
            <div class="form-status" role="alert"></div>
          </form>
        </div>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
