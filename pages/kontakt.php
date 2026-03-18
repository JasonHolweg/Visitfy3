<?php
/**
 * Visitfy3 – pages/kontakt.php
 * Contact page with form, server-side validation, honeypot, CSRF, DSGVO checkbox.
 */
require __DIR__ . '/../partials/cms.php';
require __DIR__ . '/../partials/mail.php';
require_once __DIR__ . '/../partials/turnstile.php';

/* ── Session for CSRF token ────────────────────────────── */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? visitfy_base_path() : '../';
$contentConfig = visitfy_load_json(__DIR__ . '/../assets/data/content.json', []);
$pageTitle = 'Kontakt | Visitfy – 360° Rundgänge anfragen';
$pageDesc  = 'Kontaktieren Sie Visitfy für ein unverbindliches Angebot für Ihren 360° Rundgang. Nutzen Sie unser Kontaktformular oder schreiben Sie direkt.';

$contactEmail = trim((string)visitfy_get($contentConfig, 'kontakt_text.email', visitfy_get($contentConfig, 'footer.contact_email', 'info@visitfy.de')));
if ($contactEmail === '') {
    $contactEmail = 'info@visitfy.de';
}

$mailConfig = visitfy_load_mail_config();
if (($mailConfig['MAILGUN_FROM_EMAIL'] ?? '') === '') {
    $mailConfig['MAILGUN_FROM_EMAIL'] = $contactEmail;
}
if (($mailConfig['MAILGUN_TO_EMAIL'] ?? '') === '') {
    $mailConfig['MAILGUN_TO_EMAIL'] = $contactEmail;
}
if (($mailConfig['MAILGUN_FROM_NAME'] ?? '') === '') {
    $mailConfig['MAILGUN_FROM_NAME'] = 'Visitfy';
}

