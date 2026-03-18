<?php
/**
 * Visitfy Admin – Tour Card Management
 */
require dirname(__DIR__) . '/bootstrap.php';
admin_require_login();

$notice = '';
$error = '';
$csrf = admin_csrf_token();

function he(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$toursPath = admin_root_path() . '/assets/data/tours.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_validate_csrf($_POST['csrf'] ?? null)) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_tours') {
        $raw = (string)($_POST['tours_json'] ?? '');
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $error = 'Ungültige Tour-Daten (kein gültiges JSON).';
        } else {
            $clean = [];
            foreach ($decoded as $i => $t) {
                if (!is_array($t)) continue;
                $title = trim((string)($t['title'] ?? ''));
                if ($title === '') continue;
                $url = trim((string)($t['matterportUrl'] ?? ''));
                if ($url !== '' && !preg_match('#^https?://#', $url)) $url = '';
                $clean[] = [
                    'id'            => $i + 1,
                    'title'         => $title,
                    'tag'           => trim((string)($t['tag'] ?? '')),
                    'description'   => trim((string)($t['description'] ?? '')),
                    'matterportUrl' => $url,
                ];
            }
            $encoded = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            if (file_put_contents($toursPath, $encoded . PHP_EOL, LOCK_EX) !== false) {
                $notice = count($clean) . ' Rundgang-Karte(n) gespeichert.';
            } else {
                $error = 'Datei konnte nicht gespeichert werden.';
            }
        }
    }
}

$tours = admin_read_json($toursPath, []);
if (!is_array($tours)) $tours = [];

ob_start();
?>
<div class="topbar">
  <div class="topbar-title">Rundgänge</div>
  <div class="topbar-actions">
    <span style="font-size:12px;color:var(--text-2)"><?= count($tours) ?> Einträge</span>
    <button type="submit" form="tours-form" class="btn btn-primary btn-sm">Speichern</button>
  </div>
</div>

<div class="page-body has-action-bar">

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Rundgang-Karten</div>
        <div class="card-desc" style="margin-bottom:0">
          Matterport-Touren verwalten. Drag &amp; Drop zum Umsortieren.
          Matterport-URLs haben das Format: <code style="font-family:monospace;font-size:11px;color:var(--text-2)">https://my.matterport.com/show/?m=...</code>
        </div>
      </div>
      <button type="button" class="btn btn-secondary btn-sm" id="add-tour">
        + Tour hinzufügen
      </button>
    </div>

    <div id="tours-list">
      <?php if (empty($tours)): ?>
      <div id="tours-empty" style="text-align:center;padding:40px;color:var(--text-2)">
        <p>Noch keine Rundgänge angelegt.</p>
      </div>
      <?php else: ?>
      <?php foreach ($tours as $i => $tour): ?>
      <div class="r-item" draggable="true" style="margin-bottom:8px">
        <div class="r-item-head">
          <span class="r-handle">⠿</span>
          <span class="r-label"><?= he($tour['title'] ?? 'Tour ' . ($i + 1)) ?></span>
          <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
        </div>
        <div class="r-item-body">
          <div class="form-row">
            <div class="field">
              <label>Titel <span style="color:var(--red)">*</span></label>
              <input type="text" class="tour-title" value="<?= he($tour['title'] ?? '') ?>" placeholder="z.B. Hotel Zur Post" required>
            </div>
            <div class="field">
              <label>Tag / Kategorie</label>
              <input type="text" class="tour-tag" value="<?= he($tour['tag'] ?? '') ?>" placeholder="z.B. Hotel">
            </div>
          </div>
          <div class="field mt-8">
            <label>Beschreibung</label>
            <textarea class="tour-desc" rows="2"><?= he($tour['description'] ?? '') ?></textarea>
          </div>
          <div class="field mt-8">
            <label>Matterport URL</label>
            <input type="url" class="tour-url" value="<?= he($tour['matterportUrl'] ?? '') ?>" placeholder="https://my.matterport.com/show/?m=...">
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Hidden form for submission -->
<form id="tours-form" method="post" action="?p=tours" style="display:none">
  <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
  <input type="hidden" name="action" value="save_tours">
  <input type="hidden" name="tours_json" id="tours-json">
</form>

