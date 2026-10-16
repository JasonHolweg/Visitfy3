<?php
/**
 * Visitfy3 – partials/routes.php
 * Eine Quelle für alle öffentlichen Seiten: saubere URL, PHP-Datei, Sitemap.
 *
 * path   Öffentliche URL (ohne Domain). Alte /pages/*.php-Adressen leitet .htaccess per 301 hierher.
 * file   Datei relativ zum Projekt-Root.
 * deps   Weitere Dateien, deren Änderung als Seitenänderung zählt (für <lastmod> in der Sitemap).
 * sitemap false = nicht in die Sitemap.
 *
 * Neue Seite: Eintrag hier + RewriteRule in .htaccess (Block „Saubere URLs“).
 */
return [
    'home' => [
        'path' => '/',
        'file' => 'index.php',
        'deps' => ['assets/data/content.json', 'assets/data/tours.json'],
    ],
    'partner' => [
        'path' => '/partner-werden',
        'file' => 'pages/partner.php',
        'deps' => ['partials/partner-network.php', 'partials/partner-testimonials.php'],
    ],
    'kontakt' => [
        'path' => '/kontakt',
        'file' => 'pages/kontakt.php',
    ],
    'faq' => [
        'path' => '/faq',
        'file' => 'pages/faq.php',
    ],
    'about' => [
        'path' => '/ueber-uns',
        'file' => 'pages/about.php',
    ],
    'impressum' => [
        'path' => '/impressum',
        'file' => 'pages/impressum.php',
        'sitemap' => false,
    ],
    'datenschutz' => [
        'path' => '/datenschutz',
        'file' => 'pages/datenschutz.php',
        'sitemap' => false,
    ],
];
