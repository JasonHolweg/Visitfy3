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
 *   stadt   Stadt/Region des Partners (wird auch als Beschriftung im Umschalter genutzt)
 *   seit    z. B. 'Partnerin seit März 2025' (optional)
 *   zitat   1–3 Sätze in den eigenen Worten des Partners, ohne Anführungszeichen
 *   umsatz  Ø Monatsumsatz der letzten Monate in €, wird auf volle Hundert gerundet angezeigt
 *   foto    Pfad relativ zum Projekt, z. B. 'assets/img/partner/lena-h.jpg'
 *           Hochformat 4:5, mindestens 800 × 1000 px, JPG oder WebP, möglichst < 200 KB
 */
return [
    [
        'freigegeben' => false,
        'name'   => '',
        'stadt'  => 'Hamburg',
        'seit'   => '',
        'zitat'  => '',
        'umsatz' => 0,
        'foto'   => '',
        // Frage für das Zitat: Wie bist du zu Visitfy gekommen, und was hat sich seitdem verändert?
    ],
    [
        'freigegeben' => false,
        'name'   => '',
        'stadt'  => 'Kiel',
        'seit'   => '',
        'zitat'  => '',
        'umsatz' => 0,
        'foto'   => '',
        // Frage: Wie schnell hattest du deine ersten Kunden?
    ],
    [
        'freigegeben' => false,
        'name'   => '',
        'stadt'  => 'Berlin-Neukölln',
        'seit'   => '',
        'zitat'  => '',
        'umsatz' => 0,
        'foto'   => '',
        // Frage: Was gefällt dir an der Arbeit als Partner am meisten?
    ],
    [
        'freigegeben' => false,
        'name'   => '',
        'stadt'  => 'Hanau',
        'seit'   => '',
        'zitat'  => '',
        'umsatz' => 0,
        'foto'   => '',
        // Frage: Wie erlebst du den Support und das Dashboard im Alltag?
    ],
    [
        'freigegeben' => false,
        'name'   => '',
        'stadt'  => 'Stuttgart',
        'seit'   => '',
        'zitat'  => '',
        'umsatz' => 0,
        'foto'   => '',
        // Frage: Was würdest du jemandem sagen, der überlegt, Partner zu werden?
    ],
];
