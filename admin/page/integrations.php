<?php
/**
 * Visitfy Admin – Integrations (Mailgun)
 */
require dirname(__DIR__) . '/bootstrap.php';
admin_require_login();

$notice = '';
$error = '';
$csrf = admin_csrf_token();
$mailTestResult = null;

function he(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$mailSettings = admin_read_mail_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_validate_csrf($_POST['csrf'] ?? null)) {
    $action = (string)($_POST['action'] ?? '');

    // ── Save Mailgun settings ────────────────────────────────────
    if ($action === 'save_mailgun') {
        $apiKey   = trim((string)($_POST['mailgun_api_key'] ?? ''));
        $domain   = trim((string)($_POST['mailgun_domain'] ?? ''));
        $apiBase  = trim((string)($_POST['mailgun_api_base'] ?? 'https://api.mailgun.net'));
        $fromEmail = trim((string)($_POST['mailgun_from_email'] ?? ''));
        $fromName  = trim((string)($_POST['mailgun_from_name'] ?? ''));
        $toEmail   = trim((string)($_POST['mailgun_to_email'] ?? ''));

        if ($apiBase === '') $apiBase = 'https://api.mailgun.net';

        if ($domain !== '' && !preg_match('/^[a-z0-9.-]+$/i', $domain)) {
            $error = 'Mailgun-Domain ist ungültig.';
        } elseif (!filter_var($apiBase, FILTER_VALIDATE_URL)) {
            $error = 'API Base-URL ist ungültig.';
        } elseif ($fromEmail !== '' && !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Absender-E-Mail ist ungültig.';
        } elseif ($toEmail !== '' && !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Empfänger-E-Mail ist ungültig.';
        } elseif (!admin_write_mail_settings([
            'MAILGUN_API_KEY'   => $apiKey,
            'MAILGUN_DOMAIN'    => $domain,
            'MAILGUN_API_BASE'  => $apiBase,
            'MAILGUN_FROM_EMAIL'=> $fromEmail,
            'MAILGUN_FROM_NAME' => $fromName,
            'MAILGUN_TO_EMAIL'  => $toEmail,
        ])) {
            $error = 'Konfiguration konnte nicht gespeichert werden.';
        } else {
            $notice = 'Mailgun-Konfiguration gespeichert.';
        }
    }

    // ── Test Mailgun ─────────────────────────────────────────────
    if ($action === 'test_mailgun') {
        $mailPartial = dirname(dirname(__DIR__)) . '/partials/mail.php';
        if (!is_file($mailPartial)) {
            $mailTestResult = ['ok' => false, 'msg' => 'Mailgun-Hilfsdatei (partials/mail.php) nicht gefunden.'];
        } else {
            require_once $mailPartial;
            $cfg = admin_read_mail_settings();
            if (!function_exists('visitfy_mail_is_configured') || !visitfy_mail_is_configured($cfg)) {
                $mailTestResult = ['ok' => false, 'msg' => 'Mailgun nicht konfiguriert (API-Key oder Domain fehlt).'];
            } else {
                $testTo = trim((string)($cfg['MAILGUN_TO_EMAIL']));
                $payload = [
                    'from'    => function_exists('visitfy_format_from_header')
                        ? visitfy_format_from_header((string)$cfg['MAILGUN_FROM_NAME'], (string)$cfg['MAILGUN_FROM_EMAIL'])
                        : $cfg['MAILGUN_FROM_EMAIL'],
                    'to'      => $testTo,
                    'subject' => '[Visitfy] Mailgun Test',
                    'text'    => 'Dies ist eine Testmail vom Visitfy Admin-Panel. Mailgun ist korrekt konfiguriert.',
                ];
                $result = visitfy_send_via_mailgun($cfg, $payload);
                if ($result['ok']) {
                    $mailTestResult = ['ok' => true, 'msg' => 'Testmail erfolgreich an ' . $testTo . ' gesendet.'];
                } else {
                    $mailTestResult = ['ok' => false, 'msg' => 'Fehler: ' . ($result['error'] ?? 'Unbekannt')];
                }
            }
        }
    }

    // Reload settings after save
    $mailSettings = admin_read_mail_settings();
}

$mailgunOk = ($mailSettings['MAILGUN_API_KEY'] ?? '') !== '' && ($mailSettings['MAILGUN_DOMAIN'] ?? '') !== '';

ob_start();
?>
<div class="topbar">
  <div class="topbar-title">Integrationen</div>
  <div class="topbar-actions">
    <?php if ($mailgunOk): ?>
    <span class="badge badge-green">Mailgun aktiv</span>
    <?php else: ?>
    <span class="badge badge-red">Mailgun nicht konfiguriert</span>
    <?php endif; ?>
  </div>
</div>

