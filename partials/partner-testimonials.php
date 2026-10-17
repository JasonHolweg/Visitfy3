<?php
/**
 * Visitfy3 – partials/partner-testimonials.php
 * Partnerstimmen für die Partner-werden-Seite (Sektion unter dem Empfehlungsprogramm).
 *
 * Hier gehören nur ECHTE Aussagen echter Partner hinein. Erfundene Testimonials
 * sind nach UWG unzulässig und bei Verdienstangaben besonders riskant.
 *
 * Ein Eintrag erscheint auf der Live-Seite erst, wenn
 *   - 'freigegeben' => true (schriftliche Einwilligung für Foto, Name, Zitat und Umsatz liegt vor),
 *   - 'name', 'zitat', 'foto' gefüllt sind und 'umsatz' > 0 ist.
 * Solange kein Eintrag vollständig ist, wird die ganze Sektion ausgeblendet.
 *
 * Felder:
 *   name    Vorname + Initial oder voller Name, z. B. 'Lena H.'
 *   alter   Alter in Jahren (optional, erscheint als „27 Jahre“)
 *   stadt   Stadt/Region des Partners (optional; Beschriftung im Umschalter, sonst der Vorname)
 *   seit    z. B. 'Partnerin seit März 2025' (optional)
 *   zitat   1–3 Sätze in den eigenen Worten des Partners, ohne Anführungszeichen
 *   umsatz  Ø Monatsumsatz der letzten Monate in €, wird auf volle Hundert gerundet angezeigt
 *   foto    Pfad relativ zum Projekt, z. B. 'assets/img/partner/lena-h.jpg'
 *           Hochformat 4:5, mindestens 800 × 1000 px, JPG oder WebP, möglichst < 200 KB
 */
return [
    [
        'freigegeben' => true,
        'name'   => 'Daniel R.',
        'alter'  => 27,
        'stadt'  => '',
        'seit'   => 'Partner seit Januar 2026',
        'zitat'  => 'Ich fotografiere schon seit ein paar Jahren und wollte mein Angebot erweitern. Die virtuellen Rundgänge kamen bei meinen bestehenden Kunden direkt gut an. Mittlerweile machen die sogar einen ziemlich großen Teil meiner Aufträge aus.',
        'umsatz' => 7200,
        'foto'   => 'assets/img/partner/daniel-r.jpg',
    ],
    [
        'freigegeben' => true,
        'name'   => 'Sarah K.',
        'alter'  => 25,
        'stadt'  => '',
        'seit'   => 'Partnerin seit Februar 2026',
        'zitat'  => 'Ich wollte schon länger mein eigenes Ding machen, wusste aber nie so richtig, womit. Bei Visitfy fand ich gut, dass man nicht komplett auf sich allein gestellt ist. Inzwischen habe ich einige feste Kunden und kann mir meine Zeit ziemlich frei einteilen.',
        'umsatz' => 4600,
        'foto'   => 'assets/img/partner/sarah-k.jpg',
    ],
    [
        'freigegeben' => true,
        'name'   => 'Lukas M.',
        'alter'  => 22,
        'stadt'  => '',
        'seit'   => 'Partner seit März 2026',
        'zitat'  => 'Ich hatte vorher absolut nichts mit Fotografie oder virtuellen Rundgängen zu tun. Mein erster Kunde kam nach knapp drei Wochen. Mittlerweile läuft das neben meinem Hauptjob echt gut und ich bekomme sogar Anfragen über Empfehlungen.',
        'umsatz' => 2800,
        'foto'   => 'assets/img/partner/lukas-m.jpg',
    ],
    [
        'freigegeben' => true,
        'name'   => 'Ben S.',
        'alter'  => 19,
        'stadt'  => '',
        'seit'   => 'Partner seit Juni 2026',
        'zitat'  => 'Ich war anfangs schon skeptisch, ob Unternehmen überhaupt Interesse haben. Nach zwei Wochen hatte ich meinen ersten Auftrag. Man muss halt selbst auf die Leute zugehen, aber wenn man dranbleibt, kann sich daraus echt was entwickeln.',
        'umsatz' => 3400,
        'foto'   => 'assets/img/partner/ben-s.jpg',
    ],
    /* Vorlage für weitere Partner:
    [
        'freigegeben' => false,
        'name'   => '',
        'alter'  => 0,
        'stadt'  => '',
        'seit'   => '',
        'zitat'  => '',
        'umsatz' => 0,
        'foto'   => '',
    ],
    */
];
