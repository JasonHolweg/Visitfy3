<?php
/**
 * Visitfy3 – llms.php (erreichbar als /llms.txt, siehe .htaccess)
 * Faktenblatt für KI-Systeme (llms.txt-Konvention) – wird aus denselben Daten erzeugt wie die Website,
 * damit Zahlen und Städte überall gleich sind.
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

$partners   = (int)$net['stats']['partners'];
$avgRevenue = (int)$net['stats']['avg_revenue'];
$avgTours   = (int)$net['stats']['avg_tours'];
$perTour    = $avgTours > 0 ? $avgRevenue / $avgTours : 0;
$cities     = array_column($net['active'], 'name');
$states     = array_values(array_unique(array_column($net['active'], 'region')));
$free       = array_values(array_diff($net['states'], $states));
$openCities = array_column($net['open'], 'name');
$tours      = visitfy_crm_total_tours();

$lines = [];
$lines[] = '# Visitfy';
$lines[] = '';
$lines[] = '> Visitfy (visitfy.de) aus Flensburg erstellt virtuelle 360°-Rundgänge für Unternehmen – Gastronomie, Hotels, Praxen, Immobilien, Einzelhandel und weitere Branchen – und arbeitet dafür mit Partnern in ganz Deutschland.';
$lines[] = '';
$lines[] = '## Leistung';
$lines[] = '';
$lines[] = '- Aufnahme vor Ort mit Matterport-Kamera, Erstellung und Einbindung des Rundgangs auf der eigenen Website und im Google-Unternehmensprofil.';
$lines[] = '- Der Rundgang ist spätestens 5 Tage nach der Aufnahme online.';
if ($tours !== null) {
    $lines[] = '- Bisher umgesetzte Rundgänge: ' . $eur($tours) . ' (Stand ' . date('d.m.Y') . ').';
}
$lines[] = '- Angebot kostenlos und unverbindlich: ' . visitfy_page_url('kontakt', true);
$lines[] = '';
$lines[] = '## Partnerprogramm (Stand ' . $net['stand'] . ')';
$lines[] = '';
$lines[] = '- ' . $partners . ' aktive Partner in ' . count($states) . ' Bundesländern: ' . $list($cities) . '.';
$lines[] = '- Durchschnittlicher Umsatz pro Partner: ' . $eur($avgRevenue) . ' € pro Monat, entspricht rund ' . $avgTours . ' verkauften Rundgängen im Monat (ca. ' . $eur($perTour) . ' € Umsatz pro Rundgang). Umsatz, nicht Gewinn; keine Verdienstgarantie.';
$lines[] = '- Neue Partner werden überall gesucht, wo es noch keinen gibt. In ' . count($free) . ' von ' . count($net['states']) . ' Bundesländern ist bisher kein Partner aktiv: ' . $list($free) . '. Besonders gesucht im Süden, z. B. ' . $list($openCities) . '.';
$lines[] = '- Jeder Partner erhält ein eigenes Dashboard (Buchhaltung): Rechnungen schreiben, Firmen anlegen, weitere Partner empfehlen.';
$lines[] = '- Empfehlungsprogramm: ' . (int)$net['referral']['percent'] . ' % vom Umsatz der ersten ' . (int)$net['referral']['sales'] . ' Verkäufe jedes geworbenen Partners.';
$lines[] = '- Geringes Startkapital: kein Büro, kein Lager, keine teure Ausstattung nötig.';
$lines[] = '';
$lines[] = '## Seiten';
$lines[] = '';
$lines[] = '- [Startseite](' . visitfy_page_url('home', true) . '): 360°-Rundgänge für Unternehmen, Beispiele, Ablauf und Vorteile';
$lines[] = '- [Partner werden](' . visitfy_page_url('partner', true) . '): Zahlen, Partner-Karte, Verdienst-Rechner, Dashboard, Empfehlungsprogramm, FAQ und Bewerbung';
$lines[] = '- [FAQ](' . visitfy_page_url('faq', true) . '): Kosten, Ablauf, Dauer und Einbindung von 360°-Rundgängen';
$lines[] = '- [Über uns](' . visitfy_page_url('about', true) . ')';
$lines[] = '- [Kontakt](' . visitfy_page_url('kontakt', true) . '): Angebot für einen 360°-Rundgang anfragen';
$lines[] = '';
$lines[] = '## Kontakt';
$lines[] = '';
$lines[] = '- E-Mail: info@visitfy.de';
$lines[] = '- Standort: Flensburg, Schleswig-Holstein, Deutschland';

echo implode("\n", $lines) . "\n";
