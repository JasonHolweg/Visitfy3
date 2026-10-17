<?php
/**
 * Simple CMS helpers for JSON-based content/script config.
 */

if (!function_exists('visitfy_load_json')) {
    function visitfy_load_json(string $absolutePath, array $fallback = []): array
    {
        if (!is_file($absolutePath)) {
            return $fallback;
        }
        $raw = file_get_contents($absolutePath);
        if (!is_string($raw) || $raw === '') {
            return $fallback;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : $fallback;
    }
}

if (!function_exists('visitfy_get')) {
    function visitfy_get(array $source, string $path, $fallback = '')
    {
        $segments = explode('.', $path);
        $cursor = $source;
        foreach ($segments as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return is_string($fallback) ? visitfy_fill($fallback) : $fallback;
            }
            $cursor = $cursor[$segment];
        }
        return is_string($cursor) ? visitfy_fill($cursor) : $cursor;
    }
}

if (!function_exists('visitfy_split_lines')) {
    function visitfy_split_lines(string $value): array
    {
        $parts = preg_split('/\r\n|\r|\n/', $value) ?: [];
        $parts = array_map(static fn($v) => trim((string)$v), $parts);
        return array_values(array_filter($parts, static fn($v) => $v !== ''));
    }
}

require_once __DIR__ . '/urls.php';
require_once __DIR__ . '/crm.php';

if (!function_exists('visitfy_base_path')) {
    /** Basis-Pfad der Website. Seit den sauberen URLs identisch mit visitfy_site_root(). */
    function visitfy_base_path(): string
    {
        $siteRoot = visitfy_site_root();
        if ($siteRoot !== '') {
            return $siteRoot;
        }
        $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName === '') {
            return '/';
        }

        if (substr($scriptName, -9) === '/index.php') {
            $base = substr($scriptName, 0, -9);
        } else {
            $base = rtrim(dirname($scriptName), '/\\');
        }

        $base = trim((string)$base, '/\\');
        return $base === '' ? '/' : '/' . $base . '/';
    }
}

if (!function_exists('visitfy_url')) {
    function visitfy_url(string $path = ''): string
    {
        if ($path === '') {
            return visitfy_base_path();
        }

        if (preg_match('#^(?:https?:)?//#i', $path) || strpos($path, 'mailto:') === 0 || strpos($path, 'tel:') === 0) {
            return $path;
        }

        if (strpos($path, '#') === 0) {
            return $path;
        }

        /* Alte Seitenpfade (z. B. 'pages/kontakt.php' aus content.json) direkt auf die saubere URL */
        $clean = visitfy_clean_url($path);
        if ($clean !== null) {
            return $clean;
        }

        return visitfy_base_path() . ltrim($path, '/');
    }
}

if (!function_exists('visitfy_site_origin')) {
    function visitfy_site_origin(): string
    {
        return 'https://visitfy.de';
    }
}

if (!function_exists('visitfy_canonical_url')) {
    function visitfy_canonical_url(): string
    {
        $origin = visitfy_site_origin();
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if ($path === '/index.php') {
            $path = '/';
        }

        return $origin . $path;
    }
}

