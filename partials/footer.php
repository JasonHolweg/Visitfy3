<?php
/**
 * Visitfy3 – partials/footer.php
 * Multi-column footer with brand area, gradient background, links.
 * Design based on Visitfy Web2.0 footer style.
 */
if (!isset($root)) $root = '';
require_once __DIR__ . '/cms.php';

$contentConfig = visitfy_load_json(__DIR__ . '/../assets/data/content.json', []);
$scriptConfig = visitfy_load_json(__DIR__ . '/../assets/data/script-config.json', []);

$buttonFxConfig = visitfy_get($contentConfig, 'button_fx', []);
if (!is_array($buttonFxConfig)) {
  $buttonFxConfig = [];
}

$buttonFxEnabled = !empty($buttonFxConfig['enabled']);
$buttonFxShimmer = !array_key_exists('shimmer', $buttonFxConfig) || !empty($buttonFxConfig['shimmer']);
$buttonFxColor = (string)($buttonFxConfig['color'] ?? '#8ec9ff');
if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $buttonFxColor)) {
  $buttonFxColor = '#8ec9ff';
}

$buttonFxSelectorMap = [
  'kontakt_submit' => '.js-btnfx-kontakt',
  'partner_submit' => '.js-btnfx-partner',
  'hero_primary' => '.js-btnfx-hero-primary',
  'hero_secondary' => '.js-btnfx-hero-secondary',
  'cta_primary' => '.js-btnfx-cta-primary',
  'cta_secondary' => '.js-btnfx-cta-secondary',
];

$buttonFxTargets = $buttonFxConfig['targets'] ?? [];
if (!is_array($buttonFxTargets)) {
  $buttonFxTargets = [];
}

$buttonFxSelectors = [];
foreach ($buttonFxTargets as $target) {
  $target = (string)$target;
  if (isset($buttonFxSelectorMap[$target]) && !in_array($buttonFxSelectorMap[$target], $buttonFxSelectors, true)) {
    $buttonFxSelectors[] = $buttonFxSelectorMap[$target];
  }
}
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
        <p>
      <?php foreach (visitfy_split_lines((string)visitfy_get($contentConfig, 'footer.brand_text', 'Ihre Location. Virtuell erlebt.\n360° Rundgänge, die Vertrauen schaffen.')) as $line): ?>
          <?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') ?><br>
      <?php endforeach; ?>
        </p>
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
        <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/about.php">Über uns</a>
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
        <a href="mailto:<?= htmlspecialchars((string)visitfy_get($contentConfig, 'footer.contact_email', 'info@visitfy.de'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'footer.contact_email', 'info@visitfy.de'), ENT_QUOTES, 'UTF-8') ?></a><!-- TODO: verify email -->
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
      <span>
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'footer.website_by_prefix', 'Webseite von'), ENT_QUOTES, 'UTF-8') ?>
        <a class="jason-gradient-link" href="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'footer.website_by_url', 'https://jasonholweg.de'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'footer.website_by_name', 'Jason Holweg'), ENT_QUOTES, 'UTF-8') ?></a>
      </span>
    </div>
  </div><!-- /.container -->
</footer>

<script>
window.VISITFY_SCRIPT_CONFIG = <?= json_encode($scriptConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}' ?>;
</script>

<?php if ($buttonFxEnabled && $buttonFxSelectors): ?>
<style>
  :root {
    --visitfy-btn-fx-color: <?= htmlspecialchars($buttonFxColor, ENT_QUOTES, 'UTF-8') ?>;
  }

  <?= implode(",\n  ", $buttonFxSelectors) ?> {
    background: var(--visitfy-btn-fx-color) !important;
    color: #061423 !important;
    border: 1.5px solid rgba(255,255,255,0.78) !important;
    box-shadow: 0 10px 24px rgba(142, 201, 255, 0.28) !important;
    position: relative;
    overflow: hidden;
  }

  <?= implode(",\n  ", array_map(static fn($s) => $s . ':hover', $buttonFxSelectors)) ?> {
    background: var(--visitfy-btn-fx-color) !important;
    color: #061423 !important;
    border-color: rgba(255,255,255,0.95) !important;
    box-shadow: 0 14px 32px rgba(142, 201, 255, 0.36) !important;
    transform: translateY(-2px);
  }

<?php if ($buttonFxShimmer): ?>
  @keyframes visitfyBtnShimmerSweep {
    0% { transform: translateX(-140%) skewX(-22deg); opacity: 0; }
    12% { opacity: 0.22; }
    40% { opacity: 0.78; }
    68% { opacity: 0.22; }
    100% { transform: translateX(230%) skewX(-22deg); opacity: 0; }
  }

  <?= implode(",\n  ", array_map(static fn($s) => $s . '::after', $buttonFxSelectors)) ?> {
    content: '';
    position: absolute;
    inset: -14% auto -14% -55%;
    width: 45%;
    pointer-events: none;
    background: linear-gradient(100deg,
      rgba(255,255,255,0) 0%,
      rgba(255,255,255,0.18) 32%,
      rgba(255,255,255,0.72) 48%,
      rgba(255,255,255,0.18) 68%,
      rgba(255,255,255,0) 100%);
    animation: visitfyBtnShimmerSweep 2.5s ease-in-out infinite;
  }
<?php endif; ?>
</style>
<?php endif; ?>

<script src="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/js/main.js"></script>
<script src="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/js/intro.js"></script>
</body>
</html>
