<?php
/**
 * Visitfy Admin – Settings (Script Vars + Password Management)
 */
require_once dirname(__DIR__) . '/bootstrap.php';
admin_require_login();

$notice = '';
$error = '';
$csrf = admin_csrf_token();

function he(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$scriptPath = admin_script_config_path();
$script = admin_read_json($scriptPath, []);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_validate_csrf($_POST['csrf'] ?? null)) {
    $action = (string)($_POST['action'] ?? '');

    // ── Change password ──────────────────────────────────────────
    if ($action === 'save_password') {
        $currentPw  = (string)($_POST['current_password'] ?? '');
        $newPw      = (string)($_POST['new_password'] ?? '');
        $confirmPw  = (string)($_POST['confirm_password'] ?? '');

        if (!hash_equals(admin_password(), $currentPw)) {
            $error = 'Aktuelles Passwort ist falsch.';
        } elseif (strlen($newPw) < 8) {
            $error = 'Neues Passwort muss mindestens 8 Zeichen haben.';
        } elseif ($newPw !== $confirmPw) {
            $error = 'Passwörter stimmen nicht überein.';
        } elseif (!admin_write_password($newPw)) {
            $error = 'Passwort konnte nicht gespeichert werden.';
        } else {
            $notice = 'Passwort erfolgreich geändert.';
        }
    }

    // ── Reset password (generate + email) ───────────────────────
    if ($action === 'reset_password') {
        $newPw = bin2hex(random_bytes(8)); // 16-char hex
        if (!admin_write_password($newPw)) {
            $error = 'Passwort konnte nicht gespeichert werden.';
        } else {
            // Try to send via Mailgun
            $mailPartial = dirname(dirname(__DIR__)) . '/partials/mail.php';
            $mailSent = false;
            if (is_file($mailPartial)) {
                require_once $mailPartial;
                $cfg = admin_read_mail_settings();
                if (function_exists('visitfy_mail_is_configured') && visitfy_mail_is_configured($cfg)) {
                    $payload = [
                        'from'    => function_exists('visitfy_format_from_header')
                            ? visitfy_format_from_header((string)$cfg['MAILGUN_FROM_NAME'], (string)$cfg['MAILGUN_FROM_EMAIL'])
                            : $cfg['MAILGUN_FROM_EMAIL'],
                        'to'      => 'info@visitfy.de',
                        'subject' => 'Visitfy Admin - Neues Passwort',
                        'text'    => 'Dein neues Admin-Passwort: ' . $newPw . "\n\nBitte ändere es nach dem Login in den Einstellungen.",
                    ];
                    $result = visitfy_send_via_mailgun($cfg, $payload);
                    $mailSent = $result['ok'];
                }
            }
            if ($mailSent) {
                $notice = 'Neues Passwort generiert und an info@visitfy.de gesendet.';
            } else {
                $notice = 'Neues Passwort gesetzt: ' . $newPw . ' — Mailversand fehlgeschlagen, bitte notiere das Passwort.';
            }
        }
    }

    // ── Save script variables ────────────────────────────────────
    if ($action === 'save_script') {
        $heroWords = preg_split('/\r\n|\r|\n/', (string)($_POST['script_hero_words'] ?? '')) ?: [];
        $heroWords = array_values(array_filter(array_map(fn($v) => trim((string)$v), $heroWords), fn($v) => $v !== ''));

        $script = [
            'intro' => [
                'text_delay'         => (int)($_POST['intro_text_delay'] ?? 200),
                'intro_hold'         => (int)($_POST['intro_intro_hold'] ?? 1500),
                'fade_out_duration'  => (int)($_POST['intro_fade_out_duration'] ?? 1100),
                'show_text_delay'    => (int)($_POST['intro_show_text_delay'] ?? 120),
                'skip_click_delay'   => (int)($_POST['intro_skip_click_delay'] ?? 300),
            ],
            'main' => [
                'particle_count'         => (int)($_POST['main_particle_count'] ?? 500),
                'particle_max_speed'     => (float)($_POST['main_particle_max_speed'] ?? 0.45),
                'particle_max_line_dist' => (int)($_POST['main_particle_max_line_dist'] ?? 90),
                'particle_mouse_radius'  => (int)($_POST['main_particle_mouse_radius'] ?? 120),
                'particle_mouse_force'   => (float)($_POST['main_particle_mouse_force'] ?? 0.012),
                'hero_words'             => $heroWords,
                'hero_fade_duration'     => (int)($_POST['main_hero_fade_duration'] ?? 420),
                'hero_hold_duration'     => (int)($_POST['main_hero_hold_duration'] ?? 1900),
                'countup_duration'       => (int)($_POST['main_countup_duration'] ?? 1800),
                'stack_rotation_amount'  => (float)($_POST['main_stack_rotation_amount'] ?? 0.5),
                'stack_item_stack_dist'  => (int)($_POST['main_stack_item_stack_dist'] ?? 15),
                'tour_display_style'     => in_array(($_POST['main_tour_display_style'] ?? 'stack'), ['stack', 'cardswap'], true)
                    ? $_POST['main_tour_display_style'] : 'stack',
            ],
        ];

        if (!admin_write_json($scriptPath, $script)) {
            $error = 'Script-Variablen konnten nicht gespeichert werden.';
        } else {
            $notice = 'Script-Variablen gespeichert.';
        }
    }

    // Reload
    $script = admin_read_json($scriptPath, []);
}