if (!function_exists('visitfy_org_node')) {
    /**
     * Liefert den Organization/LocalBusiness-Knoten (geteilt von allen Seiten).
     */
    function visitfy_org_node(): array
    {
        $origin = visitfy_site_origin();
        return [
            '@type'         => ['ProfessionalService', 'LocalBusiness'],
            '@id'           => $origin . '/#organization',
            'name'          => 'Visitfy',
            'alternateName' => 'Visitfy 360° Rundgänge',
            'url'           => $origin . '/',
            'logo'          => [
                '@type'  => 'ImageObject',
                'url'    => $origin . '/assets/img/logo.svg',
                'width'  => 645,
                'height' => 563,
            ],
            'image'       => $origin . '/assets/img/logo.svg',
            'description' => 'Visitfy entwickelt professionelle 360° virtuelle Rundgänge für Unternehmen jeder Branche – realistisch, hochwertig und sofort einsatzbereit für Website und Google Business Profile.',
            'telephone'   => '+49 1520 6955260',
            'email'       => 'info@visitfy.de',
            'priceRange'  => '€€',
            'vatID'       => 'DE363387743',
            'founder'     => [
                '@type' => 'Person',
                'name'  => 'Kristian Meister',
            ],
            'address'     => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => 'Flensburger Str. 25',
                'postalCode'      => '24976',
                'addressLocality' => 'Handewitt',
                'addressRegion'   => 'Schleswig-Holstein',
                'addressCountry'  => 'DE',
            ],
            'areaServed' => [
                ['@type' => 'City', 'name' => 'Flensburg'],
                ['@type' => 'AdministrativeArea', 'name' => 'Schleswig-Holstein'],
                ['@type' => 'Country', 'name' => 'Deutschland'],
            ],
            'hasMap'    => 'https://maps.app.goo.gl/Hx3L2b3xxQU2XBv16',
            'sameAs'    => [
                'https://maps.app.goo.gl/Hx3L2b3xxQU2XBv16',
                'https://www.instagram.com/visitfy.de/',
                'https://www.facebook.com/people/Visitfy/61567271012669/',
            ],
            'knowsAbout' => [
                '360° Rundgänge',
                'virtuelle Touren',
                'Matterport',
                'Google Street View Trusted',
                'Google Business Profile Optimierung',
                'digitale Raumpräsentation',
            ],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name'  => '360°-Rundgang-Dienstleistungen',
                'itemListElement' => [
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => '360° Rundgang für Gastronomie & Cafés']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => '360° Rundgang für Hotels & Wellness']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => '360° Rundgang für Einzelhandel & Showrooms']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => '360° Rundgang für Immobilien']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => '360° Rundgang für Praxen & medizinische Einrichtungen']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Google Business Profile Integration']],
                ],
            ],
        ];
    }
}

if (!function_exists('visitfy_website_node')) {
    function visitfy_website_node(): array
    {
        $origin = visitfy_site_origin();
        return [
            '@type'      => 'WebSite',
            '@id'        => $origin . '/#website',
            'url'        => $origin . '/',
            'name'       => 'Visitfy',
            'inLanguage' => 'de-DE',
            'publisher'  => ['@id' => $origin . '/#organization'],
        ];
    }
}

if (!function_exists('visitfy_breadcrumb_node')) {
    /**
     * @param array<int, array{name:string, url:string}> $crumbs
     */
    function visitfy_breadcrumb_node(array $crumbs): array
    {
        $items = [];
        foreach ($crumbs as $i => $c) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $c['name'],
                'item'     => $c['url'],
            ];
        }
        return [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}

if (!function_exists('visitfy_strip_html')) {
    function visitfy_strip_html(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}

if (!function_exists('visitfy_faq_node')) {
    /**
     * @param array<int, array{question:string, answer:string}> $items
     */
    function visitfy_faq_node(array $items): array
    {
        $list = [];
        foreach ($items as $item) {
            $list[] = [
                '@type'          => 'Question',
                'name'           => (string)($item['question'] ?? ''),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => visitfy_strip_html((string)($item['answer'] ?? '')),
                ],
            ];
        }
        return [
            '@type'      => 'FAQPage',
            'mainEntity' => $list,
        ];
    }
}

