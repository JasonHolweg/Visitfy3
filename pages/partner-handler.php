<?php
/**
 * Visitfy3 – pages/partner-handler.php
 * AJAX endpoint for the partner contact form (partner.php).
 * Returns JSON: {"ok":true} or {"ok":false,"error":"..."}
 */

require __DIR__ . '/../partials/cms.php';
require __DIR__ . '/../partials/mail.php';
require_once __DIR__ . '/../partials/turnstile.php';
require_once __DIR__ . '/../partials/formguard.php';

header('Content-Type: application/json; charset=utf-8');

/* Only accept POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Ungültige Anfrage.']);
    exit;
}

/* Honeypot */
if (!empty($_POST['hp_website'])) {
    /* Silent accept for bots */
    echo json_encode(['ok' => true]);
    exit;
}

/* Formular-Token (partials/formguard.php): schneller als ein Mensch = Bot → still annehmen.
   Fehlt das Token oder passt es nicht (z. B. Seite vor einem Update geladen), Hinweis zum Neuladen. */
$guard = visitfy_formguard_check('partner', (string)($_POST['ft'] ?? ''));
if ($guard === 'too_fast') {
    echo json_encode(['ok' => true]);
    exit;
}
if ($guard !== 'ok') {
    echo json_encode(['ok' => false, 'error' => 'Die Seite war zu lange geöffnet. Bitte lade sie neu und schick das Formular noch einmal ab.']);
    exit;
}

/* Turnstile */
if (visitfy_turnstile_is_enabled()) {
    $token = (string)($_POST['cf-turnstile-response'] ?? '');
    if (!visitfy_turnstile_verify($token, (string)($_SERVER['REMOTE_ADDR'] ?? ''))) {
        echo json_encode(['ok' => false, 'error' => 'Bitte bestätigen Sie, dass Sie kein Roboter sind.']);
        exit;
    }
}

/* Sanitize & validate – nur Name, E-Mail und Stadt sind Pflicht */
$name      = trim(strip_tags($_POST['name']      ?? ''));
$email     = trim(strip_tags($_POST['email']     ?? ''));
$stadt     = trim(strip_tags($_POST['stadt']     ?? ''));
$nachricht = trim(strip_tags($_POST['nachricht'] ?? ''));
$dsgvo     = !empty($_POST['dsgvo']);