$defaultPwWarning = admin_password() === 'visitfy-admin';

ob_start();
?>
<div class="topbar">
  <div class="topbar-title">Einstellungen</div>
  <?php if ($defaultPwWarning): ?>
  <div class="topbar-actions">
    <span class="badge badge-yellow">Standardpasswort aktiv</span>
  </div>
  <?php endif; ?>
</div>

<div class="page-body">

  <!-- Password Change -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Passwort ändern</div>
        <div class="card-desc" style="margin-bottom:0">Ändere dein Admin-Passwort. Mindestens 8 Zeichen.</div>
      </div>
      <?php if ($defaultPwWarning): ?>
      <span class="badge badge-yellow">Standard-PW aktiv</span>
      <?php endif; ?>
    </div>

    <form method="post" action="?p=settings">
      <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
      <input type="hidden" name="action" value="save_password">

      <div class="form-row">
        <div class="field">
          <label>Aktuelles Passwort</label>
          <input type="password" name="current_password" autocomplete="current-password" required>
        </div>
        <div></div>
      </div>
      <div class="form-row mt-12">
        <div class="field">
          <label>Neues Passwort</label>
          <input type="password" name="new_password" autocomplete="new-password" minlength="8" required>
        </div>
        <div class="field">
          <label>Passwort bestätigen</label>
          <input type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
        </div>
      </div>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">Passwort speichern</button>
      </div>
    </form>
  </div>

  <!-- Password Reset -->
  <div class="card" style="margin-top:16px">
    <div class="card-header">
      <div>
        <div class="card-title">Passwort zurücksetzen</div>
        <div class="card-desc" style="margin-bottom:0">
          Generiert ein neues zufälliges Passwort und sendet es an <strong>info@visitfy.de</strong> per Mailgun.
          Stelle sicher, dass Mailgun konfiguriert ist.
        </div>
      </div>
    </div>
    <form method="post" action="?p=settings" onsubmit="return confirm('Passwort zurücksetzen und per E-Mail senden?')">
      <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
      <input type="hidden" name="action" value="reset_password">
      <button type="submit" class="btn btn-danger">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" style="margin-right:4px">
          <path d="M2 8a6 6 0 1 0 1.5-3.9"/>
          <path d="M2 3v5h5"/>
        </svg>
        Passwort zurücksetzen
      </button>
    </form>
  </div>

  <!-- Script Variables -->
  <div class="card" style="margin-top:16px">
    <div class="card-header">
      <div>
        <div class="card-title">Script-Variablen</div>
        <div class="card-desc" style="margin-bottom:0">Animations- und Performance-Einstellungen für Intro und Hauptseite.</div>
      </div>
    </div>

    <form id="script-form" method="post" action="?p=settings">
      <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
      <input type="hidden" name="action" value="save_script">

      <!-- Intro Variables -->
      <div style="margin-bottom:16px">
        <div class="card-title" style="font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2);margin-bottom:12px">Intro Animation</div>
        <div class="form-row">
          <div class="field">
            <label>Text Delay (ms)</label>
            <input type="number" name="intro_text_delay" value="<?= (int)($script['intro']['text_delay'] ?? 200) ?>">
          </div>
          <div class="field">
            <label>Intro Hold (ms)</label>
            <input type="number" name="intro_intro_hold" value="<?= (int)($script['intro']['intro_hold'] ?? 1500) ?>">
          </div>
        </div>
        <div class="form-row mt-12">
          <div class="field">
            <label>Fade Out Duration (ms)</label>
            <input type="number" name="intro_fade_out_duration" value="<?= (int)($script['intro']['fade_out_duration'] ?? 1100) ?>">
          </div>
          <div class="field">
            <label>Show Text Delay (ms)</label>
            <input type="number" name="intro_show_text_delay" value="<?= (int)($script['intro']['show_text_delay'] ?? 120) ?>">
          </div>
        </div>
        <div class="form-row mt-12">
          <div class="field">
            <label>Skip Click Delay (ms)</label>
            <input type="number" name="intro_skip_click_delay" value="<?= (int)($script['intro']['skip_click_delay'] ?? 300) ?>">
          </div>
          <div></div>
        </div>
      </div>

      <hr class="divider">

      <!-- Main/Particle Variables -->
      <div style="margin-bottom:16px">
        <div class="card-title" style="font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2);margin-bottom:12px">Partikel-System</div>
        <div class="form-row">
          <div class="field">
            <label>Partikelanzahl</label>
            <input type="number" name="main_particle_count" value="<?= (int)($script['main']['particle_count'] ?? 500) ?>">
          </div>
          <div class="field">
            <label>Max. Geschwindigkeit</label>
            <input type="number" name="main_particle_max_speed" step="0.01" value="<?= number_format((float)($script['main']['particle_max_speed'] ?? 0.45), 2) ?>">
          </div>
        </div>
        <div class="form-row mt-12">
          <div class="field">
            <label>Max. Linienabstand (px)</label>
            <input type="number" name="main_particle_max_line_dist" value="<?= (int)($script['main']['particle_max_line_dist'] ?? 90) ?>">
          </div>
          <div class="field">
            <label>Maus-Radius (px)</label>
            <input type="number" name="main_particle_mouse_radius" value="<?= (int)($script['main']['particle_mouse_radius'] ?? 120) ?>">
          </div>
        </div>
        <div class="form-row mt-12">
          <div class="field">
            <label>Maus-Kraft</label>
            <input type="number" name="main_particle_mouse_force" step="0.001" value="<?= number_format((float)($script['main']['particle_mouse_force'] ?? 0.012), 3) ?>">
          </div>
          <div></div>
        </div>
      </div>

      <hr class="divider">

      <!-- Hero Words & Animation -->
      <div style="margin-bottom:16px">
        <div class="card-title" style="font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2);margin-bottom:12px">Hero Animation</div>
        <?php
        $heroWordsArr = $script['main']['hero_words'] ?? [];
        $heroWordsStr = is_array($heroWordsArr) ? implode("\n", $heroWordsArr) : '';
        ?>
        <div class="field">
          <label>Hero Wörter (eines pro Zeile)</label>
          <textarea name="script_hero_words" rows="5"><?= he($heroWordsStr) ?></textarea>
        </div>
        <div class="form-row mt-12">
          <div class="field">
            <label>Fade Duration (ms)</label>
            <input type="number" name="main_hero_fade_duration" value="<?= (int)($script['main']['hero_fade_duration'] ?? 420) ?>">
          </div>
          <div class="field">
            <label>Hold Duration (ms)</label>
            <input type="number" name="main_hero_hold_duration" value="<?= (int)($script['main']['hero_hold_duration'] ?? 1900) ?>">
          </div>
        </div>
      </div>

      <hr class="divider">

      <!-- Count-Up & Stack -->
      <div>
        <div class="card-title" style="font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2);margin-bottom:12px">Count-Up & Stack</div>
        <div class="form-row">
          <div class="field">
            <label>Count-Up Duration (ms)</label>
            <input type="number" name="main_countup_duration" value="<?= (int)($script['main']['countup_duration'] ?? 1800) ?>">
          </div>
          <div class="field">
            <label>Stack Rotation (deg)</label>
            <input type="number" name="main_stack_rotation_amount" step="0.1" value="<?= number_format((float)($script['main']['stack_rotation_amount'] ?? 0.5), 1) ?>">
          </div>
        </div>
        <div class="form-row mt-12">
          <div class="field">
            <label>Stack Item Abstand (px)</label>
            <input type="number" name="main_stack_item_stack_dist" value="<?= (int)($script['main']['stack_item_stack_dist'] ?? 15) ?>">
          </div>
          <div class="field">
            <label>Tour Anzeige-Stil</label>
            <select name="main_tour_display_style">
              <option value="stack" <?= ($script['main']['tour_display_style'] ?? 'stack') === 'stack' ? 'selected' : '' ?>>Stack</option>
              <option value="cardswap" <?= ($script['main']['tour_display_style'] ?? 'stack') === 'cardswap' ? 'selected' : '' ?>>Card Swap</option>
            </select>
          </div>
        </div>
      </div>

      <div style="margin-top:20px">
        <button type="submit" class="btn btn-primary">Script-Variablen speichern</button>
      </div>
    </form>
  </div>

</div>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'Einstellungen';
$currentPage = 'settings';
include dirname(__DIR__) . '/partial/layout.php';
