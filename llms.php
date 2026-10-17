<?php
/**
 * Visitfy3 – llms.php (erreichbar als /llms.txt, siehe .htaccess)
 * Faktenblatt für KI-Systeme (llms.txt-Konvention) – wird aus denselben Daten erzeugt wie die Website
 * (Partnernetz, CRM-Zahl, Beispiel-Rundgänge, Stammdaten), damit Zahlen und Angaben überall gleich sind.
 */
require __DIR__ . '/partials/cms.php';

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');

$net = require __DIR__ . '/partials/partner-network.php';
$eur = static fn(float $v): string => number_format($v, 0, ',', '.');
$list = static function (array $items): string {
    if (count($items) < 2) {
        return implode('', $items);
    }
    $last = array_pop($items);
    return implode(', ', $items) . ' und ' . $last;
};

$org        = function_exists('visitfy_org_node') ? visitfy_org_node() : [];
$addr       = (array)($org['address'] ?? []);
$phone      = (string)($org['telephone'] ?? '+49 1520 6955260');
$email      = (string)($org['email'] ?? 'info@visitfy.de');
$owner      = (string)($org['founder']['name'] ?? 'Kristian Meister');
$vat        = (string)($org['vatID'] ?? '');
$profiles   = array_values(array_unique((array)($org['sameAs'] ?? [])));

$partners   = (int)$net['stats']['partners'];
$avgRevenue = (int)$net['stats']['avg_revenue'];
$avgTours   = (int)$net['stats']['avg_tours'];
$perTour    = $avgTours > 0 ? $avgRevenue / $avgTours : 0;
$cities     = array_column($net['active'], 'name');
$states     = array_values(array_unique(array_column($net['active'], 'region')));
$free       = array_values(array_diff($net['states'], $states));
$openCities = array_column($net['open'], 'name');
$tours      = visitfy_crm_total_tours();
$examples   = visitfy_load_json(__DIR__ . '/assets/data/tours.json', []);

