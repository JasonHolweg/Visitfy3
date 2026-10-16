<?php
/**
 * Visitfy3 – partials/partner-network.php
 * Zentrale Zahlen + Standorte für die Partner-werden-Seite.
 * Hier pflegen, wenn neue Partner dazukommen oder sich die Durchschnitte ändern.
 *
 * Koordinaten (x/y) liegen im viewBox der Karte (siehe partials/germany-map-paths.php).
 * Neue Stadt? Lat/Lon mit derselben Projektion umrechnen (siehe Kommentar dort)
 * oder grob zwischen benachbarten Städten interpolieren.
 */
return [
    'stand' => 'Oktober 2026',

    'stats' => [
        'partners'      => 13,     // aktive Partner
        'avg_revenue'   => 7842,   // Ø Monatsumsatz pro Partner in €
        'avg_tours'     => 11,     // Ø verkaufte Rundgänge pro Monat
    ],

    'referral' => [
        'percent' => 35,   // Prämie in % vom Umsatz …
        'sales'   => 2,    // … der ersten N Verkäufe des geworbenen Partners
    ],

    // Aktive Partner-Standorte (Reihenfolge = Animationsreihenfolge, Nord → Süd)
    'active' => [
        ['name' => 'Flensburg',   'region' => 'Schleswig-Holstein', 'x' => 226.0, 'y' => 41.6,  'hq' => true],
        ['name' => 'Schleswig',   'region' => 'Schleswig-Holstein', 'x' => 232.3, 'y' => 67.6],
        ['name' => 'Kiel',        'region' => 'Schleswig-Holstein', 'x' => 262.3, 'y' => 85.8],
        ['name' => 'Rendsburg',   'region' => 'Schleswig-Holstein', 'x' => 237.2, 'y' => 87.3],
        ['name' => 'Hamburg',     'region' => 'Hamburg',            'x' => 254.8, 'y' => 157.9],
        ['name' => 'Hannover',    'region' => 'Niedersachsen',      'x' => 239.3, 'y' => 267.4],
        ['name' => 'Osnabrück',   'region' => 'Niedersachsen',      'x' => 143.1, 'y' => 274.4],
        ['name' => 'Berlin-Neukölln', 'region' => 'Berlin',         'x' => 449.8, 'y' => 254.2],
        ['name' => 'Brandenburg', 'region' => 'Brandenburg',        'x' => 398.6, 'y' => 262.4],
        ['name' => 'Rheingau',    'region' => 'Hessen',             'x' => 140.7, 'y' => 484.4],
        ['name' => 'Hanau',       'region' => 'Hessen',             'x' => 189.3, 'y' => 476.5],
        ['name' => 'Nahe',        'region' => 'Rheinland-Pfalz',    'x' => 125.0, 'y' => 501.1],
        ['name' => 'Stuttgart',   'region' => 'Baden-Württemberg',  'x' => 202.5, 'y' => 602.8],
    ],

    // Gebiete, in denen aktiv Partner gesucht werden (Fokus Süden)
    'open' => [
        ['name' => 'Würzburg',   'x' => 250.5, 'y' => 508.6],
        ['name' => 'Nürnberg',   'x' => 318.4, 'y' => 540.1],
        ['name' => 'Karlsruhe',  'x' => 155.1, 'y' => 580.2],
        ['name' => 'Regensburg', 'x' => 381.5, 'y' => 580.1],
        ['name' => 'Ulm',        'x' => 251.8, 'y' => 638.4],
        ['name' => 'Augsburg',   'x' => 308.2, 'y' => 641.2],
        ['name' => 'München',    'x' => 350.6, 'y' => 662.5],
        ['name' => 'Freiburg',   'x' => 117.5, 'y' => 673.2],
    ],
];
