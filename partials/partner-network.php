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

    // Fokus-Gebiete: freie Städte im Süden, auf der Karte beschriftet (wichtigste zuerst)
    'open' => [
        ['name' => 'München',    'x' => 350.6, 'y' => 662.5],
        ['name' => 'Nürnberg',   'x' => 318.4, 'y' => 540.1],
        ['name' => 'Augsburg',   'x' => 308.2, 'y' => 641.2],
        ['name' => 'Karlsruhe',  'x' => 155.1, 'y' => 580.2],
        ['name' => 'Freiburg',   'x' => 117.5, 'y' => 673.2],
        ['name' => 'Regensburg', 'x' => 381.5, 'y' => 580.1],
        ['name' => 'Würzburg',   'x' => 250.5, 'y' => 508.6],
        ['name' => 'Ulm',        'x' => 251.8, 'y' => 638.4],
    ],

    // Weitere freie Regionen: auf der Karte als kleine Punkte ohne Beschriftung
    'free' => [
        ['name' => 'Oldenburg', 'x' => 155.0, 'y' => 194.2],
        ['name' => 'Bremen', 'x' => 187.8, 'y' => 201.0],
        ['name' => 'Münster', 'x' => 118.0, 'y' => 303.3],
        ['name' => 'Bielefeld', 'x' => 170.2, 'y' => 298.5],
        ['name' => 'Dortmund', 'x' => 107.1, 'y' => 344.6],
        ['name' => 'Düsseldorf', 'x' => 65.6, 'y' => 369.5],
        ['name' => 'Köln', 'x' => 75.3, 'y' => 397.0],
        ['name' => 'Aachen', 'x' => 22.9, 'y' => 409.4],
        ['name' => 'Kassel', 'x' => 223.7, 'y' => 366.4],
        ['name' => 'Göttingen', 'x' => 249.3, 'y' => 345.4],
        ['name' => 'Braunschweig', 'x' => 284.6, 'y' => 277.6],
        ['name' => 'Magdeburg', 'x' => 347.7, 'y' => 290.8],
        ['name' => 'Leipzig', 'x' => 392.3, 'y' => 362.7],
        ['name' => 'Dresden', 'x' => 473.0, 'y' => 386.8],
        ['name' => 'Chemnitz', 'x' => 425.9, 'y' => 409.5],
        ['name' => 'Erfurt', 'x' => 314.3, 'y' => 397.2],
        ['name' => 'Rostock', 'x' => 370.4, 'y' => 106.3],
        ['name' => 'Schwerin', 'x' => 332.8, 'y' => 149.7],
        ['name' => 'Koblenz', 'x' => 110.1, 'y' => 452.7],
        ['name' => 'Trier', 'x' => 50.6, 'y' => 506.7],
        ['name' => 'Saarbrücken', 'x' => 70.1, 'y' => 555.3],
        ['name' => 'Mannheim', 'x' => 160.1, 'y' => 535.5],
        ['name' => 'Bayreuth', 'x' => 347.7, 'y' => 493.7],
        ['name' => 'Ingolstadt', 'x' => 340.5, 'y' => 603.9],
        ['name' => 'Passau', 'x' => 464.6, 'y' => 619.1],
        ['name' => 'Konstanz', 'x' => 200.2, 'y' => 705.3],
    ],

    // Alle Bundesländer – daraus berechnet die Seite, wo es noch keinen Partner gibt.
    // 'region' bei den aktiven Partnern muss exakt einem dieser Namen entsprechen.
    'states' => [
        'Baden-Württemberg', 'Bayern', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hessen',
        'Mecklenburg-Vorpommern', 'Niedersachsen', 'Nordrhein-Westfalen', 'Rheinland-Pfalz',
        'Saarland', 'Sachsen', 'Sachsen-Anhalt', 'Schleswig-Holstein', 'Thüringen',
    ],
    // Reihenfolge, in der freie Bundesländer genannt werden (Süden zuerst)
    'states_priority' => ['Bayern', 'Nordrhein-Westfalen', 'Sachsen', 'Thüringen', 'Sachsen-Anhalt', 'Mecklenburg-Vorpommern', 'Saarland', 'Bremen'],
];
