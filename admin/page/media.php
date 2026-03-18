<?php
/**
 * Visitfy Admin – Media / Image Management
 */
require dirname(__DIR__) . '/bootstrap.php';
admin_require_login();

$notice = '';
$error = '';
$csrf = admin_csrf_token();

function he(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_validate_csrf($_POST['csrf'] ?? null)) {
    $action = (string)($_POST['action'] ?? '');

    // ── AJAX upload ──────────────────────────────────────────────
    if ($action === 'upload_image') {
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        $folders = admin_upload_folders();
        $targetFolder = admin_safe_relative_path((string)($_POST['target_folder'] ?? ''));
        $uploadError = '';
        $uploadedName = '';

        if (!in_array($targetFolder, array_values($folders), true)) {
            $uploadError = 'Ungültiger Upload-Ordner.';
        } elseif (!isset($_FILES['image_file']) || !is_array($_FILES['image_file'])) {
            $uploadError = 'Keine Datei hochgeladen.';
        } else {
            $file = $_FILES['image_file'];
            $tmp = (string)($file['tmp_name'] ?? '');
            $name = (string)($file['name'] ?? '');
            $errCode = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($errCode !== UPLOAD_ERR_OK || $tmp === '' || $name === '') {
                $uploadError = 'Upload fehlgeschlagen (Code ' . $errCode . ').';
            } else {
                $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $name) ?? '';
                $cleanName = trim($cleanName, '-');
                $ext = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION));
                $allowedExt = ['png', 'jpg', 'jpeg', 'webp', 'avif', 'svg'];

                if ($cleanName === '' || !in_array($ext, $allowedExt, true)) {
                    $uploadError = 'Dateityp nicht erlaubt: ' . $ext;
                } else {
                    $folderAbs = admin_absolute_path($targetFolder);
                    if (!is_dir($folderAbs)) @mkdir($folderAbs, 0775, true);
                    $destAbs = $folderAbs . '/' . $cleanName;
                    $i = 1;
                    while (file_exists($destAbs)) {
                        $base = pathinfo($cleanName, PATHINFO_FILENAME);
                        $destAbs = $folderAbs . '/' . $base . '-' . $i . '.' . $ext;
                        $i++;
                    }
                    if (!move_uploaded_file($tmp, $destAbs)) {
                        $uploadError = 'Datei konnte nicht gespeichert werden.';
                    } else {
                        $uploadedName = basename($destAbs);
                    }
                }
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            if ($uploadError !== '') {
                echo json_encode(['ok' => false, 'error' => $uploadError]);
            } else {
                echo json_encode(['ok' => true, 'filename' => $uploadedName, 'folder' => $targetFolder]);
            }
            exit;
        }

        if ($uploadError !== '') {
            $error = $uploadError;
        } else {
            $notice = 'Bild hochgeladen: ' . $uploadedName;
        }
    }

    // ── Delete image ─────────────────────────────────────────────
    if ($action === 'delete_image') {
        $folders = admin_upload_folders();
        $targetFolder = admin_safe_relative_path((string)($_POST['target_folder'] ?? ''));
        $imageName = (string)($_POST['image_name'] ?? '');

        if (!in_array($targetFolder, array_values($folders), true)) {
            $error = 'Ungültiger Zielordner.';
        } elseif ($imageName === '' || basename($imageName) !== $imageName) {
            $error = 'Ungültiger Dateiname.';
        } else {
            $filePath = admin_absolute_path($targetFolder . '/' . $imageName);
            if (!is_file($filePath)) {
                $error = 'Datei nicht gefunden.';
            } elseif (!@unlink($filePath)) {
                $error = 'Datei konnte nicht gelöscht werden.';
            } else {
                $notice = 'Bild gelöscht: ' . $imageName;
            }
        }
    }

    // ── Rename image ─────────────────────────────────────────────
    if ($action === 'rename_image') {
        $folders = admin_upload_folders();
        $targetFolder = admin_safe_relative_path((string)($_POST['target_folder'] ?? ''));
        $oldName = (string)($_POST['old_name'] ?? '');
        $newNameRaw = (string)($_POST['new_name'] ?? '');

        if (!in_array($targetFolder, array_values($folders), true)) {
            $error = 'Ungültiger Zielordner.';
        } elseif ($oldName === '' || basename($oldName) !== $oldName) {
            $error = 'Ungültiger alter Dateiname.';
        } else {
            $newName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $newNameRaw) ?? '';
            $newName = trim($newName, '-');
            $oldExt = strtolower(pathinfo($oldName, PATHINFO_EXTENSION));
            $newExt = strtolower(pathinfo($newName, PATHINFO_EXTENSION));
            $allowedExt = ['png', 'jpg', 'jpeg', 'webp', 'avif', 'svg'];

            if ($newName === '' || basename($newName) !== $newName) {
                $error = 'Ungültiger neuer Dateiname.';
            } elseif (!in_array($oldExt, $allowedExt, true) || !in_array($newExt, $allowedExt, true)) {
                $error = 'Dateityp nicht erlaubt.';
            } else {
                $oldPath = admin_absolute_path($targetFolder . '/' . $oldName);
                $newPath = admin_absolute_path($targetFolder . '/' . $newName);
                if (!is_file($oldPath)) {
                    $error = 'Originaldatei nicht gefunden.';
                } elseif ($oldName !== $newName && file_exists($newPath)) {
                    $error = 'Dateiname bereits vergeben.';
                } elseif ($oldName === $newName) {
                    $notice = 'Dateiname unverändert.';
                } elseif (!@rename($oldPath, $newPath)) {
                    $error = 'Umbenennen fehlgeschlagen.';
                } else {
                    $notice = 'Umbenannt in: ' . $newName;
                }
            }
        }
    }
}