if (!function_exists('visitfy_render_jsonld')) {
    /**
     * Erzeugt den vollständigen JSON-LD-Block für die aktuelle Seite.
     */
    function visitfy_render_jsonld(string $pageKey, string $pageTitle = '', string $pageDesc = ''): string
    {
        $origin   = visitfy_site_origin();
        $canon    = visitfy_canonical_url();
        $org      = visitfy_org_node();
        $site     = visitfy_website_node();

        $home   = ['name' => 'Start', 'url' => $origin . '/'];
        $graph  = [$org, $site];

        switch ($pageKey) {
            case 'home':
                $graph[] = [
                    '@type'           => 'WebPage',
                    '@id'             => $canon . '#webpage',
                    'url'             => $canon,
                    'name'            => $pageTitle ?: 'Visitfy | 360° Rundgänge',
                    'description'     => $pageDesc,
                    'inLanguage'      => 'de-DE',
                    'isPartOf'        => ['@id' => $origin . '/#website'],
                    'about'           => ['@id' => $origin . '/#organization'],
                    'primaryImageOfPage' => $origin . '/assets/img/logo.svg',
                ];
                $contentConfig = visitfy_load_json(__DIR__ . '/../assets/data/content.json', []);
                $faqItems = visitfy_get($contentConfig, 'faq.items', []);
                if (is_array($faqItems) && $faqItems) {
                    $graph[] = visitfy_faq_node($faqItems);
                }
                /* Tour-Liste */
                $tours = visitfy_load_json(__DIR__ . '/../assets/data/tours.json', []);
                if (is_array($tours) && $tours) {
                    $tourItems = [];
                    foreach ($tours as $i => $t) {
                        $tourItems[] = [
                            '@type'    => 'ListItem',
                            'position' => $i + 1,
                            'item'     => [
                                '@type'       => 'CreativeWork',
                                'name'        => (string)($t['title'] ?? ''),
                                'genre'       => (string)($t['tag'] ?? ''),
                                'description' => (string)($t['description'] ?? ''),
                                'url'         => (string)($t['matterportUrl'] ?? ''),
                                'creator'     => ['@id' => $origin . '/#organization'],
                            ],
                        ];
                    }
                    $graph[] = [
                        '@type'           => 'ItemList',
                        '@id'             => $canon . '#tours',
                        'name'            => 'Beispiel-Rundgänge',
                        'itemListElement' => $tourItems,
                    ];
                }
                $graph[] = visitfy_breadcrumb_node([$home]);
                break;

            case 'about':
                $graph[] = [
                    '@type'      => 'AboutPage',
                    '@id'        => $canon . '#webpage',
                    'url'        => $canon,
                    'name'       => $pageTitle,
                    'description'=> $pageDesc,
                    'inLanguage' => 'de-DE',
                    'isPartOf'   => ['@id' => $origin . '/#website'],
                    'about'      => ['@id' => $origin . '/#organization'],
                ];
                $graph[] = [
                    '@type'      => 'Person',
                    'name'       => 'Kristian Meister',
                    'jobTitle'   => 'Geschäftsführer',
                    'worksFor'   => ['@id' => $origin . '/#organization'],
                ];
                $graph[] = visitfy_breadcrumb_node([
                    $home,
                    ['name' => 'Über uns', 'url' => $canon],
                ]);
                break;

            case 'faq':
                $graph[] = [
                    '@type'      => 'WebPage',
                    '@id'        => $canon . '#webpage',
                    'url'        => $canon,
                    'name'       => $pageTitle,
                    'description'=> $pageDesc,
                    'inLanguage' => 'de-DE',
                    'isPartOf'   => ['@id' => $origin . '/#website'],
                ];
                /* FAQ aus pages/faq.php – die dort gerenderten 6 Items */
                $graph[] = visitfy_faq_node([
                    [
                        'question' => 'Was ist ein 360° Rundgang?',
                        'answer'   => 'Ein 360° Rundgang ist eine interaktive, virtuelle Begehung Ihrer Räume. Mit modernster Kameratechnik werden alle Bereiche Ihrer Location fotorealistisch erfasst und zu einem nahtlosen, immersiven Erlebnis zusammengesetzt. Besucher können sich frei durch Ihre Räume bewegen, Bereiche erkunden und optional eingebettete Informationen wie Menüs, Preise oder Kontaktoptionen abrufen – direkt im Rundgang, rund um die Uhr, von jedem Gerät aus.',
                    ],
                    [
                        'question' => 'Welche Vorteile bietet ein 360° Rundgang?',
                        'answer'   => 'Ein 360° Rundgang bietet messbare Mehrwerte: stärkeres Vertrauen bei potenziellen Kunden noch vor dem ersten Besuch, höhere Sichtbarkeit auf Google Maps und in der organischen Suche, mehr qualifizierte Anfragen, ein 24/7 verfügbares interaktives Schaufenster, interaktive Hotspots für Menüs, Preislisten, Videos und Kontaktformulare sowie die Einbindung auf Website, Booking-Portalen und Social Media.',
                    ],
                    [
                        'question' => 'Für welche Branchen eignet sich ein 360° Rundgang?',
                        'answer'   => 'Grundsätzlich für jede Location, die Kunden physisch besuchen oder vorab erleben sollen: Gastronomie (Restaurants, Cafés, Bars, Eventlocations), Hotels und Wellness (Zimmer, Spa, Konferenzräume), Immobilien (Verkauf, Vermietung, Neubauprojekte), Einzelhandel und Showrooms, Praxen und medizinische Einrichtungen, Fitnessstudios und Sportanlagen sowie Bildungseinrichtungen und Coworking-Spaces.',
                    ],
                    [
                        'question' => 'Was kostet ein 360° Rundgang?',
                        'answer'   => 'Die Kosten richten sich nach Größe und Raumanzahl der Location sowie dem gewünschten Funktionsumfang (interaktive Hotspots, Branding, mehrsprachige Beschriftungen). Für ein transparentes, individuelles Angebot beraten wir persönlich und unverbindlich.',
                    ],
                    [
                        'question' => 'Wie lange dauert die Erstellung eines Rundgangs?',
                        'answer'   => 'Nach dem Briefing und der Aufnahme vor Ort ist der Rundgang im Durchschnitt innerhalb von 5 Werktagen live. Für größere Locations oder Sonderwünsche kalkulieren wir gemeinsam den Zeitplan.',
                    ],
                    [
                        'question' => 'Wo kann ich den Rundgang einbinden?',
                        'answer'   => 'Auf der eigenen Website (per iFrame, ein Klick), direkt im Google Business Profil, in Buchungsportalen wie Booking.com oder OpenTable, auf Social Media als Link oder eingebetteter Post, in E-Mail-Newslettern als Preview-Link.',
                    ],
                ]);
                $graph[] = visitfy_breadcrumb_node([
                    $home,
                    ['name' => 'FAQ', 'url' => $canon],
                ]);
                break;

            case 'kontakt':
                $graph[] = [
                    '@type'      => 'ContactPage',
                    '@id'        => $canon . '#webpage',
                    'url'        => $canon,
                    'name'       => $pageTitle,
                    'description'=> $pageDesc,
                    'inLanguage' => 'de-DE',
                    'isPartOf'   => ['@id' => $origin . '/#website'],
                    'mainEntity' => ['@id' => $origin . '/#organization'],
                ];
                $graph[] = visitfy_breadcrumb_node([
                    $home,
                    ['name' => 'Kontakt', 'url' => $canon],
                ]);
                break;

            case 'partner':
                $graph[] = [
                    '@type'      => 'WebPage',
                    '@id'        => $canon . '#webpage',
                    'url'        => $canon,
                    'name'       => $pageTitle,
                    'description'=> $pageDesc,
                    'inLanguage' => 'de-DE',
                    'isPartOf'   => ['@id' => $origin . '/#website'],
                ];
                $graph[] = visitfy_breadcrumb_node([
                    $home,
                    ['name' => 'Partner werden', 'url' => $canon],
                ]);
                break;

            case 'impressum':
                $graph[] = [
                    '@type'      => 'WebPage',
                    '@id'        => $canon . '#webpage',
                    'url'        => $canon,
                    'name'       => $pageTitle,
                    'description'=> $pageDesc,
                    'inLanguage' => 'de-DE',
                    'isPartOf'   => ['@id' => $origin . '/#website'],
                ];
                $graph[] = visitfy_breadcrumb_node([
                    $home,
                    ['name' => 'Impressum', 'url' => $canon],
                ]);
                break;

            case 'datenschutz':
                $graph[] = [
                    '@type'      => 'WebPage',
                    '@id'        => $canon . '#webpage',
                    'url'        => $canon,
                    'name'       => $pageTitle,
                    'description'=> $pageDesc,
                    'inLanguage' => 'de-DE',
                    'isPartOf'   => ['@id' => $origin . '/#website'],
                ];
                $graph[] = visitfy_breadcrumb_node([
                    $home,
                    ['name' => 'Datenschutz', 'url' => $canon],
                ]);
                break;

            default:
                $graph[] = [
                    '@type'      => 'WebPage',
                    '@id'        => $canon . '#webpage',
                    'url'        => $canon,
                    'name'       => $pageTitle,
                    'description'=> $pageDesc,
                    'inLanguage' => 'de-DE',
                    'isPartOf'   => ['@id' => $origin . '/#website'],
                ];
                break;
        }

        $payload = [
            '@context' => 'https://schema.org',
            '@graph'   => $graph,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) {
            return '';
        }

        return '<script type="application/ld+json">' . $json . '</script>';
    }
}