/* ── Server-side form handling ─────────────────────────── */
$formSent  = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* CSRF check */
    $csrfToken = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $csrfToken)) {
        $formError = 'Ungültige Anfrage. Bitte laden Sie die Seite neu.';
    /* Honeypot check */
    } elseif (!empty($_POST['hp_website'])) {
        /* Silent reject */
        $formSent = true;
    /* Turnstile check */
    } elseif (visitfy_turnstile_is_enabled() && !visitfy_turnstile_verify(
        (string)($_POST['cf-turnstile-response'] ?? ''),
        (string)($_SERVER['REMOTE_ADDR'] ?? '')
    )) {
        $formError = 'Bitte bestätigen Sie, dass Sie kein Roboter sind.';
    } else {
        /* Sanitize & validate */
        $name     = trim(strip_tags($_POST['name']     ?? ''));
        $firma    = trim(strip_tags($_POST['firma']    ?? ''));
        $email    = trim(strip_tags($_POST['email']    ?? ''));
        $telefon  = trim(strip_tags($_POST['telefon']  ?? ''));
        $branche  = trim(strip_tags($_POST['branche']  ?? ''));
        $nachricht= trim(strip_tags($_POST['nachricht']?? ''));
        $dsgvo    = isset($_POST['dsgvo']) ? (bool)$_POST['dsgvo'] : false;

        $allowedBranchen = ['gastronomie','hotel','immobilien','einzelhandel','praxis','fitness','sonstiges',''];

        if (empty($name) || strlen($name) > 120) {
            $formError = 'Bitte geben Sie einen gültigen Namen an.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 200) {
            $formError = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
        } elseif (strlen($telefon) > 40) {
            $formError = 'Die Telefonnummer ist zu lang.';
        } elseif (empty($nachricht) || strlen($nachricht) > 3000) {
            $formError = 'Bitte geben Sie eine Nachricht ein (max. 3000 Zeichen).';
        } elseif (!$dsgvo) {
            $formError = 'Bitte akzeptieren Sie die Datenschutzerklärung.';
        } elseif (!in_array($branche, $allowedBranchen, true)) {
            $formError = 'Ungültige Branchenauswahl.';
        } elseif (preg_match('/[\r\n]/', $email)) {
            $formError = 'Ungültige E-Mail-Adresse.';
        } elseif (!visitfy_mail_is_configured($mailConfig)) {
            $formError = 'Das Kontaktformular ist im Moment noch nicht vollständig eingerichtet. Bitte schreiben Sie uns direkt an ' . $contactEmail . '.';
        } else {
            /* Strip CRLF from all user fields to prevent email header/body injection */
            $safeSubjectName = str_replace(["\r", "\n"], '', $name);
            $safeName        = str_replace(["\r", "\n"], '', $name);
            $safeFirma       = str_replace(["\r", "\n"], '', $firma);
            $safeEmail       = str_replace(["\r", "\n"], '', $email);
            $safeTelefon     = str_replace(["\r", "\n"], '', $telefon);
            $safeBranche     = str_replace(["\r", "\n"], '', $branche);
            $subject = '[Visitfy] Neue Anfrage von ' . $safeSubjectName;
            $body = "Name: $safeName\n"
                . "Firma: $safeFirma\n"
                . "E-Mail: $safeEmail\n"
                . "Telefon: $safeTelefon\n"
                . "Branche: $safeBranche\n\n"
                . "Nachricht:\n$nachricht\n";

            $escapedMessage = nl2br(htmlspecialchars($nachricht, ENT_QUOTES, 'UTF-8'));

            /* ── Admin notification email ── */
            $row = static function (string $label, string $value): string {
                return '<tr>'
                    . '<td style="padding:10px 16px 10px 0;font-size:13px;font-weight:700;letter-spacing:0.06em;'
                    .   'text-transform:uppercase;color:rgba(255,255,255,0.4);white-space:nowrap;vertical-align:top;">'
                    . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td>'
                    . '<td style="padding:10px 0;font-size:15px;color:#ffffff;vertical-align:top;">'
                    . $value . '</td>'
                    . '</tr>';
            };
            $adminInner =
                '<p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:0.15em;'
                .   'text-transform:uppercase;color:rgba(255,255,255,0.4);">Neue Anfrage</p>'
                . '<h1 style="margin:0 0 32px;font-size:24px;font-weight:700;color:#ffffff;line-height:1.3;">'
                . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . ' hat eine Anfrage gesendet.</h1>'
                . '<table cellpadding="0" cellspacing="0" role="presentation" width="100%"'
                .   ' style="border-collapse:collapse;border-top:1px solid rgba(255,255,255,0.08);">'
                . $row('Name',    htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8'))
                . $row('Firma',   htmlspecialchars($safeFirma !== '' ? $safeFirma : '—', ENT_QUOTES, 'UTF-8'))
                . $row('E-Mail',  '<a href="mailto:' . htmlspecialchars($safeEmail, ENT_QUOTES, 'UTF-8') . '"'
                    . ' style="color:#ffffff;">' . htmlspecialchars($safeEmail, ENT_QUOTES, 'UTF-8') . '</a>')
                . $row('Telefon', htmlspecialchars($safeTelefon !== '' ? $safeTelefon : '—', ENT_QUOTES, 'UTF-8'))
                . $row('Branche', htmlspecialchars($safeBranche !== '' ? $safeBranche : '—', ENT_QUOTES, 'UTF-8'))
                . '</table>'
                . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation">'
                . '<tr><td style="height:1px;background-color:rgba(255,255,255,0.08);padding:0;font-size:0;line-height:0;">&nbsp;</td></tr>'
                . '</table>'
                . '<p style="margin:24px 0 8px;font-size:11px;font-weight:700;letter-spacing:0.15em;'
                .   'text-transform:uppercase;color:rgba(255,255,255,0.4);">Nachricht</p>'
                . '<p style="margin:0;font-size:15px;color:rgba(255,255,255,0.85);line-height:1.7;">'
                . $escapedMessage . '</p>'
                . '<p style="margin:32px 0 0;">'
                . '<a href="mailto:' . htmlspecialchars($safeEmail, ENT_QUOTES, 'UTF-8') . '"'
                .   ' style="display:inline-block;padding:12px 28px;background-color:#ffffff;color:#000000;'
                .   'font-size:14px;font-weight:700;text-decoration:none;border-radius:10px;">Antworten</a>'
                . '</p>';

            $adminFooter = '<p style="margin:0;font-size:12px;color:rgba(255,255,255,0.28);line-height:1.6;">'
                . 'Visitfy Admin &middot; Diese E-Mail wurde automatisch generiert.</p>';

            $adminHtml = visitfy_email_wrap($adminInner, $adminFooter);

            $adminPayload = [
                'from' => visitfy_format_from_header((string)$mailConfig['MAILGUN_FROM_NAME'], (string)$mailConfig['MAILGUN_FROM_EMAIL']),
                'to' => (string)$mailConfig['MAILGUN_TO_EMAIL'],
                'subject' => $subject,
                'text' => $body,
                'html' => $adminHtml,
                'h:Reply-To' => $safeEmail,
            ];

            $sendResult = visitfy_send_contact_mail($mailConfig, $adminPayload);

            if (!$sendResult['ok']) {
                error_log('Visitfy contact mail failed: ' . $sendResult['error']);
                $formError = 'Ihre Anfrage konnte gerade nicht gesendet werden. Bitte versuchen Sie es erneut oder schreiben Sie direkt an ' . $contactEmail . '.';
            } else {
                $safeName_html = htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8');
                $confirmInner =
                    '<p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:0.15em;'
                    .   'text-transform:uppercase;color:rgba(255,255,255,0.4);">Bestätigung</p>'
                    . '<h1 style="margin:0 0 28px;font-size:24px;font-weight:700;color:#ffffff;line-height:1.3;">'
                    . 'Ihre Anfrage ist eingegangen.</h1>'
                    . '<p style="margin:0 0 16px;font-size:16px;color:rgba(255,255,255,0.75);line-height:1.7;">'
                    . 'Hallo ' . $safeName_html . ',</p>'
                    . '<p style="margin:0 0 16px;font-size:16px;color:rgba(255,255,255,0.75);line-height:1.7;">'
                    . 'vielen Dank für Ihre Anfrage! Wir haben Ihre Nachricht erhalten und melden uns '
                    . '<strong style="color:#ffffff;">in Kürze</strong> bei Ihnen.</p>'
                    . '<p style="margin:0 0 36px;font-size:16px;color:rgba(255,255,255,0.75);line-height:1.7;">'
                    . 'In der Regel antworten wir innerhalb von <strong style="color:#ffffff;">24 Stunden</strong> '
                    . 'an Werktagen.</p>'
                    . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation">'
                    . '<tr><td style="height:1px;background-color:rgba(255,255,255,0.08);font-size:0;line-height:0;">&nbsp;</td></tr>'
                    . '</table>'
                    . '<p style="margin:28px 0 4px;font-size:15px;color:rgba(255,255,255,0.5);">Viele Grüße</p>'
                    . '<p style="margin:0;font-size:16px;font-weight:700;color:#ffffff;">Das Visitfy Team</p>';

                $confirmFooter =
                    '<p style="margin:0;font-size:12px;color:rgba(255,255,255,0.28);line-height:1.6;">'
                    . 'Visitfy &middot; Flensburg, Deutschland &middot; '
                    . '<a href="mailto:' . htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') . '"'
                    .   ' style="color:rgba(255,255,255,0.4);text-decoration:none;">'
                    . htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') . '</a></p>'
                    . '<p style="margin:6px 0 0;font-size:11px;color:rgba(255,255,255,0.18);">'
                    . 'Sie erhalten diese E-Mail, weil Sie das Kontaktformular auf visitfy.de ausgefüllt haben.</p>';

                $confirmHtml = visitfy_email_wrap($confirmInner, $confirmFooter);

                $confirmationPayload = [
                    'from' => visitfy_format_from_header((string)$mailConfig['MAILGUN_FROM_NAME'], (string)$mailConfig['MAILGUN_FROM_EMAIL']),
                    'to' => $safeEmail,
                    'subject' => 'Ihre Anfrage bei Visitfy ist eingegangen',
                    'text' => "Hallo $safeName,\n\nvielen Dank für Ihre Anfrage bei Visitfy! Wir haben Ihre Nachricht erhalten und melden uns in Kürze bei Ihnen.\n\nIn der Regel antworten wir innerhalb von 24 Stunden an Werktagen.\n\nViele Grüße\nDas Visitfy Team",
                    'html' => $confirmHtml,
                    'h:Reply-To' => (string)$mailConfig['MAILGUN_TO_EMAIL'],
                ];

                $confirmationResult = visitfy_send_contact_mail($mailConfig, $confirmationPayload);
                if (!$confirmationResult['ok']) {
                    error_log('Visitfy confirmation mail failed: ' . $confirmationResult['error']);
                }

                /* Save inquiry to JSON if user has given cookie consent */
                if (($_COOKIE['visitfy_consent'] ?? '') === '1') {
                    $inquiriesPath = __DIR__ . '/../assets/data/inquiries.json';
                    $inquiries = visitfy_load_json($inquiriesPath, []);
                    $maxId = 0;
                    foreach ($inquiries as $inq) { if (isset($inq['id']) && (int)$inq['id'] > $maxId) $maxId = (int)$inq['id']; }
                    $inquiries[] = [
                        'id'        => $maxId + 1,
                        'timestamp' => date('c'),
                        'date'      => date('d.m.Y H:i'),
                        'name'      => $safeName,
                        'firma'     => $safeFirma,
                        'email'     => $safeEmail,
                        'telefon'   => $safeTelefon,
                        'branche'   => $safeBranche,
                        'nachricht' => $nachricht,
                        'source'    => 'kontakt',
                    ];
                    $encoded = json_encode($inquiries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                    if (is_string($encoded)) {
                        file_put_contents($inquiriesPath, $encoded . PHP_EOL, LOCK_EX);
                    }
                }

                $formSent = true;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
            }
        }
    }
}

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content">

  <section class="page-hero">
    <div class="container">
      <p class="section-eyebrow fade-up"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.eyebrow', 'Kontakt'), ENT_QUOTES, 'UTF-8') ?></p>
      <h1 class="fade-up delay-1"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.title', 'Angebot anfragen'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="fade-up delay-2">
        <?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.sub', 'Unverbindlich, persönlich und kostenlos. Wir melden uns innerhalb von 24 Stunden.'), ENT_QUOTES, 'UTF-8') ?>
      </p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="contact-grid">

        <!-- Contact info -->
        <div class="contact-info fade-up">
          <h3><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.sidebar_heading', 'So erreichen Sie uns'), ENT_QUOTES, 'UTF-8') ?></h3>
          <p>
            <?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.sidebar_text', 'Nutzen Sie das Formular für eine schnelle Anfrage – oder schreiben Sie uns direkt per E-Mail.'), ENT_QUOTES, 'UTF-8') ?>
          </p>
          <p style="margin-top:1.5rem">
            <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?></a>
          </p>
          <div style="margin-top:2.5rem">
            <p style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.response_label', 'Antwortzeit'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.response_text', 'In der Regel innerhalb von 24 Stunden an Werktagen.'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div style="margin-top:2rem">
            <p style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem"><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.location_label', 'Standort'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars((string)visitfy_get($contentConfig, 'kontakt_text.location_text', 'Flensburg, Deutschland'), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>

        <!-- Form -->
        <div class="contact-form-box fade-up delay-2">
<?php if ($formSent): ?>
          <div class="form-status success" role="alert" style="display:block">
            ✓ Vielen Dank! Ihre Anfrage ist eingegangen. Wir melden uns bald.
          </div>
<?php elseif ($formError): ?>
          <div class="form-status error" role="alert" style="display:block">
            <?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?>
          </div>
<?php endif; ?>

          <form method="post" action="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/kontakt.php" novalidate>
            <!-- CSRF token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <!-- Honeypot -->
            <div class="form-honeypot" aria-hidden="true">
              <label for="hp_website">Website</label>
              <input type="text" id="hp_website" name="hp_website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="k_name">Name *</label>
                <input type="text" id="k_name" name="name" required autocomplete="name"
                       value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Max Mustermann">
              </div>
              <div class="form-group">
                <label for="k_firma">Firma</label>
                <input type="text" id="k_firma" name="firma" autocomplete="organization"
                       value="<?= htmlspecialchars($_POST['firma'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Muster GmbH">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="k_email">E-Mail *</label>
                <input type="email" id="k_email" name="email" required autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="name@firma.de">
              </div>
              <div class="form-group">
                <label for="k_telefon">Telefon (optional)</label>
                <input type="tel" id="k_telefon" name="telefon" autocomplete="tel"
                       value="<?= htmlspecialchars($_POST['telefon'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="+49 …">
              </div>
            </div>

            <div class="form-group">
              <label for="k_branche">Branche</label>
              <select id="k_branche" name="branche">
                <option value="">Bitte wählen…</option>
                <?php
                $branchen = [
                    'gastronomie' => 'Gastronomie (Restaurant, Café, Bar)',
                    'hotel'       => 'Hotel &amp; Wellness',
                    'immobilien'  => 'Immobilien',
                    'einzelhandel'=> 'Einzelhandel &amp; Showroom',
                    'praxis'      => 'Praxis &amp; Medizin',
                    'fitness'     => 'Fitness &amp; Sport',
                    'sonstiges'   => 'Sonstiges',
                ];
                $selBranche = $_POST['branche'] ?? '';
                foreach ($branchen as $val => $label):
                    $sel = ($selBranche === $val) ? ' selected' : '';
                ?>
                <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"<?= $sel ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="k_nachricht">Nachricht *</label>
              <textarea id="k_nachricht" name="nachricht" required rows="5"
                        placeholder="Beschreiben Sie kurz Ihre Location und was Sie sich vorstellen…"><?= htmlspecialchars($_POST['nachricht'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-check">
              <input type="checkbox" id="k_dsgvo" name="dsgvo" required<?= !empty($_POST['dsgvo']) ? ' checked' : '' ?>>
              <label for="k_dsgvo">
                Ich habe die <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/datenschutz.php">Datenschutzerklärung</a> gelesen und bin mit der
                Verarbeitung meiner Daten zur Bearbeitung meiner Anfrage einverstanden. *
              </label>
            </div>

            <?= visitfy_turnstile_widget() ?>
            <button type="submit" class="btn btn-primary js-btnfx-kontakt" style="width:100%">Anfrage absenden</button>
          </form>
        </div>

      </div><!-- /.contact-grid -->
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
