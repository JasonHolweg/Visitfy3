<?php
/**
 * Visitfy3 – sitemap.php (erreichbar als /sitemap.xml, siehe .htaccess)
 * Wird aus partials/routes.php erzeugt; <lastmod> = letzte Änderung der Seite bzw. ihrer Daten.
 */
require __DIR__ . '/partials/cms.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach (visitfy_routes() as $key => $route) {
    if (($route['sitemap'] ?? true) === false) {
        continue;
    }
    $mtime = 0;
    foreach (array_merge([$route['file']], $route['deps'] ?? []) as $file) {
        $abs = __DIR__ . '/' . $file;
        if (is_file($abs)) {
            $mtime = max($mtime, (int)filemtime($abs));
        }
    }
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars(visitfy_page_url($key, true), ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    if ($mtime > 0) {
        echo '    <lastmod>' . gmdate('Y-m-d\TH:i:s+00:00', $mtime) . "</lastmod>\n";
    }
    echo "  </url>\n";
}
echo "</urlset>\n";
