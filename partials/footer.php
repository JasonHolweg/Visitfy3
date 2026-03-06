<?php
/**
 * Visitfy3 – partials/footer.php
 * Multi-column footer with brand area, gradient background, links.
 * Design based on Visitfy Web2.0 footer style.
 */
if (!isset($root)) $root = '';
?>
<!-- ── Footer ──────────────────────────────────────────────── -->
<footer class="site-footer" role="contentinfo">
  <div class="container">
    <div class="footer-inner">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>index.php" class="nav-logo" aria-label="Visitfy Startseite">
          <img src="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/img/logo.svg" alt="Visitfy" width="120" height="30">
        </a>
        <p>Ihre Location. Virtuell erlebt.<br>360° Rundgänge, die Vertrauen schaffen.</p>
        <div class="footer-socials">
          <a href="https://www.instagram.com/visitfy.de/" target="_blank" rel="noopener noreferrer" class="footer-social-link" aria-label="Instagram">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
          <a href="https://www.facebook.com/people/Visitfy/61567271012669/" target="_blank" rel="noopener noreferrer" class="footer-social-link" aria-label="Facebook">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
          <!-- TODO: LinkedIn / YouTube placeholder -->
        </div>
      </div>

      <!-- Quick links -->
      <div class="footer-links-group">
        <h4>Navigation</h4>
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>index.php">Startseite</a>
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/partner.php">Partner werden</a>
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/faq.php">FAQ</a>
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/kontakt.php">Kontakt</a>
      </div>

      <!-- Legal -->
      <div class="footer-links-group">
        <h4>Rechtliches</h4>
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/impressum.php">Impressum</a>
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/datenschutz.php">Datenschutz</a>
      </div>

      <!-- Contact -->
      <div class="footer-links-group">
        <h4>Kontakt</h4>
        <a href="mailto:info@visitfy.de">info@visitfy.de</a><!-- TODO: verify email -->
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/kontakt.php">Kontaktformular</a>
      </div>

    </div><!-- /.footer-inner -->

    <div class="footer-bottom">
      <span>&copy; <?= (int)date('Y') ?> Visitfy. Alle Rechte vorbehalten.</span>
      <span>
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/impressum.php">Impressum</a>
        &nbsp;·&nbsp;
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/datenschutz.php">Datenschutz</a>
      </span>
    </div>
  </div><!-- /.container -->
</footer>

<script src="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/js/main.js"></script>
<script src="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/js/intro.js"></script>
</body>
</html>
