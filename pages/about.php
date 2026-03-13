<?php
/**
 * Visitfy3 – pages/about.php
 * About Us page with JSON-driven content.
 */
require __DIR__ . '/../partials/cms.php';
$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? visitfy_base_path() : '../';

$contentConfig = visitfy_load_json(__DIR__ . '/../assets/data/content.json', []);

$pageTitle = 'Über uns | Visitfy – 360° Rundgänge';
$pageDesc  = 'Lerne Visitfy kennen: Mission, Qualitätsanspruch und das Team hinter professionellen 360° Rundgängen.';

$aboutFeatures = visitfy_get($contentConfig, 'about.features', []);
if (!is_array($aboutFeatures)) $aboutFeatures = [];
$aboutPerfection = visitfy_get($contentConfig, 'about.perfection_points', []);
if (!is_array($aboutPerfection)) $aboutPerfection = [];

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<?php
/* Perfection box SVG icons – mapped by index */
$perfectionIcons = [
  '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>', /* Expertise */
  '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2z"/></svg>', /* Qualität */
  '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>', /* Beratung */
  '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', /* Schnell */
];

/* Team photos */
$kristianPhoto = (string)visitfy_get($contentConfig, 'team.kristian_photo', '');
$jasonPhoto    = (string)visitfy_get($contentConfig, 'team.jason_photo', '');
?>

<main id="main-content">

  <section class="page-hero">
    <div class="container">
      <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.eyebrow', 'ÜBER UNS'), ENT_QUOTES, 'UTF-8') ?></p>
      <h1 class="fade-up delay-1"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.title', 'Über Visitfy'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="fade-up delay-2">
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.paragraph_1', 'Visitfy ist ein spezialisierter Anbieter für professionelle 360° virtuelle Rundgänge.'), ENT_QUOTES, 'UTF-8') ?>
      </p>
    </div>
  </section>

  <section class="section" aria-labelledby="about-visitfy-heading">
    <div class="container">
      <h2 class="section-title fade-up" id="about-visitfy-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.title', 'Über Visitfy'), ENT_QUOTES, 'UTF-8') ?></h2>

      <div class="about-grid">
        <div class="about-text fade-up delay-1">
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.paragraph_2', ''), ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.paragraph_3', ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="fade-up delay-2">
          <div class="glass about-panel">
            <p class="about-panel-badge" aria-hidden="true">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              Erleben statt nur sehen
            </p>
            <h3 class="about-difference-title">
<?php foreach (visitfy_split_lines((string)visitfy_get($contentConfig, 'about.difference_title', 'Der Visitfy-Unterschied:\nErleben statt nur sehen.')) as $line): ?>
              <?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') ?><br>
<?php endforeach; ?>
            </h3>

            <div class="about-features">
              <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.features_title', 'Was uns auszeichnet'), ENT_QUOTES, 'UTF-8') ?></h3>
              <ul>
<?php foreach ($aboutFeatures as $item): ?>
                <li class="about-icon-item">
                  <span class="about-item-icon" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
                  <?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?>
                </li>
<?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Perfection: 4 Icon-Boxen -->
  <section class="section" aria-labelledby="about-perfection-heading">
    <div class="container">
      <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.perfection_title', 'Perfektion in jedem Detail'), ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-title fade-up delay-1" id="about-perfection-heading"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.perfection_title', 'Perfektion in jedem Detail'), ENT_QUOTES, 'UTF-8') ?></h2>

      <div class="perfection-grid">
<?php foreach ($aboutPerfection as $i => $item): ?>
        <div class="perfection-box glass fade-up delay-<?= ($i % 4) + 1 ?>">
          <div class="perfection-icon" aria-hidden="true">
            <?= $perfectionIcons[$i] ?? $perfectionIcons[0] ?>
          </div>
          <p class="perfection-text"><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
<?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" aria-labelledby="about-team-heading">
    <div class="container">
      <p class="section-eyebrow fade-up">TEAM</p>
      <h2 class="section-title fade-up delay-1" id="about-team-heading">Menschen hinter Visitfy</h2>

      <div class="about-team-grid">
        <article class="about-team-card glass fade-up delay-1">
<?php if ($kristianPhoto): ?>
          <div class="about-team-photo">
            <img src="<?= htmlspecialchars($root . $kristianPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.kristian_name', 'Kristian Meister'), ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
          </div>
<?php else: ?>
          <div class="about-team-avatar" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
<?php endif; ?>
          <p class="about-team-role"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.kristian_role', 'Geschäftsführer'), ENT_QUOTES, 'UTF-8') ?></p>
          <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.kristian_name', 'Kristian Meister'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.kristian_text', ''), ENT_QUOTES, 'UTF-8') ?></p>
        </article>

        <article class="about-team-card glass fade-up delay-2">
<?php if ($jasonPhoto): ?>
          <div class="about-team-photo">
            <img src="<?= htmlspecialchars($root . $jasonPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.jason_name', 'Jason Holweg'), ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
          </div>
<?php else: ?>
          <div class="about-team-avatar" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
<?php endif; ?>
          <p class="about-team-role"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.jason_role', 'Entwickler'), ENT_QUOTES, 'UTF-8') ?></p>
          <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.jason_name', 'Jason Holweg'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.jason_text', ''), ENT_QUOTES, 'UTF-8') ?></p>
          <p>
            <a class="jason-gradient-link" href="<?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.jason_link_url', 'https://jasonholweg.de'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.jason_link_text', 'jasonholweg.de'), ENT_QUOTES, 'UTF-8') ?></a>
          </p>
        </article>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
