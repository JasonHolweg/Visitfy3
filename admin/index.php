<?php
require __DIR__ . '/bootstrap.php';

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'login') {
    $password = (string)($_POST['password'] ?? '');
    if (admin_login($password)) {
        header('Location: index.php');
        exit;
    }
    $error = 'Login fehlgeschlagen. Bitte Passwort prüfen.';
}

if (!admin_is_logged_in()) {
    $defaultPasswordWarning = admin_password() === 'visitfy-admin';
    ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Visitfy Admin Login</title>
  <style>
    body{margin:0;min-height:100vh;display:grid;place-items:center;background:#050505;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
    .card{width:min(460px,92vw);background:#111;border:1px solid #2a2a2a;border-radius:16px;padding:28px;box-shadow:0 10px 40px rgba(0,0,0,.45)}
    h1{font-size:1.35rem;margin:0 0 8px}
    p{color:#b9b9b9;margin:0 0 16px;line-height:1.6}
    input{width:100%;padding:12px 14px;border-radius:10px;border:1px solid #2f2f2f;background:#0a0a0a;color:#fff}
    button{margin-top:14px;width:100%;padding:12px 14px;border-radius:10px;border:1px solid #3b3b3b;background:#fff;color:#000;font-weight:700;cursor:pointer}
    .msg{font-size:.92rem;margin:10px 0;padding:10px 12px;border-radius:10px}
    .err{background:#2b1111;border:1px solid #663030;color:#ffbcbc}
    .warn{background:#2b2511;border:1px solid #665830;color:#ffe1a3}
  </style>
</head>
<body>
  <form class="card" method="post" action="">
    <h1>Visitfy Admin</h1>
    <p>Einfaches Admin-Panel ohne Codebearbeitung.</p>
    <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($defaultPasswordWarning): ?><div class="msg warn">Standardpasswort aktiv (visitfy-admin). Bitte per VISITFY_ADMIN_PASSWORD in .deploy.env ändern.</div><?php endif; ?>
    <input type="hidden" name="action" value="login">
    <input type="password" name="password" placeholder="Admin-Passwort" required>
    <button type="submit">Einloggen</button>
  </form>
</body>
</html>
<?php
    exit;
}

$contentPath = admin_content_config_path();
$scriptPath = admin_script_config_path();
$content = admin_read_json($contentPath, []);
$script = admin_read_json($scriptPath, []);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_is_logged_in()) {
    if (!admin_validate_csrf($_POST['csrf'] ?? null)) {
        $error = 'Ungültige Anfrage (CSRF). Bitte Seite neu laden.';
    } else {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'save_content') {
            $features = preg_split('/\r\n|\r|\n/', (string)($_POST['about_features'] ?? '')) ?: [];
            $features = array_values(array_filter(array_map(static fn($v) => trim((string)$v), $features), static fn($v) => $v !== ''));

            $perfection = preg_split('/\r\n|\r|\n/', (string)($_POST['about_perfection_points'] ?? '')) ?: [];
            $perfection = array_values(array_filter(array_map(static fn($v) => trim((string)$v), $perfection), static fn($v) => $v !== ''));

            $heroWords = preg_split('/\r\n|\r|\n/', (string)($_POST['hero_rotating_words'] ?? '')) ?: [];
            $heroWords = array_values(array_filter(array_map(static fn($v) => trim((string)$v), $heroWords), static fn($v) => $v !== ''));

            $allowedButtonFxTargets = [
              'kontakt_submit',
              'partner_submit',
              'hero_primary',
              'hero_secondary',
              'cta_primary',
              'cta_secondary',
            ];
            $buttonFxTargetsInput = $_POST['button_fx_targets'] ?? [];
            if (!is_array($buttonFxTargetsInput)) {
              $buttonFxTargetsInput = [];
            }
            $buttonFxTargets = [];
            foreach ($buttonFxTargetsInput as $target) {
              $target = trim((string)$target);
              if ($target !== '' && in_array($target, $allowedButtonFxTargets, true) && !in_array($target, $buttonFxTargets, true)) {
                $buttonFxTargets[] = $target;
              }
            }

            $buttonFxColorRaw = trim((string)($_POST['button_fx_color'] ?? ''));
            if ($buttonFxColorRaw === '') {
                $buttonFxColorRaw = '#8ec9ff';
            }
            $buttonFxColor = $buttonFxColorRaw;
            if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $buttonFxColor)) {
              $buttonFxColor = '#8ec9ff';
            }

            $kpiTargets = $_POST['kpi_target'] ?? [];
            $kpiSuffixes = $_POST['kpi_suffix'] ?? [];
            $kpiLabels = $_POST['kpi_label'] ?? [];
            if (!is_array($kpiTargets)) {
              $kpiTargets = [];
            }
            if (!is_array($kpiSuffixes)) {
              $kpiSuffixes = [];
            }
            if (!is_array($kpiLabels)) {
              $kpiLabels = [];
            }

            $kpiItems = [];
            $kpiCount = max(count($kpiTargets), count($kpiSuffixes), count($kpiLabels));
            for ($i = 0; $i < $kpiCount; $i++) {
              $target = trim((string)($kpiTargets[$i] ?? ''));
              $suffix = trim((string)($kpiSuffixes[$i] ?? ''));
              $label = trim((string)($kpiLabels[$i] ?? ''));
              if ($target === '' && $suffix === '' && $label === '') {
                continue;
              }
              $kpiItems[] = [
                'target' => $target,
                'suffix' => $suffix,
                'label' => $label,
              ];
            }

            $content = [
                'seo' => [
                    'home_title' => (string)($_POST['seo_home_title'] ?? ''),
                    'home_desc' => (string)($_POST['seo_home_desc'] ?? ''),
                ],
                'intro' => [
                    'tagline' => (string)($_POST['intro_tagline'] ?? ''),
                    'hint' => (string)($_POST['intro_hint'] ?? ''),
                    'skip_button' => (string)($_POST['intro_skip_button'] ?? ''),
                ],
                'hero' => [
                    'eyebrow' => (string)($_POST['hero_eyebrow'] ?? ''),
                    'prefix' => (string)($_POST['hero_prefix'] ?? ''),
                    'rotating_words' => $heroWords,
                    'desc' => (string)($_POST['hero_desc'] ?? ''),
                    'button_primary_text' => (string)($_POST['hero_btn1_text'] ?? ''),
                    'button_primary_link' => (string)($_POST['hero_btn1_link'] ?? ''),
                    'button_secondary_text' => (string)($_POST['hero_btn2_text'] ?? ''),
                    'button_secondary_link' => (string)($_POST['hero_btn2_link'] ?? ''),
                ],
                'kpi' => [
                    'eyebrow' => (string)($_POST['kpi_eyebrow'] ?? ''),
                    'title' => (string)($_POST['kpi_title'] ?? ''),
                  'items' => $kpiItems,
                ],
                'marquee' => [
                    'label' => (string)($_POST['marquee_label'] ?? ''),
                ],
                'about' => [
                    'eyebrow' => (string)($_POST['about_eyebrow'] ?? ''),
                    'title' => (string)($_POST['about_title'] ?? ''),
                    'paragraph_1' => (string)($_POST['about_p1'] ?? ''),
                    'paragraph_2' => (string)($_POST['about_p2'] ?? ''),
                    'paragraph_3' => (string)($_POST['about_p3'] ?? ''),
                    'difference_title' => (string)($_POST['about_difference_title'] ?? ''),
                    'features_title' => (string)($_POST['about_features_title'] ?? ''),
                    'features' => $features,
                    'perfection_title' => (string)($_POST['about_perfection_title'] ?? ''),
                    'perfection_points' => $perfection,
                ],
                'team' => [
                    'kristian_name' => (string)($_POST['team_kristian_name'] ?? ''),
                    'kristian_role' => (string)($_POST['team_kristian_role'] ?? ''),
                    'kristian_text' => (string)($_POST['team_kristian_text'] ?? ''),
                    'jason_name' => (string)($_POST['team_jason_name'] ?? ''),
                    'jason_role' => (string)($_POST['team_jason_role'] ?? ''),
                    'jason_text' => (string)($_POST['team_jason_text'] ?? ''),
                    'jason_link_text' => (string)($_POST['team_jason_link_text'] ?? ''),
                    'jason_link_url' => (string)($_POST['team_jason_link_url'] ?? ''),
                ],
                'final_cta' => [
                    'title' => (string)($_POST['cta_title'] ?? ''),
                    'text' => (string)($_POST['cta_text'] ?? ''),
                    'button_primary_text' => (string)($_POST['cta_btn1_text'] ?? ''),
                    'button_primary_link' => (string)($_POST['cta_btn1_link'] ?? ''),
                    'button_secondary_text' => (string)($_POST['cta_btn2_text'] ?? ''),
                    'button_secondary_link' => (string)($_POST['cta_btn2_link'] ?? ''),
                ],
                'footer' => [
                    'brand_text' => (string)($_POST['footer_brand_text'] ?? ''),
                    'contact_email' => (string)($_POST['footer_contact_email'] ?? ''),
                    'website_by_prefix' => (string)($_POST['footer_website_by_prefix'] ?? ''),
                    'website_by_name' => (string)($_POST['footer_website_by_name'] ?? ''),
                    'website_by_url' => (string)($_POST['footer_website_by_url'] ?? ''),
                ],
                'button_fx' => [
                  'enabled' => !empty($_POST['button_fx_enabled']),
                  'color' => $buttonFxColor,
                  'shimmer' => !empty($_POST['button_fx_shimmer']),
                  'targets' => $buttonFxTargets,
                ],
            ];

            if (!admin_write_json($contentPath, $content)) {
                $error = 'Inhaltsdaten konnten nicht gespeichert werden.';
            } else {
                $notice = 'Inhalte gespeichert.';
            }
        }

        if ($action === 'save_script') {
            $heroWords = preg_split('/\r\n|\r|\n/', (string)($_POST['script_hero_words'] ?? '')) ?: [];
            $heroWords = array_values(array_filter(array_map(static fn($v) => trim((string)$v), $heroWords), static fn($v) => $v !== ''));

            $script = [
                'intro' => [
                    'text_delay' => (int)($_POST['intro_text_delay'] ?? 200),
                    'intro_hold' => (int)($_POST['intro_intro_hold'] ?? 1500),
                    'fade_out_duration' => (int)($_POST['intro_fade_out_duration'] ?? 1100),
                    'show_text_delay' => (int)($_POST['intro_show_text_delay'] ?? 120),
                    'skip_click_delay' => (int)($_POST['intro_skip_click_delay'] ?? 300),
                ],
                'main' => [
                    'particle_count' => (int)($_POST['main_particle_count'] ?? 500),
                    'particle_max_speed' => (float)($_POST['main_particle_max_speed'] ?? 0.45),
                    'particle_max_line_dist' => (int)($_POST['main_particle_max_line_dist'] ?? 90),
                    'particle_mouse_radius' => (int)($_POST['main_particle_mouse_radius'] ?? 120),
                    'particle_mouse_force' => (float)($_POST['main_particle_mouse_force'] ?? 0.012),
                    'hero_words' => $heroWords,
                    'hero_fade_duration' => (int)($_POST['main_hero_fade_duration'] ?? 420),
                    'hero_hold_duration' => (int)($_POST['main_hero_hold_duration'] ?? 1900),
                    'countup_duration' => (int)($_POST['main_countup_duration'] ?? 1800),
                    'stack_rotation_amount' => (float)($_POST['main_stack_rotation_amount'] ?? 0.5),
                    'stack_item_stack_dist' => (int)($_POST['main_stack_item_stack_dist'] ?? 15),
                ],
            ];

            if (!admin_write_json($scriptPath, $script)) {
                $error = 'Script-Variablen konnten nicht gespeichert werden.';
            } else {
                $notice = 'Script-Variablen gespeichert.';
            }
        }

        if ($action === 'upload_image') {
            $folders = admin_upload_folders();
            $targetFolder = admin_safe_relative_path((string)($_POST['target_folder'] ?? ''));
            if (!in_array($targetFolder, array_values($folders), true)) {
                $error = 'Ungültiger Upload-Ordner.';
            } elseif (!isset($_FILES['image_file']) || !is_array($_FILES['image_file'])) {
                $error = 'Keine Datei hochgeladen.';
            } else {
                $file = $_FILES['image_file'];
                $tmp = (string)($file['tmp_name'] ?? '');
                $name = (string)($file['name'] ?? '');
                $errCode = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

                if ($errCode !== UPLOAD_ERR_OK || $tmp === '' || $name === '') {
                    $error = 'Upload fehlgeschlagen.';
                } else {
                    $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $name) ?? '';
                    $cleanName = trim($cleanName, '-');
                    $ext = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION));
                    $allowedExt = ['png', 'jpg', 'jpeg', 'webp', 'avif', 'svg'];

                    if ($cleanName === '' || !in_array($ext, $allowedExt, true)) {
                        $error = 'Dateityp nicht erlaubt. Erlaubt: png, jpg, jpeg, webp, avif, svg.';
                    } else {
                        $folderAbs = admin_absolute_path($targetFolder);
                        if (!is_dir($folderAbs)) {
                            @mkdir($folderAbs, 0775, true);
                        }

                        $destAbs = $folderAbs . '/' . $cleanName;
                        $i = 1;
                        while (file_exists($destAbs)) {
                            $base = pathinfo($cleanName, PATHINFO_FILENAME);
                            $destAbs = $folderAbs . '/' . $base . '-' . $i . '.' . $ext;
                            $i++;
                        }

                        if (!move_uploaded_file($tmp, $destAbs)) {
                            $error = 'Datei konnte nicht gespeichert werden.';
                        } else {
                            $notice = 'Bild erfolgreich hochgeladen in ' . $targetFolder;
                        }
                    }
                }
            }
        }

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
                  $error = 'Datei wurde nicht gefunden.';
                } elseif (!@unlink($filePath)) {
                  $error = 'Datei konnte nicht gelöscht werden.';
                } else {
                  $notice = 'Bild gelöscht: ' . $imageName;
                }
              }
            }

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
                    $error = 'Eine Datei mit dem neuen Namen existiert bereits.';
                  } elseif ($oldName === $newName) {
                    $notice = 'Dateiname unverändert.';
                  } elseif (!@rename($oldPath, $newPath)) {
                    $error = 'Datei konnte nicht umbenannt werden.';
                  } else {
                    $notice = 'Bild umbenannt in: ' . $newName;
                  }
                }
              }
            }
    }

    $content = admin_read_json($contentPath, []);
    $script = admin_read_json($scriptPath, []);
}

