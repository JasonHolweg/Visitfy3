<?php
/**
 * Visitfy3 – pages/partner-handler.php
 * AJAX endpoint for the partner contact form (partner.php).
 * Returns JSON: {"ok":true} or {"ok":false,"error":"..."}
 */

require __DIR__ . '/../partials/cms.php';
require __DIR__ . '/../partials/mail.php';
require_once __DIR__ . '/../partials/turnstile.php';

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

/* Turnstile */
if (visitfy_turnstile_is_enabled()) {
    $token = (string)($_POST['cf-turnstile-response'] ?? '');
    if (!visitfy_turnstile_verify($token, (string)($_SERVER['REMOTE_ADDR'] ?? ''))) {
        echo json_encode(['ok' => false, 'error' => 'Bitte bestätigen Sie, dass Sie kein Roboter sind.']);
        exit;
    }
}

/* Sanitize & validate */
$name      = trim(strip_tags($_POST['name']      ?? ''));
$firma     = trim(strip_tags($_POST['firma']     ?? ''));
$email     = trim(strip_tags($_POST['email']     ?? ''));
$rolle     = trim(strip_tags($_POST['rolle']     ?? ''));
$nachricht = trim(strip_tags($_POST['nachricht'] ?? ''));
$dsgvo     = !empty($_POST['dsgvo']);

$allowedRollen = ['sales', 'agency', 'self-employed', 'other', ''];

if (empty($name) || strlen($name) > 120) {
    echo json_encode(['ok' => false, 'error' => 'Bitte geben Sie einen gültigen Namen an.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 200) {
    echo json_encode(['ok' => false, 'error' => 'Bitte geben Sie eine gültige E-Mail-Adresse an.']);
    exit;
}
if (empty($nachricht) || strlen($nachricht) > 3000) {
    echo json_encode(['ok' => false, 'error' => 'Bitte geben Sie eine Nachricht ein (max. 3000 Zeichen).']);
    exit;
}
if (!$dsgvo) {
    echo json_encode(['ok' => false, 'error' => 'Bitte akzeptieren Sie die Datenschutzerklärung.']);
    exit;
}
if (!in_array($rolle, $allowedRollen, true)) {
    echo json_encode(['ok' => false, 'error' => 'Ungültige Rollenauswahl.']);
    exit;
}
if (preg_match('/[\r\n]/', $email)) {
    echo json_encode(['ok' => false, 'error' => 'Ungültige E-Mail-Adresse.']);
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
$safeFirma     = str_replace(["\r", "\n"], '', $firma);
$safeEmail     = str_replace(["\r", "\n"], '', $email);
$safeRolle     = str_replace(["\r", "\n"], '', $rolle);

$subject = '[Visitfy Partner] Neue Anfrage von ' . $safeName;
$body = "Name: $safeName\n"
    . "Firma: $safeFirma\n"
    . "E-Mail: $safeEmail\n"
    . "Rolle: $safeRolle\n\n"
    . "Nachricht:\n$nachricht\n";

$escapedMessage = nl2br(htmlspecialchars($nachricht, ENT_QUOTES, 'UTF-8'));

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
    . $row('Firma',  htmlspecialchars($safeFirma !== '' ? $safeFirma : '—', ENT_QUOTES, 'UTF-8'))
    . $row('E-Mail', '<a href="mailto:' . htmlspecialchars($safeEmail, ENT_QUOTES, 'UTF-8') . '"'
        . ' style="color:#ffffff;">' . htmlspecialchars($safeEmail, ENT_QUOTES, 'UTF-8') . '</a>')
    . $row('Rolle',  htmlspecialchars($safeRolle !== '' ? $safeRolle : '—', ENT_QUOTES, 'UTF-8'))
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

echo json_encode(['ok' => true]);
