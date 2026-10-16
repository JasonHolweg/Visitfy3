<?php
/**
 * Visitfy3 – partials/schema.php
 * Gemeinsame strukturierte Daten (JSON-LD) für alle Seiten.
 * Alle Seiten verweisen über dieselbe @id auf die Organisation, damit Google und KI-Systeme
 * die Angaben einer Firma zuordnen.
 */
require_once __DIR__ . '/cms.php';

if (!function_exists('visitfy_schema_org_id')) {

    function visitfy_schema_org_id(): string
    {
        return VISITFY_SITE_URL . '/#organization';
    }

    /** Organisation „Visitfy“ – Werte aus content.json, wo vorhanden. */
    function visitfy_schema_organization(array $contentConfig): array
    {
        $email = trim((string)visitfy_get($contentConfig, 'kontakt_text.email', ''));
        if ($email === '') {
            $email = trim((string)visitfy_get($contentConfig, 'footer.contact_email', 'info@visitfy.de'));
        }
        $phone = trim((string)visitfy_get($contentConfig, 'kontakt_text.phone', ''));

        $cities = [];
        $networkFile = __DIR__ . '/partner-network.php';
        if (is_file($networkFile)) {
            $network = require $networkFile;
            foreach ((array)($network['active'] ?? []) as $p) {
                if (!empty($p['name'])) {
                    $cities[] = ['@type' => 'Place', 'name' => (string)$p['name']];
                }
            }
        }

        $org = [
            '@type'       => 'Organization',
            '@id'         => visitfy_schema_org_id(),
            'name'        => 'Visitfy',
            'url'         => VISITFY_SITE_URL . '/',
            'logo'        => ['@type' => 'ImageObject', 'url' => VISITFY_SITE_URL . '/assets/img/logo.svg'],
            'description' => 'Visitfy erstellt virtuelle 360°-Rundgänge für Unternehmen – für Gastronomie, Hotels, Praxen, Immobilien und Einzelhandel – und arbeitet dafür mit Partnern in ganz Deutschland.',
            'email'       => $email,
            'address'     => ['@type' => 'PostalAddress', 'addressLocality' => 'Flensburg', 'addressRegion' => 'Schleswig-Holstein', 'addressCountry' => 'DE'],
            'areaServed'  => array_merge([['@type' => 'Country', 'name' => 'Deutschland']], $cities),
            'knowsAbout'  => ['360-Grad-Rundgänge', 'Virtuelle Rundgänge', 'Matterport'],
            'sameAs'      => [
                'https://www.instagram.com/visitfy.de/',
                'https://www.facebook.com/people/Visitfy/61567271012669/',
            ],
        ];
        if ($phone !== '') {
            $org['telephone'] = $phone;
        }
        return $org;
    }

    function visitfy_schema_website(): array
    {
        return [
            '@type'      => 'WebSite',
            '@id'        => VISITFY_SITE_URL . '/#website',
            'url'        => VISITFY_SITE_URL . '/',
            'name'       => 'Visitfy',
            'inLanguage' => 'de-DE',
            'publisher'  => ['@id' => visitfy_schema_org_id()],
        ];
    }

    /** Leistung „360°-Rundgang“ */
    function visitfy_schema_service(): array
    {
        return [
            '@type'       => 'Service',
            '@id'         => VISITFY_SITE_URL . '/#service',
            'name'        => 'Virtuelle 360°-Rundgänge für Unternehmen',
            'serviceType' => '360-Grad-Rundgang',
            'description' => 'Aufnahme vor Ort, Erstellung und Einbindung eines virtuellen 360°-Rundgangs auf der eigenen Website und im Google-Unternehmensprofil.',
            'provider'    => ['@id' => visitfy_schema_org_id()],
            'areaServed'  => ['@type' => 'Country', 'name' => 'Deutschland'],
            'audience'    => ['@type' => 'BusinessAudience', 'audienceType' => 'Gastronomie, Hotels, Praxen, Immobilien, Einzelhandel'],
        ];
    }

    /** Seite + Breadcrumb */
    function visitfy_schema_webpage(string $url, string $name, string $description, array $crumbs = []): array
    {
        $page = [
            '@type'       => 'WebPage',
            '@id'         => $url . '#webpage',
            'url'         => $url,
            'name'        => $name,
            'description' => $description,
            'inLanguage'  => 'de-DE',
            'isPartOf'    => ['@id' => VISITFY_SITE_URL . '/#website'],
            'about'       => ['@id' => visitfy_schema_org_id()],
        ];
        if ($crumbs) {
            $items = [];
            foreach (array_values($crumbs) as $i => $crumb) {
                $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb[0], 'item' => $crumb[1]];
            }
            $page['breadcrumb'] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
        }
        return $page;
    }

    /**
     * FAQPage aus einer Liste von Fragen. $items: [['question' => ..., 'answer' => ...], …]
     * (alternativ Schlüssel 'q'/'a'). Antworten dürfen einfaches HTML enthalten.
     */
    function visitfy_schema_faq(array $items, string $id): array
    {
        $entities = [];
        foreach ($items as $item) {
            $q = trim(strip_tags((string)($item['question'] ?? $item['q'] ?? '')));
            $a = trim(strip_tags((string)($item['answer'] ?? $item['a'] ?? ''), '<p><br><ul><ol><li><strong><b><em><i>'));
            if ($q === '' || $a === '') {
                continue;
            }
            $entities[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
        }
        return ['@type' => 'FAQPage', '@id' => $id, 'mainEntity' => $entities];
    }

    /** Ein <script type="application/ld+json"> für einen @graph ausgeben (für Blöcke im <body>). */
    function visitfy_schema_print(array $nodes): void
    {
        $nodes = array_values(array_filter($nodes));
        if (!$nodes) {
            return;
        }
        echo '<script type="application/ld+json">'
            . json_encode(['@context' => 'https://schema.org', '@graph' => $nodes], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)
            . "</script>\n";
    }
}
