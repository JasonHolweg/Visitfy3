<?php
/**
 * Visitfy3 – partials/urls.php
 * URL-Helfer für saubere Adressen (wird von partials/cms.php geladen).
 *
 *   visitfy_site_root()          '/' (oder '/unterordner/', falls die Seite nicht im Webroot liegt)
 *   visitfy_page_url('partner')  '/partner-werden'
 *   visitfy_page_url('partner', true)  'https://visitfy.de/partner-werden'
 *   visitfy_clean_url('pages/kontakt.php')  '/kontakt' (alte Pfade aus content.json etc.), sonst null
 */

if (!defined('VISITFY_SITE_URL')) {
    define('VISITFY_SITE_URL', 'https://visitfy.de');
}

if (!function_exists('visitfy_site_root')) {
    /** Pfad des Projekt-Roots relativ zum Webserver-Root, immer mit / am Anfang und Ende. */
    function visitfy_site_root(): string
    {
        static $base = null;
        if ($base !== null) {
            return $base;
        }
        $base = '/';
        $project = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
        $docroot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? '')) ?: '';
        if ($docroot !== '' && strpos($project, $docroot) === 0) {
            $rel = trim(str_replace('\\', '/', substr($project, strlen($docroot))), '/');
            $base = $rel === '' ? '/' : '/' . $rel . '/';
        }
        return $base;
    }
}

if (!function_exists('visitfy_routes')) {
    function visitfy_routes(): array
    {
        static $routes = null;
        if ($routes === null) {
            $routes = require __DIR__ . '/routes.php';
        }
        return $routes;
    }
}

if (!function_exists('visitfy_page_url')) {
    /** Saubere URL einer Seite aus partials/routes.php. */
    function visitfy_page_url(string $key, bool $absolute = false): string
    {
        $routes = visitfy_routes();
        $path = (string)($routes[$key]['path'] ?? '/');
        if ($absolute) {
            return VISITFY_SITE_URL . $path;
        }
        return rtrim(visitfy_site_root(), '/') . $path;
    }
}

if (!function_exists('visitfy_clean_url')) {
    /**
     * Wandelt alte interne Pfade (z. B. 'pages/kontakt.php', 'index.php', 'pages/faq.php#x')
     * in die saubere URL um. Für Pfade, die keine Seite sind (Bilder, Handler …), kommt null zurück.
     */
    function visitfy_clean_url(string $path): ?string
    {
        $hash = '';
        if (($pos = strpos($path, '#')) !== false) {
            $hash = substr($path, $pos);
            $path = substr($path, 0, $pos);
        }
        $norm = ltrim($path, '/');
        if ($norm === '' || $norm === 'index.php') {
            return visitfy_page_url('home') . $hash;
        }
        foreach (visitfy_routes() as $key => $route) {
            if ($norm === $route['file'] || '/' . $norm === $route['path']) {
                return visitfy_page_url($key) . $hash;
            }
        }
        return null;
    }
}
