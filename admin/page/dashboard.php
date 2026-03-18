<?php
/**
 * Visitfy Admin – Dashboard
 */
require_once dirname(__DIR__) . '/bootstrap.php';
admin_require_login();

$csrf = admin_csrf_token();

// Load data
$content = admin_read_json(admin_content_config_path(), []);
$tours = admin_read_json(admin_root_path() . '/assets/data/tours.json', []);
$mailSettings = admin_read_mail_settings();
$script = admin_read_json(admin_script_config_path(), []);

// Count media files
$mediaCount = 0;
foreach (admin_upload_folders() as $folder) {
    $abs = admin_absolute_path($folder);
    if (is_dir($abs)) {
        $files = glob($abs . '/*.{png,jpg,jpeg,webp,avif,svg}', GLOB_BRACE);
        $mediaCount += is_array($files) ? count($files) : 0;
    }
}

$faqCount = count($content['faq']['items'] ?? []);
$kpiCount = count($content['kpi']['items'] ?? []);
$testimonialCount = count($content['testimonials']['items'] ?? []);
$tourCount = is_array($tours) ? count($tours) : 0;
$caseCount = count($content['cases']['items'] ?? []);

// Check mailgun configured
$mailgunOk = ($mailSettings['MAILGUN_API_KEY'] ?? '') !== '' && ($mailSettings['MAILGUN_DOMAIN'] ?? '') !== '';

// Content modification time
$contentMtime = '';
$contentPath = admin_content_config_path();
if (is_file($contentPath)) {
    $contentMtime = date('d.m.Y H:i', filemtime($contentPath));
}

// Completeness checks
$checks = [
    ['label' => 'Hero Titel', 'ok' => trim(admin_field($content, 'hero.prefix', '')) !== '' || trim(admin_field($content, 'hero.eyebrow', '')) !== ''],
    ['label' => 'KPI Einträge (' . $kpiCount . ')', 'ok' => $kpiCount > 0],
    ['label' => 'Testimonials (' . $testimonialCount . ')', 'ok' => $testimonialCount > 0],
    ['label' => 'FAQ Einträge (' . $faqCount . ')', 'ok' => $faqCount > 0],
    ['label' => 'Rundgänge (' . $tourCount . ')', 'ok' => $tourCount > 0],
    ['label' => 'Footer E-Mail', 'ok' => trim(admin_field($content, 'footer.contact_email', '')) !== ''],
    ['label' => 'SEO Titel', 'ok' => trim(admin_field($content, 'seo.home_title', '')) !== ''],
    ['label' => 'Mailgun konfiguriert', 'ok' => $mailgunOk],
];

ob_start();
?>
<div class="topbar">
  <div class="topbar-title">Dashboard</div>
  <div class="topbar-actions">
    <span style="font-size:12px;color:var(--text-2)"><?= date('d. F Y') ?></span>
    <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
        <path d="M7 3H3a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h9a1 1 0 0 0 1-1V9"/>
        <path d="M10 2h4v4M14 2 8 8"/>
      </svg>
      Vorschau
    </a>
  </div>
</div>

