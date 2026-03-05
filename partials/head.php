<?php
/**
 * Visitfy3 – partials/head.php
 * Outputs <head> content.
 * Variables expected (with defaults):
 *   $pageTitle   string
 *   $pageDesc    string
 *   $pageOgImg   string (optional)
 *   $root        string (path prefix, e.g. '' or '../')
 */
if (!isset($root))      $root      = '';
if (!isset($pageTitle)) $pageTitle = 'Visitfy | 360° Rundgänge für moderne Unternehmen';
if (!isset($pageDesc))  $pageDesc  = 'Visitfy entwickelt professionelle 360° virtuelle Rundgänge für Unternehmen jeder Branche – realistisch, hochwertig und sofort einsatzbereit.';
if (!isset($pageOgImg)) $pageOgImg = '';
$canonicalUrl = 'https://visitfy.de/'; // TODO: set dynamically per deployment
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="robots" content="index, follow">

  <!-- Open Graph -->
  <meta property="og:type"        content="website">
  <meta property="og:title"       content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:site_name"   content="Visitfy">
<?php if ($pageOgImg): ?>
  <meta property="og:image"       content="<?= htmlspecialchars($pageOgImg, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">

  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

  <!-- Favicon -->
  <link rel="icon" href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/img/logo.svg" type="image/svg+xml">

  <!-- Stylesheet -->
  <link rel="stylesheet" href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>assets/css/style.css">
</head>
