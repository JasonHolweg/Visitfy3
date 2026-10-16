<?php
/**
 * Visitfy3 – ping.php (erreichbar als /ping, siehe .htaccess)
 * Nimmt Seitenaufrufe vom Browser entgegen (navigator.sendBeacon, Script in partials/footer.php).
 * Keine Cookies, keine IP-Speicherung – Details in partials/stats.php.
 */
require __DIR__ . '/partials/stats.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

$ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
if (visitfy_stats_is_bot($ua)) {
    http_response_code(204);
    exit;
}

$raw = (string)file_get_contents('php://input', false, null, 0, 2048);
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    exit;
}

$path = visitfy_stats_clean_path((string)($data['p'] ?? '/'));
$ref = substr((string)($data['r'] ?? ''), 0, 500);
$refHost = $ref !== '' ? strtolower((string)parse_url($ref, PHP_URL_HOST)) : '';
$ownHost = strtolower(preg_replace(['/^www\./', '/:\d+$/'], '', (string)($_SERVER['HTTP_HOST'] ?? '')));
$width = (int)($data['w'] ?? 0);

/* Interne Klicks zählen als Seitenaufruf, aber nicht als neue Quelle */
$internal = $refHost !== '' && preg_replace('/^www\./', '', $refHost) === $ownHost;
$source = $internal ? 'Intern' : visitfy_stats_source($refHost, (string)($data['u'] ?? ''));

visitfy_stats_append([
    'k' => 'pv',
    'p' => $path,
    's' => $source,
    'v' => visitfy_stats_visitor(),
    'd' => $width <= 0 ? '?' : ($width < 768 ? 'm' : ($width < 1100 ? 't' : 'd')),
]);

http_response_code(204);