// Collect all images across folders
$folders = admin_upload_folders();
$activeFolder = admin_safe_relative_path((string)($_GET['folder'] ?? ''));
if (!in_array($activeFolder, array_values($folders), true)) {
    $activeFolder = '';
}

$allImages = [];
foreach ($folders as $folderLabel => $folderPath) {
    $abs = admin_absolute_path($folderPath);
    if (!is_dir($abs)) continue;
    $files = glob($abs . '/*.{png,jpg,jpeg,webp,avif,svg}', GLOB_BRACE);
    if (!is_array($files)) continue;
    foreach ($files as $file) {
        $allImages[] = [
            'name' => basename($file),
            'folder' => $folderPath,
            'folder_label' => $folderLabel,
            'size' => filesize($file),
            'mtime' => filemtime($file),
        ];
    }
}

// Sort by mtime desc
usort($allImages, fn($a, $b) => $b['mtime'] - $a['mtime']);

// Filter by folder
$displayImages = $activeFolder
    ? array_values(array_filter($allImages, fn($img) => $img['folder'] === $activeFolder))
    : $allImages;

function fmt_size(int $bytes): string {
    if ($bytes >= 1024 * 1024) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

// Determine default upload folder
$defaultUploadFolder = reset($folders) ?: 'assets/img';

ob_start();
?>
<div class="topbar">
  <div class="topbar-title">Bilder</div>
  <div class="topbar-actions">
    <span style="font-size:12px;color:var(--text-2)"><?= count($displayImages) ?> Bilder</span>
  </div>
</div>

<div class="page-body">

  <!-- Upload Zone -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Bilder hochladen</div>
        <div class="card-desc" style="margin-bottom:0">Ziehe Bilder hierher oder klicke zum Auswählen. PNG, JPG, WebP, AVIF, SVG erlaubt.</div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <label style="font-size:11px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.05em">Ordner</label>
        <select id="upload-folder-select" style="width:220px">
          <?php foreach ($folders as $lbl => $path): ?>
          <option value="<?= he($path) ?>"><?= he($lbl) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="upload-zone" id="upload-zone">
      <div class="upload-zone-icon">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M24 32V16M16 24l8-8 8 8"/>
          <path d="M8 36a8 8 0 0 1 0-16h2a12 12 0 1 1 24 0h2a8 8 0 0 1 0 16"/>
        </svg>
      </div>
      <p><strong>Dateien hierher ziehen</strong> oder klicken zum Auswählen</p>
      <small>PNG, JPG, WebP, AVIF, SVG · Mehrere Dateien möglich</small>
    </div>
    <input type="file" id="upload-input" multiple accept="image/*" style="display:none">
    <div id="upload-queue" class="upload-queue" style="display:none"></div>
    <div class="progress-bar" id="upload-progress-wrap" style="display:none">
      <div class="progress-bar-fill" id="upload-progress" style="width:0"></div>
    </div>
  </div>

  <!-- Folder Filter -->
  <div class="filter-tabs mt-16">
    <a href="?p=media" class="filter-tab <?= $activeFolder === '' ? 'active' : '' ?>">
      Alle (<?= count($allImages) ?>)
    </a>
    <?php foreach ($folders as $lbl => $folderPath): ?>
    <?php $cnt = count(array_filter($allImages, fn($img) => $img['folder'] === $folderPath)); ?>
    <a href="?p=media&folder=<?= he(urlencode($folderPath)) ?>" class="filter-tab <?= $activeFolder === $folderPath ? 'active' : '' ?>">
      <?= he(explode(' (', $lbl)[0]) ?> (<?= $cnt ?>)
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Image Grid -->
  <?php if (empty($displayImages)): ?>
  <div style="text-align:center;padding:60px 20px;color:var(--text-2)">
    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;opacity:0.3">
      <rect x="4" y="10" width="40" height="28" rx="4"/>
      <circle cx="16" cy="20" r="4"/>
      <path d="M4 30l10-10 10 10 6-6 14 14"/>
    </svg>
    <p>Noch keine Bilder in diesem Ordner.</p>
  </div>
  <?php else: ?>
  <div class="media-grid">
    <?php foreach ($displayImages as $img): ?>
    <?php
    $imgPath = $img['folder'] . '/' . $img['name'];
    $webPath = '../' . $imgPath;
    $shortFolder = explode(' (', $img['folder_label'])[0];
    ?>
    <div class="media-item" id="media-<?= he(md5($imgPath)) ?>">
      <div class="media-item-thumb">
        <?php $isSvg = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION)) === 'svg'; ?>
        <img src="<?= he($webPath) ?>" alt="<?= he($img['name']) ?>" loading="lazy"
          style="<?= $isSvg ? 'padding:12px;object-fit:contain;background:#161616' : '' ?>">
      </div>
      <div class="media-item-info">
        <div class="media-item-name" title="<?= he($img['name']) ?>"><?= he($img['name']) ?></div>
        <div class="media-item-folder"><?= he($shortFolder) ?> · <?= he(fmt_size($img['size'])) ?></div>
      </div>
      <div class="media-item-actions">
        <button type="button" class="btn btn-secondary btn-xs" style="flex:1"
          onclick="startRename('<?= he(md5($imgPath)) ?>')">
          Umbenennen
        </button>
        <form method="post" action="?p=media" style="margin:0" onsubmit="return confirm('Bild löschen?')">
          <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
          <input type="hidden" name="action" value="delete_image">
          <input type="hidden" name="target_folder" value="<?= he($img['folder']) ?>">
          <input type="hidden" name="image_name" value="<?= he($img['name']) ?>">
          <button type="submit" class="btn btn-danger btn-xs">✕</button>
        </form>
      </div>
      <!-- Rename inline form (hidden by default) -->
      <div id="rename-form-<?= he(md5($imgPath)) ?>" style="display:none;padding:0 10px 10px">
        <form method="post" action="?p=media" class="rename-form">
          <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
          <input type="hidden" name="action" value="rename_image">
          <input type="hidden" name="target_folder" value="<?= he($img['folder']) ?>">
          <input type="hidden" name="old_name" value="<?= he($img['name']) ?>">
          <input type="text" name="new_name" value="<?= he($img['name']) ?>" style="font-size:11px">
          <button type="submit" class="btn btn-primary btn-xs">OK</button>
          <button type="button" class="btn btn-secondary btn-xs" onclick="cancelRename('<?= he(md5($imgPath)) ?>')">✕</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<script>
