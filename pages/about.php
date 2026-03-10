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
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.paragraph_1', ''), ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.paragraph_2', ''), ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.paragraph_3', ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="fade-up delay-2">
          <div class="glass about-panel">
            <h3 class="about-difference-title">
<?php foreach (visitfy_split_lines((string)visitfy_get($contentConfig, 'about.difference_title', 'Der Visitfy-Unterschied:\nErleben statt nur sehen.')) as $line): ?>
              <?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') ?><br>
<?php endforeach; ?>
            </h3>

            <div class="about-features">
              <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.features_title', 'Was uns auszeichnet'), ENT_QUOTES, 'UTF-8') ?></h3>
              <ul>
<?php foreach ($aboutFeatures as $item): ?>
                <li><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?></li>
<?php endforeach; ?>
              </ul>
            </div>
          </div>

          <div class="glass about-panel">
            <div class="about-features" style="margin-top:0;">
              <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'about.perfection_title', 'Perfektion in jedem Detail'), ENT_QUOTES, 'UTF-8') ?></h3>
              <ul>
<?php foreach ($aboutPerfection as $item): ?>
                <li><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?></li>
<?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" aria-labelledby="about-team-heading">
    <div class="container">
      <p class="section-eyebrow fade-up">TEAM</p>
      <h2 class="section-title fade-up delay-1" id="about-team-heading">Menschen hinter Visitfy</h2>

      <div class="about-team-grid">
        <article class="about-team-card glass fade-up delay-1">
          <p class="about-team-role"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.kristian_role', 'Geschäftsführer'), ENT_QUOTES, 'UTF-8') ?></p>
          <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.kristian_name', 'Kristian Meister'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'team.kristian_text', ''), ENT_QUOTES, 'UTF-8') ?></p>
        </article>

        <article class="about-team-card glass fade-up delay-2">
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