<div class="page-body">

  <!-- Quick Stats -->
  <div class="widget-grid">
    <div class="widget">
      <div class="widget-icon">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="8" cy="8" r="6"/>
          <path d="M6.5 5.5 11 8l-4.5 2.5V5.5z"/>
        </svg>
      </div>
      <div class="widget-value"><?= $tourCount ?></div>
      <div class="widget-label">Rundgänge</div>
    </div>

    <div class="widget">
      <div class="widget-icon">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="3" width="14" height="10" rx="1.5"/>
          <circle cx="5.5" cy="6.5" r="1.5"/>
          <path d="M1 10l3.5-3.5 3 3 2-2 5.5 5.5"/>
        </svg>
      </div>
      <div class="widget-value"><?= $mediaCount ?></div>
      <div class="widget-label">Bilder gesamt</div>
    </div>

    <div class="widget">
      <div class="widget-icon">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 2h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z"/>
          <path d="M5 6h6M5 9h4"/>
        </svg>
      </div>
      <div class="widget-value"><?= $faqCount ?></div>
      <div class="widget-label">FAQ Einträge</div>
    </div>

    <div class="widget">
      <div class="widget-icon">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 10V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/>
          <path d="M1 10h14M5 10l-1 3M11 10l1 3M7 13h2"/>
        </svg>
      </div>
      <div class="widget-value"><?= $kpiCount ?></div>
      <div class="widget-label">KPI Einträge</div>
    </div>
  </div>

  <!-- Bottom grid -->
  <div class="dash-grid">
    <!-- System Status -->
    <div class="card">
      <div class="card-header">
        <div class="card-header-left">
          <div class="card-title">System Status</div>
        </div>
      </div>
      <div class="status-list">
        <div class="status-row">
          <span class="status-row-label">Mailgun</span>
          <?php if ($mailgunOk): ?>
          <span class="badge badge-green">Konfiguriert</span>
          <?php else: ?>
          <span class="badge badge-red">Nicht konfiguriert</span>
          <?php endif; ?>
        </div>
        <div class="status-row">
          <span class="status-row-label">PHP Version</span>
          <span class="badge badge-gray"><?= htmlspecialchars(PHP_VERSION, ENT_QUOTES) ?></span>
        </div>
        <div class="status-row">
          <span class="status-row-label">Content zuletzt geändert</span>
          <span style="font-size:12px;color:var(--text-2)"><?= $contentMtime ?: '—' ?></span>
        </div>
        <div class="status-row">
          <span class="status-row-label">Cases</span>
          <span class="badge badge-gray"><?= $caseCount ?></span>
        </div>
        <div class="status-row">
          <span class="status-row-label">Testimonials</span>
          <span class="badge badge-gray"><?= $testimonialCount ?></span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
      <div class="card-header">
        <div class="card-header-left">
          <div class="card-title">Schnellzugriff</div>
        </div>
      </div>
      <div class="quick-actions">
        <a href="?p=content&s=hero" class="quick-action">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 2h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z"/>
            <path d="M5 6h6M5 9h4"/>
          </svg>
          Inhalte bearbeiten
        </a>
        <a href="?p=tours" class="quick-action">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="8" r="6"/>
            <path d="M6.5 5.5 11 8l-4.5 2.5V5.5z"/>
          </svg>
          Rundgänge verwalten
        </a>
        <a href="?p=media" class="quick-action">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="3" width="14" height="10" rx="1.5"/>
            <circle cx="5.5" cy="6.5" r="1.5"/>
            <path d="M1 10l3.5-3.5 3 3 2-2 5.5 5.5"/>
          </svg>
          Bilder hochladen
        </a>
        <a href="?p=integrations" class="quick-action">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 6 6 10"/>
            <path d="M7.5 3.5 12.5 8.5a2.12 2.12 0 0 1 0 3L11 13a2.12 2.12 0 0 1-3 0L3 8a2.12 2.12 0 0 1 0-3L4.5 3.5a2.12 2.12 0 0 1 3 0z"/>
          </svg>
          Mailgun prüfen
        </a>
      </div>
    </div>

    <!-- Content Vollständigkeit -->
    <div class="card">
      <div class="card-header">
        <div class="card-header-left">
          <div class="card-title">Content Vollständigkeit</div>
          <div class="card-desc" style="margin-bottom:0">Überprüfung wichtiger Inhaltsbereiche</div>
        </div>
        <?php $doneCount = count(array_filter($checks, fn($c) => $c['ok'])); ?>
        <span class="badge <?= $doneCount === count($checks) ? 'badge-green' : 'badge-yellow' ?>"><?= $doneCount ?>/<?= count($checks) ?></span>
      </div>
      <div class="checklist">
        <?php foreach ($checks as $check): ?>
        <div class="check-item">
          <span class="check-item-label"><?= htmlspecialchars($check['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php if ($check['ok']): ?>
          <span class="check-ok">✓ OK</span>
          <?php else: ?>
          <span class="check-fail">✗ Fehlt</span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Info -->
    <div class="card">
      <div class="card-header">
        <div class="card-header-left">
          <div class="card-title">Übersicht</div>
        </div>
      </div>
      <div class="status-list">
        <div class="status-row">
          <span class="status-row-label">Warum 360° Karten</span>
          <span class="badge badge-gray"><?= count($content['warum360']['cards'] ?? []) ?></span>
        </div>
        <div class="status-row">
          <span class="status-row-label">Ablauf Schritte</span>
          <span class="badge badge-gray"><?= count($content['ablauf']['items'] ?? []) ?></span>
        </div>
        <div class="status-row">
          <span class="status-row-label">Vergleich Negativ</span>
          <span class="badge badge-gray"><?= count($content['vergleich']['negative_items'] ?? []) ?></span>
        </div>
        <div class="status-row">
          <span class="status-row-label">Vergleich Positiv</span>
          <span class="badge badge-gray"><?= count($content['vergleich']['positive_items'] ?? []) ?></span>
        </div>
        <div class="status-row">
          <span class="status-row-label">Button FX aktiv</span>
          <?php $bfxOn = !empty($content['button_fx']['enabled']); ?>
          <span class="badge <?= $bfxOn ? 'badge-green' : 'badge-gray' ?>"><?= $bfxOn ? 'Ja' : 'Nein' ?></span>
        </div>
      </div>
    </div>
  </div>

</div>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
include dirname(__DIR__) . '/partial/layout.php';