// ── Upload Zone ────────────────────────────────────────────────────
(function() {
  var zone = document.getElementById('upload-zone');
  var inp = document.getElementById('upload-input');
  var queue = document.getElementById('upload-queue');
  var progressWrap = document.getElementById('upload-progress-wrap');
  var progressBar = document.getElementById('upload-progress');
  var folderSelect = document.getElementById('upload-folder-select');

  if (!zone || !inp) return;

  zone.addEventListener('click', function() { inp.click(); });

  zone.addEventListener('dragover', function(e) {
    e.preventDefault();
    zone.classList.add('drag-over');
  });
  zone.addEventListener('dragleave', function() { zone.classList.remove('drag-over'); });
  zone.addEventListener('drop', function(e) {
    e.preventDefault();
    zone.classList.remove('drag-over');
    if (e.dataTransfer && e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files);
  });

  inp.addEventListener('change', function() {
    if (inp.files.length) uploadFiles(inp.files);
    inp.value = '';
  });

  function uploadFiles(files) {
    queue.style.display = 'flex';
    progressWrap.style.display = 'block';
    var arr = Array.from(files);
    var done = 0;

    arr.forEach(function(file) {
      var item = document.createElement('div');
      item.className = 'upload-item';
      item.innerHTML = '<span class="ui-name">' + escHtml(file.name) + '</span>'
        + '<span class="ui-size">' + fmtSize(file.size) + '</span>'
        + '<span class="ui-status waiting">Wartend</span>';
      queue.appendChild(item);
      var status = item.querySelector('.ui-status');

      var fd = new FormData();
      fd.append('csrf', <?= json_encode($csrf) ?>);
      fd.append('action', 'upload_image');
      fd.append('target_folder', folderSelect.value);
      fd.append('image_file', file);

      status.textContent = 'Uploading…';
      status.className = 'ui-status uploading';

      fetch('?p=media', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        done++;
        progressBar.style.width = Math.round(done / arr.length * 100) + '%';
        if (data.ok) {
          status.textContent = '✓ Fertig';
          status.className = 'ui-status done';
        } else {
          status.textContent = '✗ ' + (data.error || 'Fehler');
          status.className = 'ui-status error';
        }
        if (done === arr.length) {
          setTimeout(function() { location.reload(); }, 1200);
        }
      })
      .catch(function() {
        done++;
        status.textContent = '✗ Netzwerkfehler';
        status.className = 'ui-status error';
        progressBar.style.width = Math.round(done / arr.length * 100) + '%';
      });
    });
  }

  function fmtSize(bytes) {
    if (bytes >= 1048576) return (bytes/1048576).toFixed(1) + ' MB';
    if (bytes >= 1024) return (bytes/1024).toFixed(1) + ' KB';
    return bytes + ' B';
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
})();

// ── Rename helpers ─────────────────────────────────────────────────
function startRename(hash) {
  var formDiv = document.getElementById('rename-form-' + hash);
  if (!formDiv) return;
  formDiv.style.display = 'block';
  var input = formDiv.querySelector('input[name="new_name"]');
  if (input) { input.focus(); input.select(); }
}

function cancelRename(hash) {
  var formDiv = document.getElementById('rename-form-' + hash);
  if (formDiv) formDiv.style.display = 'none';
}
</script>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'Bilder';
$currentPage = 'media';
include dirname(__DIR__) . '/partial/layout.php';
