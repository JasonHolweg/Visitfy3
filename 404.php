<?php
/**
 * Visitfy3 – 404.php
 * Fehlerseite für Adressen, die es nicht gibt (ErrorDocument 404 in .htaccess).
 */
http_response_code(404);
require __DIR__ . '/partials/cms.php';
$root = visitfy_site_root();
$pageTitle = 'Seite nicht gefunden | Visitfy';
$pageDesc  = 'Diese Seite gibt es auf visitfy.de nicht (mehr).';
$pageRobots = 'noindex, follow';
$noCanonical = true;

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>

<main id="main-content">
  <section class="page-hero">
    <div class="container">
      <p class="section-eyebrow">Fehler 404</p>
      <h1>Diese Seite gibt es nicht.</h1>
      <p>Vielleicht wurde sie verschoben oder die Adresse hat sich geändert. Hier geht es weiter:</p>
      <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;margin-top:2rem">
        <a href="<?= htmlspecialchars(visitfy_page_url('home'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Zur Startseite</a>
        <a href="<?= htmlspecialchars(visitfy_page_url('kontakt'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Rundgang anfragen</a>
        <a href="<?= htmlspecialchars(visitfy_page_url('partner'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Partner werden</a>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
