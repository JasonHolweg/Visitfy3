<?php
/**
 * Visitfy Admin – Anfragen (Contact Inquiries)
 */
require_once dirname(__DIR__) . '/bootstrap.php';
admin_require_login();

$csrf = admin_csrf_token();
$inquiriesPath = admin_root_path() . '/assets/data/inquiries.json';
$notice = '';
$error = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'delete') {
    if (!admin_validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Ungültige Anfrage.';
    } else {
        $deleteId = (int)($_POST['id'] ?? 0);
        $inquiries = admin_read_json($inquiriesPath, []);
        $inquiries = array_values(array_filter($inquiries, static fn($inq) => (int)($inq['id'] ?? 0) !== $deleteId));
        if (admin_write_json($inquiriesPath, $inquiries)) {
            $notice = 'Anfrage gelöscht.';
        } else {
            $error = 'Löschen fehlgeschlagen.';
        }
    }
}

// Handle delete all
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'delete_all') {
    if (!admin_validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Ungültige Anfrage.';
    } else {
        if (admin_write_json($inquiriesPath, [])) {
            $notice = 'Alle Anfragen gelöscht.';
        } else {
            $error = 'Löschen fehlgeschlagen.';
        }
    }
}

$inquiries = admin_read_json($inquiriesPath, []);
// Newest first
usort($inquiries, static fn($a, $b) => strcmp((string)($b['timestamp'] ?? ''), (string)($a['timestamp'] ?? '')));

$brancheLabels = [
    'gastronomie'  => 'Gastronomie',
    'hotel'        => 'Hotel & Wellness',
    'immobilien'   => 'Immobilien',
    'einzelhandel' => 'Einzelhandel & Showroom',
    'praxis'       => 'Praxis & Medizin',
    'fitness'      => 'Fitness & Sport',
    'sonstiges'    => 'Sonstiges',
];

ob_start();
?>
<div class="topbar">
  <div class="topbar-title">
    Anfragen
    <?php if (count($inquiries) > 0): ?>
    <span style="margin-left:8px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;color:var(--text-2)"><?= count($inquiries) ?></span>
    <?php endif; ?>
  </div>
  <?php if (count($inquiries) > 0): ?>
  <div class="topbar-actions">
    <form method="post" style="display:inline" onsubmit="return confirm('Wirklich alle <?= count($inquiries) ?> Anfragen löschen?')">
      <input type="hidden" name="action" value="delete_all">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--red)">Alle löschen</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<div class="page-body">

<?php if ($notice): ?>
<div class="toast toast-ok" style="margin-bottom:20px"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="toast toast-err" style="margin-bottom:20px"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (empty($inquiries)): ?>
  <div class="card" style="text-align:center;padding:60px 24px">
    <svg width="40" height="40" viewBox="0 0 16 16" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 16px;display:block">
      <path d="M2 3h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/>
      <path d="M1 4l7 5 7-5"/>
    </svg>
    <div style="color:var(--text-2);font-size:14px">Noch keine gespeicherten Anfragen.</div>
    <div style="color:var(--text-3);font-size:12px;margin-top:6px">Anfragen werden nur gespeichert, wenn der Nutzer dem Cookie-Consent zugestimmt hat.</div>
  </div>
<?php else: ?>
  <div style="display:flex;flex-direction:column;gap:12px">
  <?php foreach ($inquiries as $inq): ?>
    <?php
    $id      = (int)($inq['id'] ?? 0);
    $date    = htmlspecialchars((string)($inq['date'] ?? '—'), ENT_QUOTES, 'UTF-8');
    $name    = htmlspecialchars((string)($inq['name'] ?? '—'), ENT_QUOTES, 'UTF-8');
    $firma   = htmlspecialchars((string)($inq['firma'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email   = htmlspecialchars((string)($inq['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $tel     = htmlspecialchars((string)($inq['telefon'] ?? ''), ENT_QUOTES, 'UTF-8');
    $branche = htmlspecialchars($brancheLabels[(string)($inq['branche'] ?? '')] ?? (string)($inq['branche'] ?? ''), ENT_QUOTES, 'UTF-8');
    $msg     = nl2br(htmlspecialchars((string)($inq['nachricht'] ?? ''), ENT_QUOTES, 'UTF-8'));
    ?>
    <div class="card">
      <div class="card-header" style="align-items:flex-start">
        <div class="card-header-left" style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <div class="card-title"><?= $name ?></div>
            <?php if ($firma): ?>
            <span class="badge badge-gray"><?= $firma ?></span>
            <?php endif; ?>
            <?php if ($branche): ?>
            <span class="badge badge-gray"><?= $branche ?></span>
            <?php endif; ?>
          </div>
          <div style="font-size:12px;color:var(--text-2);margin-top:4px"><?= $date ?></div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;align-items:center">
          <?php if ($email): ?>
          <a href="mailto:<?= $email ?>" class="btn btn-secondary btn-sm">Antworten</a>
          <?php endif; ?>
          <form method="post" style="display:inline" onsubmit="return confirm('Anfrage von <?= $name ?> löschen?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--red)" title="Anfrage löschen">
              <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 4h12M5 4V2h6v2M6 7v5M10 7v5M3 4l1 10h8l1-10"/>
              </svg>
            </button>
          </form>
        </div>
      </div>

      <div class="status-list" style="margin-top:12px">
        <?php if ($email): ?>
        <div class="status-row">
          <span class="status-row-label">E-Mail</span>
          <a href="mailto:<?= $email ?>" style="font-size:13px;color:var(--text)"><?= $email ?></a>
        </div>
        <?php endif; ?>
        <?php if ($tel): ?>
        <div class="status-row">
          <span class="status-row-label">Telefon</span>
          <span style="font-size:13px;color:var(--text)"><?= $tel ?></span>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($msg): ?>
      <div style="margin-top:14px;padding:14px;background:var(--surface-3);border-radius:8px;font-size:13px;color:var(--text-2);line-height:1.65">
        <?= $msg ?>
      </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>
<?php endif; ?>

</div>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'Anfragen';
$currentPage = 'anfragen';
include dirname(__DIR__) . '/partial/layout.php';