if ($name === '' || mb_strlen($name) > 120) {
    echo json_encode(['ok' => false, 'error' => 'Bitte gib deinen Namen an.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 200 || preg_match('/[\r\n]/', $email)) {
    echo json_encode(['ok' => false, 'error' => 'Bitte gib eine gültige E-Mail-Adresse an.']);
    exit;
}
if ($stadt === '' || mb_strlen($stadt) > 120) {
    echo json_encode(['ok' => false, 'error' => 'Bitte gib deine Stadt oder Region an.']);
    exit;
}
if (mb_strlen($nachricht) > 3000) {
    echo json_encode(['ok' => false, 'error' => 'Deine Nachricht ist zu lang (max. 3.000 Zeichen).']);
    exit;
}
if (!$dsgvo) {
    echo json_encode(['ok' => false, 'error' => 'Bitte bestätige die Datenschutzerklärung.']);
    exit;
}

/* Typischer Link-Spam → still annehmen, nichts senden */
if (visitfy_formguard_is_spam($nachricht, [$name, $stadt])) {
    echo json_encode(['ok' => true]);
    exit;
}

/* Höchstens 3 Anfragen pro Stunde vom selben Absender */
if (!visitfy_formguard_rate('partner')) {
    echo json_encode(['ok' => false, 'error' => 'Du hast uns gerade schon geschrieben – wir melden uns bei dir. Eilt es? Ruf uns an: +49 1520 6955260.']);
    exit;
}

/* Load mail config */
$mailConfig = visitfy_load_mail_config();
$contactEmail = (string)($mailConfig['MAILGUN_TO_EMAIL'] ?? 'info@visitfy.de');
if ($contactEmail === '') $contactEmail = 'info@visitfy.de';

if (!visitfy_mail_is_configured($mailConfig)) {
    echo json_encode(['ok' => false, 'error' => 'Das Kontaktformular ist im Moment noch nicht vollständig eingerichtet.']);
    exit;
}

/* Sanitize for email */
$safeName      = str_replace(["\r", "\n"], '', $name);
$safeEmail     = str_replace(["\r", "\n"], '', $email);
$safeStadt     = str_replace(["\r", "\n"], '', $stadt);

$subject = '[Visitfy Partner] Neue Anfrage von ' . $safeName . ' (' . $safeStadt . ')';
$body = "Name: $safeName\n"
    . "E-Mail: $safeEmail\n"
    . "Stadt / Region: $safeStadt\n\n"
    . "Nachricht:\n" . ($nachricht !== '' ? $nachricht : '—') . "\n";

$escapedMessage = $nachricht !== '' ? nl2br(htmlspecialchars($nachricht, ENT_QUOTES, 'UTF-8')) : '—';

$row = static function (string $label, string $value): string {
    return '<tr>'
        . '<td style="padding:10px 16px 10px 0;font-size:13px;font-weight:700;letter-spacing:0.06em;'
        .   'text-transform:uppercase;color:rgba(255,255,255,0.4);white-space:nowrap;vertical-align:top;">'
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td>'
        . '<td style="padding:10px 0;font-size:15px;color:#ffffff;vertical-align:top;">'
        . $value . '</td>'
        . '</tr>';
};

$inner =
    '<p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:0.15em;'
    .   'text-transform:uppercase;color:rgba(255,255,255,0.4);">Partner-Anfrage</p>'
    . '<h1 style="margin:0 0 32px;font-size:24px;font-weight:700;color:#ffffff;line-height:1.3;">'
    . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . ' möchte Partner werden.</h1>'
    . '<table cellpadding="0" cellspacing="0" role="presentation" width="100%"'
    .   ' style="border-collapse:collapse;border-top:1px solid rgba(255,255,255,0.08);">'
    . $row('Name',   htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8'))
    . $row('E-Mail', '<a href="mailto:' . htmlspecialchars($safeEmail, ENT_QUOTES, 'UTF-8') . '"'
        . ' style="color:#ffffff;">' . htmlspecialchars($safeEmail, ENT_QUOTES, 'UTF-8') . '</a>')
    . $row('Region', htmlspecialchars($safeStadt, ENT_QUOTES, 'UTF-8'))
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

$footer = '<p style="margin:0;font-size:12px;color:rgba(255,255,255,0.28);line-height:1.6;">'
    . 'Visitfy Admin &middot; Diese E-Mail wurde automatisch generiert.</p>';

$html = visitfy_email_wrap($inner, $footer);

$payload = [
    'from'        => visitfy_format_from_header((string)$mailConfig['MAILGUN_FROM_NAME'], (string)$mailConfig['MAILGUN_FROM_EMAIL']),
    'to'          => $contactEmail,
    'subject'     => $subject,
    'text'        => $body,
    'html'        => $html,
    'h:Reply-To'  => $safeEmail,
];

$result = visitfy_send_contact_mail($mailConfig, $payload);

if (!$result['ok']) {
    error_log('Visitfy partner mail failed: ' . ($result['error'] ?? ''));
    echo json_encode(['ok' => false, 'error' => 'Ihre Anfrage konnte gerade nicht gesendet werden. Bitte versuchen Sie es erneut.']);
    exit;
}

/* Für die Begrenzung pro Absender zählen (nur erfolgreiche Anfragen) */
visitfy_formguard_rate('partner', true);

/* Statistik: erfolgreiche Partner-Bewerbung zählen (cookielos, partials/stats.php) */
require_once __DIR__ . '/../partials/stats.php';
visitfy_stats_event('lead_partner');

echo json_encode(['ok' => true]);