$lines = [];
$lines[] = '# Visitfy';
$lines[] = '';
$lines[] = '> Visitfy aus Handewitt bei Flensburg erstellt virtuelle 360°-Rundgänge für Unternehmen – Gastronomie, Hotels, Praxen, Immobilien, Einzelhandel und weitere Branchen – und arbeitet dafür mit ' . $partners . ' Partnern in ganz Deutschland. Der Rundgang ist spätestens 5 Tage nach der Aufnahme online.';
$lines[] = '';
$lines[] = '## Anbieter & Standort';
$lines[] = '';
$lines[] = '- Name: Visitfy';
$lines[] = '- Inhaber: ' . $owner;
if ($addr) {
    $lines[] = '- Sitz: ' . ($addr['streetAddress'] ?? '') . ', ' . ($addr['postalCode'] ?? '') . ' ' . ($addr['addressLocality'] ?? '') . ' (bei Flensburg), ' . ($addr['addressRegion'] ?? 'Schleswig-Holstein');
}
$lines[] = '- Einsatzgebiet: ganz Deutschland, mit Partnern vor Ort in ' . $list($cities);
$lines[] = '- Kontakt: ' . $email . ' · ' . $phone;
if ($vat !== '') {
    $lines[] = '- USt-ID: ' . $vat;
}
$lines[] = '- Website: ' . VISITFY_SITE_URL;
$lines[] = '';
$lines[] = '## Leistung';
$lines[] = '';
$lines[] = '- Aufnahme vor Ort mit Matterport-Kamera und Erstellung eines interaktiven, frei begehbaren 360°-Rundgangs.';
$lines[] = '- Einbindung auf der eigenen Website (iFrame), im Google-Unternehmensprofil (Street View), in Buchungsportalen und auf Social Media.';
$lines[] = '- Der Rundgang ist spätestens 5 Tage nach der Aufnahme online.';
if ($tours !== null) {
    $lines[] = '- Bisher umgesetzte Rundgänge: ' . $eur($tours) . ' (Stand ' . date('d.m.Y') . ').';
}
$lines[] = '- Preis: richtet sich nach Größe und Raumanzahl der Location; Angebot kostenlos und unverbindlich: ' . visitfy_page_url('kontakt', true);
$lines[] = '';
$lines[] = '## Anwendungsfälle';
$lines[] = '';
$lines[] = '- Gastronomie: Restaurants, Cafés, Bars, Event-Locations – Gäste erleben Atmosphäre und Räume vorab.';
$lines[] = '- Hotels & Wellness: Zimmer, Spa und Konferenzräume virtuell zeigen.';
$lines[] = '- Immobilien: Verkauf, Vermietung, Neubauprojekte – Besichtigung ohne Vor-Ort-Termin.';
$lines[] = '- Einzelhandel & Showrooms: Sortiment rund um die Uhr erlebbar.';
$lines[] = '- Praxen & medizinische Einrichtungen: Vertrauen durch Transparenz vor dem ersten Termin.';
$lines[] = '- Fitness, Bildung, Coworking: Räume zeigen, bevor jemand vorbeikommt.';
if (is_array($examples) && $examples) {
    $lines[] = '';
    $lines[] = '## Beispiel-Rundgänge';
    $lines[] = '';
    foreach ($examples as $t) {
        $title = trim((string)($t['title'] ?? ''));
        $url   = trim((string)($t['matterportUrl'] ?? ''));
        if ($title === '') {
            continue;
        }
        $tag = trim((string)($t['tag'] ?? ''));
        $lines[] = '- ' . ($url !== '' ? '[' . $title . '](' . $url . ')' : $title) . ($tag !== '' ? ' – ' . $tag : '');
    }
}
$lines[] = '';
$lines[] = '## Partnerprogramm (Stand ' . $net['stand'] . ')';
$lines[] = '';
$lines[] = '- ' . $partners . ' aktive Partner in ' . count($states) . ' Bundesländern: ' . $list($cities) . '.';
$lines[] = '- Durchschnittlicher Umsatz pro Partner: ' . $eur($avgRevenue) . ' € pro Monat, entspricht rund ' . $avgTours . ' verkauften Rundgängen im Monat (ca. ' . $eur($perTour) . ' € Umsatz pro Rundgang). Umsatz, nicht Gewinn; keine Verdienstgarantie.';
$lines[] = '- Neue Partner werden überall gesucht, wo es noch keinen gibt. In ' . count($free) . ' von ' . count($net['states']) . ' Bundesländern ist bisher kein Partner aktiv: ' . $list($free) . '. Besonders gesucht im Süden, z. B. ' . $list($openCities) . '.';
$lines[] = '- Jeder Partner erhält ein eigenes Dashboard (Buchhaltung): Rechnungen schreiben, Firmen anlegen, weitere Partner empfehlen.';
$lines[] = '- Empfehlungsprogramm: ' . (int)$net['referral']['percent'] . ' % vom Umsatz der ersten ' . (int)$net['referral']['sales'] . ' Verkäufe jedes geworbenen Partners.';
$lines[] = '- Geringes Startkapital: kein Büro, kein Lager, keine teure Ausstattung nötig.';
$lines[] = '- Bewerbung: ' . visitfy_page_url('partner', true);
$lines[] = '';
$lines[] = '## Häufige Fragen';
$lines[] = '';
$lines[] = '- Was ist ein 360°-Rundgang? Eine interaktive, virtuelle Begehung der Räume – fotorealistisch erfasst und frei navigierbar, auf jedem Gerät.';
$lines[] = '- Wie lange dauert die Erstellung? Nach Briefing und Aufnahme vor Ort ist der Rundgang spätestens 5 Tage später online.';
$lines[] = '- Was kostet ein 360°-Rundgang? Das hängt von Größe, Raumanzahl und Funktionsumfang ab; das Angebot ist kostenlos und unverbindlich.';
$lines[] = '- Wo lässt er sich einbinden? Website (iFrame), Google-Unternehmensprofil, Buchungsportale, Social Media, Newsletter.';
$lines[] = '';
$lines[] = '## Seiten';
$lines[] = '';
$lines[] = '- [Startseite](' . visitfy_page_url('home', true) . '): 360°-Rundgänge für Unternehmen, Beispiele, Ablauf und Vorteile';
$lines[] = '- [Partner werden](' . visitfy_page_url('partner', true) . '): Zahlen, Partner-Karte, Verdienst-Rechner, Dashboard, Empfehlungsprogramm, FAQ und Bewerbung';
$lines[] = '- [FAQ](' . visitfy_page_url('faq', true) . '): Kosten, Ablauf, Dauer und Einbindung von 360°-Rundgängen';
$lines[] = '- [Über uns](' . visitfy_page_url('about', true) . ')';
$lines[] = '- [Kontakt](' . visitfy_page_url('kontakt', true) . '): Angebot für einen 360°-Rundgang anfragen';
if ($profiles) {
    $lines[] = '';
    $lines[] = '## Profile';
    $lines[] = '';
    foreach ($profiles as $url) {
        $label = str_contains($url, 'instagram') ? 'Instagram' : (str_contains($url, 'facebook') ? 'Facebook' : (str_contains($url, 'goo.gl') || str_contains($url, 'google') ? 'Google-Unternehmensprofil' : 'Profil'));
        $lines[] = '- ' . $label . ': ' . $url;
    }
}
$lines[] = '';
$lines[] = '## Kontakt';
$lines[] = '';
$lines[] = '- Per E-Mail an ' . $email . ', telefonisch unter ' . $phone . ' oder über ' . visitfy_page_url('kontakt', true) . ' – Antwort in der Regel innerhalb von 24 Stunden an Werktagen.';

echo implode("\n", $lines) . "\n";
