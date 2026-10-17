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

  <a href="tel:+4915206955260" class="nav-phone" aria-label="Anrufen: +49 1520 6955260">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    <span>+49 1520 6955260</span>
  </a>

  <a href="<?= htmlspecialchars(visitfy_page_url('kontakt'), ENT_QUOTES, 'UTF-8') ?>" class="nav-cta">Angebot anfragen</a>

  <button class="nav-toggle" id="nav-toggle"
          aria-label="Menü öffnen" aria-expanded="false" aria-controls="nav-mobile">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- Mobile nav drawer -->
<nav class="nav-mobile" id="nav-mobile" role="navigation" aria-label="Mobilnavigation">
  <a href="tel:+4915206955260" class="nav-mobile-phone">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    +49 1520 6955260
  </a>
  <a href="<?= htmlspecialchars(visitfy_page_url('home'), ENT_QUOTES, 'UTF-8') ?>">Start</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('about'), ENT_QUOTES, 'UTF-8') ?>">Über uns</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('partner'), ENT_QUOTES, 'UTF-8') ?>">Partner werden</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('faq'), ENT_QUOTES, 'UTF-8') ?>">FAQ</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('kontakt'), ENT_QUOTES, 'UTF-8') ?>">Kontakt</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('impressum'), ENT_QUOTES, 'UTF-8') ?>">Impressum</a>
  <a href="<?= htmlspecialchars(visitfy_page_url('datenschutz'), ENT_QUOTES, 'UTF-8') ?>">Datenschutz</a>
</nav>