$csrf = admin_csrf_token();

function admin_field(array $src, string $path, string $fallback = ''): string
{
    $segments = explode('.', $path);
    $cursor = $src;
    foreach ($segments as $segment) {
        if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
            return $fallback;
        }
        $cursor = $cursor[$segment];
    }
    return is_scalar($cursor) ? (string)$cursor : $fallback;
}

function admin_lines(array $src, string $path): string
{
    $segments = explode('.', $path);
    $cursor = $src;
    foreach ($segments as $segment) {
        if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
            return '';
        }
        $cursor = $cursor[$segment];
    }
    if (!is_array($cursor)) {
        return '';
    }
    $vals = array_map(static fn($v) => trim((string)$v), $cursor);
    $vals = array_values(array_filter($vals, static fn($v) => $v !== ''));
    return implode(PHP_EOL, $vals);
}

  function admin_bool_field(array $src, string $path, bool $fallback = false): bool
  {
    $segments = explode('.', $path);
    $cursor = $src;
    foreach ($segments as $segment) {
      if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
        return $fallback;
      }
      $cursor = $cursor[$segment];
    }
    if (is_bool($cursor)) {
      return $cursor;
    }
    if (is_numeric($cursor)) {
      return (int)$cursor === 1;
    }
    if (is_string($cursor)) {
      $normalized = strtolower(trim($cursor));
      return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
    return $fallback;
  }

  $kpiFormItems = [];
  if (isset($content['kpi']) && is_array($content['kpi']) && isset($content['kpi']['items']) && is_array($content['kpi']['items'])) {
    $kpiFormItems = $content['kpi']['items'];
  }
  if (!is_array($kpiFormItems)) {
    $kpiFormItems = [];
  }
  $kpiFormItems = array_values(array_filter($kpiFormItems, static function ($item) {
    if (!is_array($item)) {
      return false;
    }
    $target = trim((string)($item['target'] ?? ''));
    $suffix = trim((string)($item['suffix'] ?? ''));
    $label = trim((string)($item['label'] ?? ''));
    return $target !== '' || $suffix !== '' || $label !== '';
  }));
  if (!$kpiFormItems) {
    $kpiFormItems = [['target' => '', 'suffix' => '', 'label' => '']];
  }

  $buttonFxAllowed = [
    'kontakt_submit' => 'Kontaktformular: Anfrage absenden',
    'partner_submit' => 'Partnerformular: Partner-Anfrage absenden',
    'hero_primary' => 'Startseite Hero: Primärbutton',
    'hero_secondary' => 'Startseite Hero: Sekundärbutton',
    'cta_primary' => 'Startseite CTA: Primärbutton',
    'cta_secondary' => 'Startseite CTA: Sekundärbutton',
  ];
  $buttonFxTargets = $content['button_fx']['targets'] ?? [];
  if (!is_array($buttonFxTargets)) {
    $buttonFxTargets = [];
  }
  $buttonFxTargets = array_values(array_filter($buttonFxTargets, static fn($v) => is_string($v) && isset($buttonFxAllowed[$v])));
  $buttonFxColor = admin_field($content, 'button_fx.color', '#8ec9ff');
  if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $buttonFxColor)) {
    $buttonFxColor = '#8ec9ff';
  }
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Visitfy Admin Panel</title>
  <style>
    :root{--bg:#070707;--panel:#111;--line:#272727;--text:#f2f2f2;--muted:#b6b6b6;--ok:#1e4f33;--err:#5b1f1f}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
    .wrap{max-width:1380px;margin:0 auto;padding:20px}
    .top{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:16px}
    .top h1{font-size:1.2rem;margin:0}
    .top a{color:#fff;text-decoration:none;border:1px solid #3a3a3a;border-radius:8px;padding:8px 12px}
    .grid{display:grid;grid-template-columns:2fr 1fr;gap:16px}
    .panel{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:14px}
    .panel h2{font-size:1rem;margin:0 0 10px}
    .msg{padding:10px 12px;border-radius:10px;margin-bottom:10px;font-size:.92rem}
    .ok{background:var(--ok);border:1px solid #327653}
    .bad{background:var(--err);border:1px solid #8f4444}
    .small{font-size:.82rem;color:var(--muted);line-height:1.5}
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
    .tabbtn{background:#161616;color:#fff;border:1px solid #333;border-radius:10px;padding:8px 10px;font-size:.85rem;cursor:pointer}
    .tabbtn.active{background:#fff;color:#000}
    .tab{display:none}
    .tab.active{display:block}
    .content-menu{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 12px}
    .content-btn{background:#161616;color:#fff;border:1px solid #333;border-radius:10px;padding:8px 10px;font-size:.82rem;cursor:pointer}
    .content-btn.active{background:#fff;color:#000}
    .content-section{display:none}
    .content-section.active{display:block}
    .field-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .field-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
    label{display:block;font-size:.78rem;letter-spacing:.02em;color:var(--muted);margin:10px 0 6px}
    input,textarea,select{width:100%;background:#0c0c0c;border:1px solid #2d2d2d;color:#fff;border-radius:10px;padding:10px}
    textarea{min-height:96px;font-family:inherit;line-height:1.45}
    .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
    button{background:#fff;color:#000;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer}
    .img-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px;margin-top:10px}
    .thumb{border:1px solid #2a2a2a;border-radius:8px;padding:6px}
    .thumb img{width:100%;height:68px;object-fit:contain;background:#0a0a0a}
    .thumb span{display:block;font-size:.7rem;color:var(--muted);margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .thumb small{display:block;font-size:.68rem;color:#8f8f8f;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .thumb-actions{display:flex;flex-direction:column;gap:6px;margin-top:8px}
    .thumb-actions form{display:flex;gap:6px;align-items:center}
    .thumb-actions input[type="text"]{padding:6px 8px;font-size:.72rem;border-radius:8px}
    .thumb-actions button{margin:0;padding:6px 8px;font-size:.72rem;border-radius:8px;width:auto}
    .thumb-delete{background:#2e1515;color:#ffd6d6;border:1px solid #5b2b2b}
    .kpi-rows{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px}
    .kpi-row{border:1px solid #272727;border-radius:10px;padding:10px}
    .kpi-row-top{display:flex;justify-content:space-between;align-items:center;gap:10px}
    .kpi-remove{background:#1b1b1b;color:#fff;border:1px solid #3b3b3b;padding:6px 10px;border-radius:8px;font-size:.8rem}
    .kpi-add{background:#1b1b1b;color:#fff;border:1px solid #3b3b3b}
    hr{border:none;border-top:1px solid #242424;margin:14px 0}
    @media (max-width: 1020px){.grid{grid-template-columns:1fr}.field-grid,.field-grid-3{grid-template-columns:1fr}}
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <h1>Visitfy Admin Panel (Einfach-Modus)</h1>
    <a href="logout.php">Logout</a>
  </div>

  <?php if ($notice): ?><div class="msg ok"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
  <?php if ($error): ?><div class="msg bad"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

  <div class="grid">
    <div class="panel">
      <div class="tabs">
        <button class="tabbtn active" type="button" data-tab="content">Inhalte</button>
        <button class="tabbtn" type="button" data-tab="scripts">Script-Variablen</button>
      </div>

      <div class="tab active" id="tab-content">
        <form method="post" action="">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="action" value="save_content">

          <div class="content-menu">
            <button type="button" class="content-btn active" data-content-target="seo">SEO</button>
            <button type="button" class="content-btn" data-content-target="intro">Intro</button>
            <button type="button" class="content-btn" data-content-target="hero">Hero</button>
            <button type="button" class="content-btn" data-content-target="kpi">KPI</button>
            <button type="button" class="content-btn" data-content-target="marquee">Marquee</button>
            <button type="button" class="content-btn" data-content-target="about">About</button>
            <button type="button" class="content-btn" data-content-target="team">Team</button>
            <button type="button" class="content-btn" data-content-target="cta">Final CTA</button>
            <button type="button" class="content-btn" data-content-target="footer">Footer</button>
            <button type="button" class="content-btn" data-content-target="buttonfx">Button Stil</button>
          </div>

          <section class="content-section active" data-content-section="seo">
            <h2>SEO</h2>
            <label>Startseite Titel</label>
            <input type="text" name="seo_home_title" value="<?= htmlspecialchars(admin_field($content, 'seo.home_title'), ENT_QUOTES, 'UTF-8') ?>">
            <label>Startseite Beschreibung</label>
            <textarea name="seo_home_desc"><?= htmlspecialchars(admin_field($content, 'seo.home_desc'), ENT_QUOTES, 'UTF-8') ?></textarea>
          </section>

          <section class="content-section" data-content-section="intro">
            <h2>Intro</h2>
            <div class="field-grid">
              <div>
                <label>Tagline</label>
                <input type="text" name="intro_tagline" value="<?= htmlspecialchars(admin_field($content, 'intro.tagline'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div>
                <label>Hinweistext</label>
                <input type="text" name="intro_hint" value="<?= htmlspecialchars(admin_field($content, 'intro.hint'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <label>Button (Überspringen)</label>
            <input type="text" name="intro_skip_button" value="<?= htmlspecialchars(admin_field($content, 'intro.skip_button'), ENT_QUOTES, 'UTF-8') ?>">
          </section>

          <section class="content-section" data-content-section="hero">
            <h2>Hero</h2>
            <div class="field-grid">
              <div>
                <label>Eyebrow</label>
                <input type="text" name="hero_eyebrow" value="<?= htmlspecialchars(admin_field($content, 'hero.eyebrow'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div>
                <label>Prefix (z.B. MEHR)</label>
                <input type="text" name="hero_prefix" value="<?= htmlspecialchars(admin_field($content, 'hero.prefix'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <label>Rotierende Wörter (1 Zeile = 1 Wort)</label>
            <textarea name="hero_rotating_words"><?= htmlspecialchars(admin_lines($content, 'hero.rotating_words'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <label>Beschreibung</label>
            <textarea name="hero_desc"><?= htmlspecialchars(admin_field($content, 'hero.desc'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="field-grid-3">
              <div>
                <label>Primär Button Text</label>
                <input type="text" name="hero_btn1_text" value="<?= htmlspecialchars(admin_field($content, 'hero.button_primary_text'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div>
                <label>Primär Button Link</label>
                <input type="text" name="hero_btn1_link" value="<?= htmlspecialchars(admin_field($content, 'hero.button_primary_link'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div></div>
              <div>
                <label>Sekundär Button Text</label>
                <input type="text" name="hero_btn2_text" value="<?= htmlspecialchars(admin_field($content, 'hero.button_secondary_text'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div>
                <label>Sekundär Button Link</label>
                <input type="text" name="hero_btn2_link" value="<?= htmlspecialchars(admin_field($content, 'hero.button_secondary_link'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
          </section>

          <section class="content-section" data-content-section="kpi">
            <h2>KPI (Zahlen)</h2>
            <div class="kpi-rows" id="kpi-rows">
              <?php foreach ($kpiFormItems as $index => $kpiItem): ?>
                <div class="kpi-row" data-kpi-row>
                  <div class="kpi-row-top">
                    <strong>KPI <?= (int)$index + 1 ?></strong>
                    <button class="kpi-remove" type="button" data-kpi-remove>Entfernen</button>
                  </div>
                  <label>Zahl</label>
                  <input type="text" name="kpi_target[]" value="<?= htmlspecialchars((string)($kpiItem['target'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  <label>Suffix</label>
                  <input type="text" name="kpi_suffix[]" value="<?= htmlspecialchars((string)($kpiItem['suffix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  <label>Text</label>
                  <textarea name="kpi_label[]"><?= htmlspecialchars((string)($kpiItem['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="actions"><button class="kpi-add" id="kpi-add" type="button">+ KPI hinzufügen</button></div>
            <div class="field-grid">
              <div>
                <label>KPI Eyebrow</label>
                <input type="text" name="kpi_eyebrow" value="<?= htmlspecialchars(admin_field($content, 'kpi.eyebrow'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div>
                <label>KPI Titel</label>
                <input type="text" name="kpi_title" value="<?= htmlspecialchars(admin_field($content, 'kpi.title'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
          </section>

          <section class="content-section" data-content-section="marquee">
            <h2>Marquee</h2>
            <label>Marquee Label</label>
            <input type="text" name="marquee_label" value="<?= htmlspecialchars(admin_field($content, 'marquee.label'), ENT_QUOTES, 'UTF-8') ?>">
          </section>

          <section class="content-section" data-content-section="about">
            <h2>About</h2>
            <div class="field-grid">
              <div>
                <label>About Eyebrow</label>
                <input type="text" name="about_eyebrow" value="<?= htmlspecialchars(admin_field($content, 'about.eyebrow'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div>
                <label>About Titel</label>
                <input type="text" name="about_title" value="<?= htmlspecialchars(admin_field($content, 'about.title'), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <label>Paragraph 1</label><textarea name="about_p1"><?= htmlspecialchars(admin_field($content, 'about.paragraph_1'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <label>Paragraph 2</label><textarea name="about_p2"><?= htmlspecialchars(admin_field($content, 'about.paragraph_2'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <label>Paragraph 3</label><textarea name="about_p3"><?= htmlspecialchars(admin_field($content, 'about.paragraph_3'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <label>Unterschied Titel (Zeilenumbruch möglich)</label><textarea name="about_difference_title"><?= htmlspecialchars(admin_field($content, 'about.difference_title'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <label>Features Titel</label><input type="text" name="about_features_title" value="<?= htmlspecialchars(admin_field($content, 'about.features_title'), ENT_QUOTES, 'UTF-8') ?>">
            <label>Features Liste (1 Zeile = 1 Punkt)</label><textarea name="about_features"><?= htmlspecialchars(admin_lines($content, 'about.features'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <label>Perfektion Titel</label><input type="text" name="about_perfection_title" value="<?= htmlspecialchars(admin_field($content, 'about.perfection_title'), ENT_QUOTES, 'UTF-8') ?>">
            <label>Perfektion Liste (1 Zeile = 1 Punkt)</label><textarea name="about_perfection_points"><?= htmlspecialchars(admin_lines($content, 'about.perfection_points'), ENT_QUOTES, 'UTF-8') ?></textarea>
          </section>

          <section class="content-section" data-content-section="team">
            <h2>Team</h2>
            <div class="field-grid">
              <div><label>Kristian Name</label><input type="text" name="team_kristian_name" value="<?= htmlspecialchars(admin_field($content, 'team.kristian_name'), ENT_QUOTES, 'UTF-8') ?>"></div>
              <div><label>Kristian Rolle</label><input type="text" name="team_kristian_role" value="<?= htmlspecialchars(admin_field($content, 'team.kristian_role'), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <label>Kristian Text</label><textarea name="team_kristian_text"><?= htmlspecialchars(admin_field($content, 'team.kristian_text'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="field-grid">
              <div><label>Jason Name</label><input type="text" name="team_jason_name" value="<?= htmlspecialchars(admin_field($content, 'team.jason_name'), ENT_QUOTES, 'UTF-8') ?>"></div>
              <div><label>Jason Rolle</label><input type="text" name="team_jason_role" value="<?= htmlspecialchars(admin_field($content, 'team.jason_role'), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <label>Jason Text</label><textarea name="team_jason_text"><?= htmlspecialchars(admin_field($content, 'team.jason_text'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="field-grid">
              <div><label>Jason Link Text</label><input type="text" name="team_jason_link_text" value="<?= htmlspecialchars(admin_field($content, 'team.jason_link_text'), ENT_QUOTES, 'UTF-8') ?>"></div>
              <div><label>Jason Link URL</label><input type="text" name="team_jason_link_url" value="<?= htmlspecialchars(admin_field($content, 'team.jason_link_url'), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
          </section>

          <section class="content-section" data-content-section="cta">
            <h2>Final CTA</h2>
            <label>CTA Titel (Zeilenumbruch möglich)</label><textarea name="cta_title"><?= htmlspecialchars(admin_field($content, 'final_cta.title'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <label>CTA Text</label><textarea name="cta_text"><?= htmlspecialchars(admin_field($content, 'final_cta.text'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="field-grid">
              <div><label>CTA Button 1 Text</label><input type="text" name="cta_btn1_text" value="<?= htmlspecialchars(admin_field($content, 'final_cta.button_primary_text'), ENT_QUOTES, 'UTF-8') ?>"></div>
              <div><label>CTA Button 1 Link</label><input type="text" name="cta_btn1_link" value="<?= htmlspecialchars(admin_field($content, 'final_cta.button_primary_link'), ENT_QUOTES, 'UTF-8') ?>"></div>
              <div><label>CTA Button 2 Text</label><input type="text" name="cta_btn2_text" value="<?= htmlspecialchars(admin_field($content, 'final_cta.button_secondary_text'), ENT_QUOTES, 'UTF-8') ?>"></div>
              <div><label>CTA Button 2 Link</label><input type="text" name="cta_btn2_link" value="<?= htmlspecialchars(admin_field($content, 'final_cta.button_secondary_link'), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
          </section>

          <section class="content-section" data-content-section="footer">
            <h2>Footer</h2>
            <label>Footer Brand Text (Zeilenumbruch möglich)</label><textarea name="footer_brand_text"><?= htmlspecialchars(admin_field($content, 'footer.brand_text'), ENT_QUOTES, 'UTF-8') ?></textarea>
            <label>Footer Kontakt E-Mail</label><input type="text" name="footer_contact_email" value="<?= htmlspecialchars(admin_field($content, 'footer.contact_email'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="field-grid">
              <div><label>Prefix "Webseite von"</label><input type="text" name="footer_website_by_prefix" value="<?= htmlspecialchars(admin_field($content, 'footer.website_by_prefix'), ENT_QUOTES, 'UTF-8') ?>"></div>
              <div><label>Name Link</label><input type="text" name="footer_website_by_name" value="<?= htmlspecialchars(admin_field($content, 'footer.website_by_name'), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <label>Footer Link URL</label><input type="text" name="footer_website_by_url" value="<?= htmlspecialchars(admin_field($content, 'footer.website_by_url'), ENT_QUOTES, 'UTF-8') ?>">
          </section>

          <section class="content-section" data-content-section="buttonfx">
            <h2>Button Stil</h2>
            <p class="small">Optionaler Spezial-Stil: hellblauer Button mit weißem Schimmer (animiert). Sie können jederzeit deaktivieren und genau festlegen, welche Buttons betroffen sind.</p>
            <div class="field-grid">
              <div>
                <label style="display:flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0;font-size:.9rem;color:#fff">
                  <input type="checkbox" name="button_fx_enabled" value="1" style="width:auto"<?= admin_bool_field($content, 'button_fx.enabled', false) ? ' checked' : '' ?>>
                  Stil aktivieren
                </label>
              </div>
              <div>
                <label style="display:flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0;font-size:.9rem;color:#fff">
                  <input type="checkbox" name="button_fx_shimmer" value="1" style="width:auto"<?= admin_bool_field($content, 'button_fx.shimmer', true) ? ' checked' : '' ?>>
                  Weißer Schimmer animiert
                </label>
              </div>
            </div>
            <label>Farbe</label>
            <div class="field-grid">
              <input id="button-fx-color-picker" type="color" value="<?= htmlspecialchars($buttonFxColor, ENT_QUOTES, 'UTF-8') ?>">
              <input type="text" name="button_fx_color" value="<?= htmlspecialchars($buttonFxColor, ENT_QUOTES, 'UTF-8') ?>" placeholder="#8ec9ff">
            </div>
            <label>Welche Buttons?</label>
            <div class="field-grid">
              <?php foreach ($buttonFxAllowed as $value => $label): ?>
                <label style="display:flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0;font-size:.85rem;color:#fff;margin:0;">
                  <input type="checkbox" name="button_fx_targets[]" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" style="width:auto"<?= in_array($value, $buttonFxTargets, true) ? ' checked' : '' ?>>
                  <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </label>
              <?php endforeach; ?>
            </div>
          </section>

          <div class="actions"><button type="submit">Inhalte speichern</button></div>
        </form>
      </div>

      <div class="tab" id="tab-scripts">
        <form method="post" action="">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="action" value="save_script">

          <h2>Intro Variablen</h2>
          <div class="field-grid">
            <div><label>Text Delay (ms)</label><input type="number" name="intro_text_delay" value="<?= htmlspecialchars(admin_field($script, 'intro.text_delay', '200'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Intro Hold (ms)</label><input type="number" name="intro_intro_hold" value="<?= htmlspecialchars(admin_field($script, 'intro.intro_hold', '1500'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Fade Out Duration (ms)</label><input type="number" name="intro_fade_out_duration" value="<?= htmlspecialchars(admin_field($script, 'intro.fade_out_duration', '1100'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Show Text Delay (ms)</label><input type="number" name="intro_show_text_delay" value="<?= htmlspecialchars(admin_field($script, 'intro.show_text_delay', '120'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Skip Click Delay (ms)</label><input type="number" name="intro_skip_click_delay" value="<?= htmlspecialchars(admin_field($script, 'intro.skip_click_delay', '300'), ENT_QUOTES, 'UTF-8') ?>"></div>
          </div>

          <h2>Main JS Variablen</h2>
          <div class="field-grid">
            <div><label>Particle Count</label><input type="number" name="main_particle_count" value="<?= htmlspecialchars(admin_field($script, 'main.particle_count', '500'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Particle Max Speed</label><input type="text" name="main_particle_max_speed" value="<?= htmlspecialchars(admin_field($script, 'main.particle_max_speed', '0.45'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Particle Max Line Dist</label><input type="number" name="main_particle_max_line_dist" value="<?= htmlspecialchars(admin_field($script, 'main.particle_max_line_dist', '90'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Mouse Radius</label><input type="number" name="main_particle_mouse_radius" value="<?= htmlspecialchars(admin_field($script, 'main.particle_mouse_radius', '120'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Mouse Force</label><input type="text" name="main_particle_mouse_force" value="<?= htmlspecialchars(admin_field($script, 'main.particle_mouse_force', '0.012'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Hero Fade Duration (ms)</label><input type="number" name="main_hero_fade_duration" value="<?= htmlspecialchars(admin_field($script, 'main.hero_fade_duration', '420'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Hero Hold Duration (ms)</label><input type="number" name="main_hero_hold_duration" value="<?= htmlspecialchars(admin_field($script, 'main.hero_hold_duration', '1900'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>CountUp Duration (ms)</label><input type="number" name="main_countup_duration" value="<?= htmlspecialchars(admin_field($script, 'main.countup_duration', '1800'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Stack Rotation Amount</label><input type="text" name="main_stack_rotation_amount" value="<?= htmlspecialchars(admin_field($script, 'main.stack_rotation_amount', '0.5'), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label>Stack Item Dist (px)</label><input type="number" name="main_stack_item_stack_dist" value="<?= htmlspecialchars(admin_field($script, 'main.stack_item_stack_dist', '15'), ENT_QUOTES, 'UTF-8') ?>"></div>
          </div>
          <label>Hero Wörter (1 Zeile = 1 Wort)</label>
          <textarea name="script_hero_words"><?= htmlspecialchars(admin_lines($script, 'main.hero_words'), ENT_QUOTES, 'UTF-8') ?></textarea>

          <div class="actions"><button type="submit">Script-Variablen speichern</button></div>
        </form>
      </div>
    </div>

    <div class="panel">
      <h2>Bilder hochladen</h2>
      <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="upload_image">
        <label for="target_folder">Zielordner</label>
        <select id="target_folder" name="target_folder">
          <?php foreach (admin_upload_folders() as $label => $path): ?>
            <option value="<?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>

        <label for="image_file">Datei</label>
        <input id="image_file" type="file" name="image_file" accept=".png,.jpg,.jpeg,.webp,.avif,.svg" required>

        <div class="actions"><button type="submit">Upload</button></div>
      </form>

      <p class="small" style="margin-top:12px">Logos für Marquee bitte in <strong>assets/img/client-logos</strong> hochladen.</p>

      <hr>
      <h2>Vorschau</h2>
      <div class="img-grid">
        <?php
        foreach (admin_upload_folders() as $folderLabel => $folderPath) {
            $absFolder = admin_absolute_path($folderPath);
            if (!is_dir($absFolder)) {
                continue;
            }
            $items = glob($absFolder . '/*.{png,jpg,jpeg,webp,avif,svg}', GLOB_BRACE);
            if (!is_array($items)) {
                continue;
            }
            $items = array_slice($items, 0, 30);
            foreach ($items as $imgPath) {
                $name = basename($imgPath);
                $src = '../' . trim($folderPath, '/') . '/' . rawurlencode($name);
                ?>
                <div class="thumb">
                  <img src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                  <span><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
                  <small><?= htmlspecialchars($folderLabel, ENT_QUOTES, 'UTF-8') ?></small>
                  <div class="thumb-actions">
                    <form method="post" action="">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action" value="rename_image">
                      <input type="hidden" name="target_folder" value="<?= htmlspecialchars($folderPath, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="old_name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="text" name="new_name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" aria-label="Neuer Dateiname">
                      <button type="submit">Umbenennen</button>
                    </form>
                    <form method="post" action="" onsubmit="return confirm('Bild wirklich löschen?');">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action" value="delete_image">
                      <input type="hidden" name="target_folder" value="<?= htmlspecialchars($folderPath, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="image_name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                      <button class="thumb-delete" type="submit">Löschen</button>
                    </form>
                  </div>
                </div>
                <?php
            }
        }
        ?>
      </div>
    </div>
  </div>
</div>

<script>
  const tabBtns = document.querySelectorAll('[data-tab]');
  const tabs = {
    content: document.getElementById('tab-content'),
    scripts: document.getElementById('tab-scripts')
  };
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.getAttribute('data-tab');
      tabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      Object.values(tabs).forEach(el => el.classList.remove('active'));
      if (tabs[target]) tabs[target].classList.add('active');
    });
  });

  const contentBtns = document.querySelectorAll('[data-content-target]');
  const contentSections = document.querySelectorAll('[data-content-section]');

  function showContentSection(name) {
    contentBtns.forEach(btn => {
      btn.classList.toggle('active', btn.getAttribute('data-content-target') === name);
    });
    contentSections.forEach(section => {
      section.classList.toggle('active', section.getAttribute('data-content-section') === name);
    });
    try {
      localStorage.setItem('visitfy-admin-content-section', name);
    } catch (e) {}
  }

  contentBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.getAttribute('data-content-target');
      if (!target) return;
      showContentSection(target);
    });
  });

  let defaultContentSection = 'seo';
  try {
    const stored = localStorage.getItem('visitfy-admin-content-section');
    if (stored && document.querySelector(`[data-content-section="${stored}"]`)) {
      defaultContentSection = stored;
    }
  } catch (e) {}
  showContentSection(defaultContentSection);

  const kpiRows = document.getElementById('kpi-rows');
  const kpiAdd = document.getElementById('kpi-add');

  function createKpiRow() {
    const row = document.createElement('div');
    row.className = 'kpi-row';
    row.setAttribute('data-kpi-row', '');
    row.innerHTML = `
      <div class="kpi-row-top">
        <strong></strong>
        <button class="kpi-remove" type="button" data-kpi-remove>Entfernen</button>
      </div>
      <label>Zahl</label>
      <input type="text" name="kpi_target[]" value="">
      <label>Suffix</label>
      <input type="text" name="kpi_suffix[]" value="">
      <label>Text</label>
      <textarea name="kpi_label[]"></textarea>
    `;
    return row;
  }

  function syncKpiRowLabels() {
    const rows = kpiRows.querySelectorAll('[data-kpi-row]');
    rows.forEach((row, index) => {
      const title = row.querySelector('.kpi-row-top strong');
      if (title) {
        title.textContent = `KPI ${index + 1}`;
      }
    });
    const removeButtons = kpiRows.querySelectorAll('[data-kpi-remove]');
    removeButtons.forEach(btn => {
      btn.disabled = rows.length <= 1;
      btn.style.opacity = rows.length <= 1 ? '0.45' : '1';
      btn.style.cursor = rows.length <= 1 ? 'not-allowed' : 'pointer';
    });
  }

  kpiAdd?.addEventListener('click', () => {
    kpiRows.appendChild(createKpiRow());
    syncKpiRowLabels();
  });

  kpiRows?.addEventListener('click', event => {
    const btn = event.target.closest('[data-kpi-remove]');
    if (!btn) return;
    const row = btn.closest('[data-kpi-row]');
    if (!row) return;
    if (kpiRows.querySelectorAll('[data-kpi-row]').length <= 1) {
      return;
    }
    row.remove();
    syncKpiRowLabels();
  });
  if (kpiRows) syncKpiRowLabels();

  const buttonFxColorPicker = document.getElementById('button-fx-color-picker');
  const buttonFxColorText = document.querySelector('input[name="button_fx_color"]');
  if (buttonFxColorPicker && buttonFxColorText) {
    buttonFxColorPicker.addEventListener('input', () => {
      buttonFxColorText.value = buttonFxColorPicker.value;
    });
    buttonFxColorText.addEventListener('input', () => {
      if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(buttonFxColorText.value.trim())) {
        buttonFxColorPicker.value = buttonFxColorText.value.trim();
      }
    });
  }
</script>
</body>
</html>
