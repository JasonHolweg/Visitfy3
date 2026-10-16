<?php
/**
 * Visitfy3 – partials/head.php
 * Outputs <head> content.
 * Variables expected (with defaults):
 *   $pageTitle   string
 *   $pageDesc    string
 *   $pageOgImg   string (optional)
 *   $root          string (Pfad-Präfix, i. d. R. visitfy_site_root() = '/')
 *   $canonicalPath string (optional, z. B. visitfy_page_url('faq'))
 *   $noCanonical   bool   (optional, z. B. 404-Seite)
 *   $pageRobots    string (optional)
 *   $jsonLd        array  (optional, Liste von JSON-LD-Objekten)
 */
require_once __DIR__ . '/cms.php';
require_once __DIR__ . '/stats.php';
visitfy_stats_log_bot_hit();

if (!isset($root))      $root      = '';
if (!isset($pageTitle)) $pageTitle = 'Visitfy | 360° Rundgänge für moderne Unternehmen';
if (!isset($pageDesc))  $pageDesc  = 'Visitfy entwickelt professionelle 360° virtuelle Rundgänge für Unternehmen jeder Branche – realistisch, hochwertig und sofort einsatzbereit.';
if (!isset($pageOgImg)) $pageOgImg = '';
if (!isset($pageRobots)) $pageRobots = 'index, follow, max-image-preview:large, max-snippet:-1';
if (!isset($jsonLd))    $jsonLd    = [];

/* Kanonische URL: Seiten können $canonicalPath setzen, sonst aus der Request-URI. */
$siteUrl = VISITFY_SITE_URL;
if (!isset($canonicalPath)) {
    $canonicalPath = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $canonicalPath = preg_replace('#/index\.php$#', '/', $canonicalPath);
    if ($canonicalPath === '') $canonicalPath = '/';
}
$canonicalUrl = $siteUrl . $canonicalPath;
$ogImgAbs = $pageOgImg !== '' ? (preg_match('#^https?://#', $pageOgImg) ? $pageOgImg : $siteUrl . '/' . ltrim($pageOgImg, '/')) : '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="robots" content="<?= htmlspecialchars($pageRobots, ENT_QUOTES, 'UTF-8') ?>">
<?php if (empty($noCanonical)): ?>
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
  <meta name="theme-color" content="#0a0a0a">

  <!-- Open Graph -->
  <meta property="og:type"        content="website">
  <meta property="og:title"       content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:site_name"   content="Visitfy">
  <meta property="og:locale"      content="de_DE">
  <meta property="og:url"         content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
<?php if ($ogImgAbs): ?>
  <meta property="og:image"       content="<?= htmlspecialchars($ogImgAbs, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta name="twitter:image"      content="<?= htmlspecialchars($ogImgAbs, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">

<?php foreach ($jsonLd as $ld): ?>
  <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>

  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

  <!-- Favicon -->
  <link rel="icon" href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/img/logo.svg" type="image/svg+xml">

  <!-- Schrift Inter (400/500/600) lokal – keine Verbindung zu Google (DSGVO, schneller) -->
  <link rel="preload" href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/fonts/inter-latin-400.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/fonts/inter-latin-600.woff2" as="font" type="font/woff2" crossorigin>
  <style>
    @font-face { font-family: 'Inter'; font-style: normal; font-weight: 400; font-display: swap; src: url('<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/fonts/inter-latin-400.woff2') format('woff2'); }
    @font-face { font-family: 'Inter'; font-style: normal; font-weight: 500; font-display: swap; src: url('<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/fonts/inter-latin-500.woff2') format('woff2'); }
    @font-face { font-family: 'Inter'; font-style: normal; font-weight: 600; font-display: swap; src: url('<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/fonts/inter-latin-600.woff2') format('woff2'); }
  </style>

  <!-- Stylesheet -->
  <link rel="stylesheet" href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/css/style.css?v=<?= filemtime(dirname(__DIR__) . '/assets/css/style.css') ?>">
</head>