<div class="page-body">

  <!-- Mailgun Card -->
  <div class="card">
    <div class="card-header">
      <div class="card-header-left">
        <div class="card-title">Mailgun E-Mail-Integration</div>
        <div class="card-desc" style="margin-bottom:0">
          Konfiguriere den Mailgun-Service für Kontakt- und Partnerformulare.
        </div>
      </div>
      <div>
        <?php if ($mailgunOk): ?>
        <span class="badge badge-green">Konfiguriert</span>
        <?php else: ?>
        <span class="badge badge-red">Nicht konfiguriert</span>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($mailTestResult !== null): ?>
    <div class="<?= $mailTestResult['ok'] ? 'msg-ok' : 'msg-err' ?>" style="margin-bottom:16px">
      <?= he($mailTestResult['msg']) ?>
    </div>
    <?php endif; ?>

    <form id="mailgun-form" method="post" action="?p=integrations">
      <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
      <input type="hidden" name="action" value="save_mailgun">

      <div class="form-row">
        <div class="field">
          <label>API Key</label>
          <input type="password" name="mailgun_api_key"
            value="<?= he($mailSettings['MAILGUN_API_KEY'] ?? '') ?>"
            placeholder="key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
            autocomplete="new-password">
        </div>
        <div class="field">
          <label>Domain</label>
          <input type="text" name="mailgun_domain"
            value="<?= he($mailSettings['MAILGUN_DOMAIN'] ?? '') ?>"
            placeholder="mail.deinedomain.de">
        </div>
      </div>

      <div class="field mt-12">
        <label>API Base URL</label>
        <input type="url" name="mailgun_api_base"
          value="<?= he($mailSettings['MAILGUN_API_BASE'] ?? 'https://api.mailgun.net') ?>"
          placeholder="https://api.mailgun.net">
        <span style="font-size:11px;color:var(--text-2);margin-top:4px">
          EU-Nutzer: <code style="font-family:monospace">https://api.eu.mailgun.net</code>
        </span>
      </div>

      <hr class="divider">

      <div class="form-row">
        <div class="field">
          <label>Absender Name</label>
          <input type="text" name="mailgun_from_name"
            value="<?= he($mailSettings['MAILGUN_FROM_NAME'] ?? 'Visitfy') ?>"
            placeholder="Visitfy">
        </div>
        <div class="field">
          <label>Absender E-Mail</label>
          <input type="email" name="mailgun_from_email"
            value="<?= he($mailSettings['MAILGUN_FROM_EMAIL'] ?? '') ?>"
            placeholder="info@visitfy.de">
        </div>
      </div>

      <div class="field mt-12">
        <label>Empfänger E-Mail (Kontaktformular-Ziel)</label>
        <input type="email" name="mailgun_to_email"
          value="<?= he($mailSettings['MAILGUN_TO_EMAIL'] ?? '') ?>"
          placeholder="info@visitfy.de">
      </div>

      <div style="display:flex;gap:10px;margin-top:20px;align-items:center">
        <button type="submit" class="btn btn-primary">Einstellungen speichern</button>
        <span style="color:var(--text-3);font-size:12px">oder</span>
      </div>
    </form>

    <hr class="divider">

    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
      <div>
        <div style="font-size:13px;font-weight:600;margin-bottom:4px">Mailgun-Verbindung testen</div>
        <div style="font-size:12px;color:var(--text-2)">Sendet eine Testmail an die konfigurierte Empfängeradresse.</div>
      </div>
      <form method="post" action="?p=integrations">
        <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
        <input type="hidden" name="action" value="test_mailgun">
        <button type="submit" class="btn btn-secondary" <?= !$mailgunOk ? 'disabled style="opacity:0.4;cursor:not-allowed"' : '' ?>>
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" style="margin-right:4px">
            <path d="M2 2l12 6-12 6V9l8-1-8-1V2z"/>
          </svg>
          Testmail senden
        </button>
      </form>
    </div>
  </div>

  <!-- Info Card -->
  <div class="card mt-16" style="margin-top:16px">
    <div class="card-header">
      <div class="card-title">Einrichtungshinweise</div>
    </div>
    <div style="font-size:13px;color:var(--text-2);line-height:1.7">
      <p style="margin-bottom:8px">
        <strong style="color:var(--text)">1. Mailgun-Konto:</strong>
        Registriere dich auf <a href="https://app.mailgun.com" target="_blank" style="color:var(--text);text-decoration:underline">app.mailgun.com</a> und füge deine Domain hinzu.
      </p>
      <p style="margin-bottom:8px">
        <strong style="color:var(--text)">2. API Key:</strong>
        Zu finden unter <em>Settings → API Keys</em> im Mailgun-Dashboard. Beginnt mit <code style="font-family:monospace;font-size:11px">key-</code>.
      </p>
      <p style="margin-bottom:8px">
        <strong style="color:var(--text)">3. Domain:</strong>
        Die verifizierte Domain (z.B. <code style="font-family:monospace;font-size:11px">mg.visitfy.de</code>), nicht die Webseiten-Domain.
      </p>
      <p>
        <strong style="color:var(--text)">4. EU-Nutzer:</strong>
        Wenn deine Domain in der EU-Region registriert ist, ändere die API Base URL auf <code style="font-family:monospace;font-size:11px">https://api.eu.mailgun.net</code>.
      </p>
    </div>
  </div>

</div>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'Integrationen';
$currentPage = 'integrations';
include dirname(__DIR__) . '/partial/layout.php';
