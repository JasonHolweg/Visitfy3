<?php
/**
 * Visitfy3 – tools/indexnow.php
 * Meldet geänderte URLs an IndexNow (Bing, Yandex, Seznam, Naver …; ChatGPT-Suche nutzt u. a. den Bing-Index).
 * Google nutzt IndexNow nicht – dort reicht die Sitemap in der Search Console.
 *
 * Aufruf auf dem Server:
 *   php tools/indexnow.php                      → alle Seiten aus partials/routes.php
 *   php tools/indexnow.php /partner-werden /faq → nur diese Pfade
 *
 * Der Schlüssel liegt öffentlich als /<schlüssel>.txt im Webroot (so verlangt es IndexNow).
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../partials/cms.php';

const INDEXNOW_KEY = '9b6ffc0287d006ba0dacce612eec12e7';

$urls = [];
foreach (array_slice($argv, 1) as $arg) {
    $urls[] = preg_match('#^https?://#', $arg) ? $arg : VISITFY_SITE_URL . '/' . ltrim($arg, '/');
}
if (!$urls) {
    foreach (visitfy_routes() as $key => $route) {
        if (($route['sitemap'] ?? true) !== false) {
            $urls[] = visitfy_page_url($key, true);
        }
    }
}

$payload = json_encode([
    'host'        => parse_url(VISITFY_SITE_URL, PHP_URL_HOST),
    'key'         => INDEXNOW_KEY,
    'keyLocation' => VISITFY_SITE_URL . '/' . INDEXNOW_KEY . '.txt',
    'urlList'     => array_values(array_unique($urls)),
], JSON_UNESCAPED_SLASHES);

$ch = curl_init('https://api.indexnow.org/indexnow');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
]);
$body = curl_exec($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

/* 200 = angenommen, 202 = angenommen (Schlüssel wird noch geprüft) */
if ($code === 200 || $code === 202) {
    echo 'IndexNow: ' . count(json_decode($payload, true)['urlList']) . " URL(s) gemeldet (HTTP $code)\n";
    exit(0);
}
fwrite(STDERR, "IndexNow fehlgeschlagen: HTTP $code $err " . substr((string)$body, 0, 300) . "\n");
exit(1);
