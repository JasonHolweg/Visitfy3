<?php
/**
 * Visitfy3 – pages/partner.php
 * Partner-werden Seite
 */
require __DIR__ . '/../partials/cms.php';
require_once __DIR__ . '/../partials/turnstile.php';
$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? visitfy_base_path() : '../';
$contentConfig = visitfy_load_json(__DIR__ . '/../assets/data/content.json', []);
$pageTitle = 'Partner werden | Visitfy – 360° Rundgänge';
$pageDesc  = 'Werden Sie Visitfy-Partner: Agenturen, Fotografen, Locations und Marketingprofis profitieren von unserer 360°-Expertise. Jetzt Partner-Formular ausfüllen.';

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content">

  <section class="page-hero partner-page-hero">
    <div class="container">
      <div class="partner-hero-inner">
        <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.hero_eyebrow', 'Wir suchen Dich!'), ENT_QUOTES, 'UTF-8') ?></p>
        <h1 class="fade-up delay-1"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.hero_title', 'Dein Business. Deine Stadt. Dein Erfolg.'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="fade-up delay-2">
          <?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.hero_sub', 'Werde unser offizieller Partner für virtuelle 360°-Rundgänge und starte in deiner Stadt durch.'), ENT_QUOTES, 'UTF-8') ?>
        </p>
        <a href="#partner-form" class="btn btn-primary fade-up delay-3 js-btnfx-partner"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.hero_button_text', 'Jetzt Partner werden'), ENT_QUOTES, 'UTF-8') ?></a>
      </div>
    </div>
  </section>

  <section class="section partner-proof-section" aria-labelledby="partner-proof-heading">
    <div class="container">
      <div class="partner-proof-intro">
        <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.proof_eyebrow', 'Visitfy'), ENT_QUOTES, 'UTF-8') ?></p>
        <h2 class="section-title fade-up delay-1" id="partner-proof-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.proof_title', 'Ein Modell, das auf Klarheit statt Komplexität setzt.'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="section-sub fade-up delay-2">
          <?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.proof_sub', 'Ein funktionierendes System, ein klarer Markt und direkte Unterstützung: Genau darauf ist diese Partnerschaft ausgelegt.'), ENT_QUOTES, 'UTF-8') ?>
        </p>
      </div>

      <div class="partner-benefits">
        <article class="benefit-card glass fade-up delay-1">
          <p class="partner-card-kicker"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_1_kicker', 'Visitfy'), ENT_QUOTES, 'UTF-8') ?></p>
          <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_1_title', 'Funktionierendes System'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_1_text', 'Wir haben’s getestet. Verfeinert. Wiederholt. Was du bekommst, ist nicht neu – sondern durchdacht, schlank und wirksam.'), ENT_QUOTES, 'UTF-8') ?></p>
        </article>
        <article class="benefit-card glass fade-up delay-2">
          <p class="partner-card-kicker"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_2_kicker', 'Visitfy'), ENT_QUOTES, 'UTF-8') ?></p>
          <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_2_title', 'Geringes Startkapital'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_2_text', 'Du brauchst kein Büro, kein Lager und keine teure Ausstattung. Starte smart – mit einem klaren Plan und wenig Risiko. Was zählt: dein Einsatz und dein Wille, wirklich loszulegen.'), ENT_QUOTES, 'UTF-8') ?></p>
        </article>
        <article class="benefit-card glass fade-up delay-3">
          <p class="partner-card-kicker"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_3_kicker', 'Visitfy'), ENT_QUOTES, 'UTF-8') ?></p>
          <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_3_title', 'Nicht allein. Nie.'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.card_3_text', 'Bei Fragen rufst du nicht irgendwo an – sondern direkt bei uns. Du bekommst echten Support, kein Ticketsystem. Persönlich. Ehrlich. Schnell.'), ENT_QUOTES, 'UTF-8') ?></p>
        </article>
      </div>

      <div class="partner-fit-panel glass fade-up delay-4">
        <div>
          <p class="section-eyebrow"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.fit_eyebrow', 'Passt das zu dir?'), ENT_QUOTES, 'UTF-8') ?></p>
          <h2 class="section-title"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.fit_title', 'Dein nächster Schritt kann klar und unkompliziert starten.'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <div class="partner-fit-copy">
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.fit_text', 'Du bist zuverlässig, kommunikativ und willst dir was Eigenes aufbauen? Dann bist du hier richtig. Fülle jetzt das kurze Formular aus – wir melden uns persönlich bei dir.'), ENT_QUOTES, 'UTF-8') ?></p>
          <p class="partner-fit-note"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.fit_note', 'Unverbindlich. Ohne Risiko. Nur echtes Interesse zählt.'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>
    </div>
  </section>

  <section class="section partner-form-section" id="partner-form" aria-labelledby="partner-form-heading">
    <div class="container">
      <div style="max-width:700px;margin:0 auto">
        <p class="section-eyebrow text-center fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_eyebrow', 'Jetzt Partner werden'), ENT_QUOTES, 'UTF-8') ?></p>
        <h2 class="section-title text-center fade-up delay-1" id="partner-form-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_title', 'Lass uns sprechen.'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="section-sub text-center fade-up delay-2" style="margin:0 auto 3rem">
          <?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_sub', 'Fülle das kurze Formular aus – wir melden uns persönlich bei dir.'), ENT_QUOTES, 'UTF-8') ?>
        </p>

        <div class="contact-form-box fade-up delay-3">
          <form method="post" action="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/partner-handler.php" data-ajax novalidate>
            <!-- Honeypot -->
            <div class="form-honeypot" aria-hidden="true">
              <label for="hp_website">Website</label>
              <input type="text" id="hp_website" name="hp_website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="p_name"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_name_label', 'Name *'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="p_name" name="name" required autocomplete="name" placeholder="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_name_placeholder', 'Max Mustermann'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="form-group">
                <label for="p_firma"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_company_label', 'Firma'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="p_firma" name="firma" autocomplete="organization" placeholder="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_company_placeholder', 'Muster GmbH'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="p_email"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_email_label', 'E-Mail *'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="email" id="p_email" name="email" required autocomplete="email" placeholder="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_email_placeholder', 'name@firma.de'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="form-group">
                <label for="p_rolle"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_role_label', 'Rolle / Bereich'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="p_rolle" name="rolle">
                  <option value=""><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_role_placeholder', 'Bitte wählen…'), ENT_QUOTES, 'UTF-8') ?></option>
                  <option value="sales"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_role_option_1', 'Vertrieb / Sales'), ENT_QUOTES, 'UTF-8') ?></option>
                  <option value="agency"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_role_option_2', 'Agentur / Marketing'), ENT_QUOTES, 'UTF-8') ?></option>
                  <option value="self-employed"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_role_option_3', 'Selbstständig / Unternehmerisch'), ENT_QUOTES, 'UTF-8') ?></option>
                  <option value="other"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_role_option_4', 'Sonstiges'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="p_nachricht"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_message_label', 'Nachricht *'), ENT_QUOTES, 'UTF-8') ?></label>
              <textarea id="p_nachricht" name="nachricht" required rows="5" placeholder="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_message_placeholder', 'Erzähl uns kurz, warum du Partner werden möchtest und in welcher Stadt du starten willst…'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
            </div>

            <div class="form-check">
              <input type="checkbox" id="p_dsgvo" name="dsgvo" required>
              <label for="p_dsgvo">
                <?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_privacy_prefix', 'Ich habe die'), ENT_QUOTES, 'UTF-8') ?>
                <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/datenschutz.php"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_privacy_link', 'Datenschutzerklärung'), ENT_QUOTES, 'UTF-8') ?></a>
                <?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_privacy_suffix', 'gelesen und bin mit der Verarbeitung meiner Daten zur Bearbeitung meiner Anfrage einverstanden. *'), ENT_QUOTES, 'UTF-8') ?>
              </label>
            </div>

            <?= visitfy_turnstile_widget() ?>
            <button type="submit" class="btn btn-primary js-btnfx-partner" style="width:100%"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'partner.form_submit_text', 'Jetzt Partner werden'), ENT_QUOTES, 'UTF-8') ?></button>
            <div class="form-status" role="alert"></div>
          </form>
        </div>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
