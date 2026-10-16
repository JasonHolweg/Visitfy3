<?php
/**
 * Visitfy3 – partials/header.php
 * Glass floating navbar with logo, links, mobile toggle.
 */
require_once __DIR__ . '/cms.php';
if (!isset($root)) $root = visitfy_site_root();
?>
<body>

<!-- ── Navigation ──────────────────────────────────────────── -->
<nav class="site-nav" role="navigation" aria-label="Hauptnavigation">
  <a href="<?= htmlspecialchars(visitfy_page_url('home'), ENT_QUOTES, 'UTF-8') ?>" class="nav-logo" aria-label="Visitfy – Startseite">
    <img src="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/img/logo.svg" alt="Visitfy Logo" width="140" height="35">
  </a>

  <ul class="nav-links" role="list">
    <li><a href="<?= htmlspecialchars(visitfy_page_url('home'), ENT_QUOTES, 'UTF-8') ?>">Start</a></li>
    <li><a href="<?= htmlspecialchars(visitfy_page_url('about'), ENT_QUOTES, 'UTF-8') ?>">Über uns</a></li>
    <li><a href="<?= htmlspecialchars(visitfy_page_url('partner'), ENT_QUOTES, 'UTF-8') ?>">Partner werden</a></li>
    <li><a href="<?= htmlspecialchars(visitfy_page_url('faq'), ENT_QUOTES, 'UTF-8') ?>">FAQ</a></li>
    <li><a href="<?= htmlspecialchars(visitfy_page_url('kontakt'), ENT_QUOTES, 'UTF-8') ?>">Kontakt</a></li>
  </ul>

  <a href="<?= htmlspecialchars(visitfy_page_url('kontakt'), ENT_QUOTES, 'UTF-8') ?>" class="nav-cta">Angebot anfragen</a>

  <button class="nav-toggle" id="nav-toggle"
          aria-label="Menü öffnen" aria-expanded="false" aria-controls="nav-mobile">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- Mobile nav drawer -->
<nav class="nav-mobile" id="nav-mobile" role="navigation" aria-label="Mobilnavigation">
  <a href="<?= htmlspecialchars(visitfy_page_url('home'), ENT_QUOTES, 'UTF-8') ?>">Start</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('about'), ENT_QUOTES, 'UTF-8') ?>">Über uns</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('partner'), ENT_QUOTES, 'UTF-8') ?>">Partner werden</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('faq'), ENT_QUOTES, 'UTF-8') ?>">FAQ</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('kontakt'), ENT_QUOTES, 'UTF-8') ?>">Kontakt</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('impressum'), ENT_QUOTES, 'UTF-8') ?>">Impressum</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('datenschutz'), ENT_QUOTES, 'UTF-8') ?>">Datenschutz</a>
</nav>