<script>
(function() {
  var list = document.getElementById('tours-list');
  var addBtn = document.getElementById('add-tour');
  var form = document.getElementById('tours-form');
  var jsonInput = document.getElementById('tours-json');

  // ── Add new tour ───────────────────────────────────────────────
  if (addBtn) {
    addBtn.addEventListener('click', function() {
      var emptyNotice = document.getElementById('tours-empty');
      if (emptyNotice) emptyNotice.remove();

      var count = list.querySelectorAll('.r-item').length + 1;
      var item = document.createElement('div');
      item.className = 'r-item';
      item.setAttribute('draggable', 'true');
      item.style.marginBottom = '8px';
      item.innerHTML = `
        <div class="r-item-head">
          <span class="r-handle">⠿</span>
          <span class="r-label">Tour ${count}</span>
          <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
        </div>
        <div class="r-item-body">
          <div class="form-row">
            <div class="field">
              <label>Titel <span style="color:var(--red)">*</span></label>
              <input type="text" class="tour-title" placeholder="z.B. Hotel Zur Post" required>
            </div>
            <div class="field">
              <label>Tag / Kategorie</label>
              <input type="text" class="tour-tag" placeholder="z.B. Hotel">
            </div>
          </div>
          <div class="field mt-8">
            <label>Beschreibung</label>
            <textarea class="tour-desc" rows="2"></textarea>
          </div>
          <div class="field mt-8">
            <label>Matterport URL</label>
            <input type="url" class="tour-url" placeholder="https://my.matterport.com/show/?m=...">
          </div>
        </div>`;
      list.appendChild(item);
      setupDrag(item);
      updateLabels();
      var first = item.querySelector('.tour-title');
      if (first) first.focus();
    });
  }

  // ── Remove ─────────────────────────────────────────────────────
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.r-remove');
    if (!btn) return;
    var item = btn.closest('.r-item');
    if (item && item.closest('#tours-list')) {
      item.remove();
      updateLabels();
    }
  });

  // ── Labels ─────────────────────────────────────────────────────
  function updateLabels() {
    list.querySelectorAll('.r-item').forEach(function(item, i) {
      var lbl = item.querySelector('.r-label');
      var title = (item.querySelector('.tour-title') || {}).value;
      if (lbl) lbl.textContent = title || ('Tour ' + (i + 1));
    });
  }

  // Listen for title changes to update labels
  list.addEventListener('input', function(e) {
    if (e.target.classList.contains('tour-title')) updateLabels();
  });

  // ── Drag & Drop ─────────────────────────────────────────────────
  var dragSrc = null;

  function setupDrag(item) {
    item.addEventListener('dragstart', function(e) {
      dragSrc = item;
      item.classList.add('is-ghost');
      e.dataTransfer.effectAllowed = 'move';
    });
    item.addEventListener('dragend', function() {
      item.classList.remove('is-ghost');
      list.querySelectorAll('.r-item.is-target').forEach(function(t) { t.classList.remove('is-target'); });
      dragSrc = null;
    });
    item.addEventListener('dragover', function(e) {
      e.preventDefault();
      if (dragSrc && dragSrc !== item) item.classList.add('is-target');
    });
    item.addEventListener('dragleave', function() { item.classList.remove('is-target'); });
    item.addEventListener('drop', function(e) {
      e.preventDefault();
      item.classList.remove('is-target');
      if (!dragSrc || dragSrc === item) return;
      var items = Array.from(list.querySelectorAll(':scope > .r-item'));
      var si = items.indexOf(dragSrc);
      var ti = items.indexOf(item);
      if (si < ti) list.insertBefore(dragSrc, item.nextSibling);
      else list.insertBefore(dragSrc, item);
      updateLabels();
    });

    // Toggle body on head click
    var head = item.querySelector('.r-item-head');
    if (head) {
      head.addEventListener('click', function(e) {
        if (e.target.closest('.r-remove') || e.target.closest('.r-handle')) return;
        var body = item.querySelector('.r-item-body');
        if (body) body.classList.toggle('collapsed');
      });
    }
  }

  list.querySelectorAll('.r-item').forEach(setupDrag);

  // ── Serialize on submit ─────────────────────────────────────────
  if (form) {
    form.addEventListener('submit', function(e) {
      var items = list.querySelectorAll('.r-item');
      var data = [];
      items.forEach(function(item, i) {
        data.push({
          id: i + 1,
          title: (item.querySelector('.tour-title') || {}).value || '',
          tag: (item.querySelector('.tour-tag') || {}).value || '',
          description: (item.querySelector('.tour-desc') || {}).value || '',
          matterportUrl: (item.querySelector('.tour-url') || {}).value || ''
        });
      });
      jsonInput.value = JSON.stringify(data);
    });
  }
})();
</script>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'Rundgänge';
$currentPage = 'tours';
$showActionBar = true;
$actionBarFormId = 'tours-form';
include dirname(__DIR__) . '/partial/layout.php';
