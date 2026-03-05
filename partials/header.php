<?php
/**
 * Visitfy3 – partials/header.php
 * Glass floating navbar with logo, links, mobile toggle.
 */
if (!isset($root)) $root = '';
?>
<body>

<!-- ── Navigation ──────────────────────────────────────────── -->
<nav class="site-nav" role="navigation" aria-label="Hauptnavigation">
  <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>index.php" class="nav-logo" aria-label="Visitfy – Startseite">
    <img src="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/img/logo.svg" alt="Visitfy Logo" width="140" height="35">
  </a>

  <ul class="nav-links" role="list">
    <li><a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>index.php">Start</a></li>
    <li><a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/partner.php">Partner werden</a></li>
    <li><a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/faq.php">FAQ</a></li>
    <li><a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/kontakt.php">Kontakt</a></li>
  </ul>

  <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/kontakt.php" class="nav-cta">Angebot anfragen</a>

  <button class="nav-toggle" id="nav-toggle"
          aria-label="Menü öffnen" aria-expanded="false" aria-controls="nav-mobile">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- Mobile nav drawer -->
<nav class="nav-mobile" id="nav-mobile" role="navigation" aria-label="Mobilnavigation">
  <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>index.php">Start</a>
  <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/partner.php">Partner werden</a>
  <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/faq.php">FAQ</a>
  <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/kontakt.php">Kontakt</a>
  <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/impressum.php">Impressum</a>
  <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/datenschutz.php">Datenschutz</a>
</nav>
