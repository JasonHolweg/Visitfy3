<?php
/**
 * Visitfy3 – partials/crm.php
 * Kennzahlen aus dem CRM (Gesamtzahl Rundgänge) für die Website – mit Cache, damit nicht jeder
 * Seitenaufruf das CRM abfragt. Fällt das CRM aus, wird der letzte bekannte Wert weiter angezeigt.
 *
 * Konfiguration: config.crm.php im Projekt-Root (nicht im Git, Vorlage: config.crm.php.example).
 *
 * Platzhalter in Texten (content.json, Admin, Seiten):
 *   {rundgaenge}  Gesamtzahl Rundgänge aus dem CRM, z. B. „1.284“
 *   {partner}     Anzahl aktiver Partner aus partials/partner-network.php
 */

if (!function_exists('visitfy_crm_config')) {

    function visitfy_crm_config(): array
    {
        static $config = null;
        if ($config === null) {
            $file = dirname(__DIR__) . '/config.crm.php';
            $loaded = is_file($file) ? @include $file : null;
            $config = is_array($loaded) ? $loaded : [];
        }
        return $config;
    }

    function visitfy_crm_cache_file(): string
    {
        return dirname(__DIR__) . '/assets/data/cache/crm.json';
    }

    /** Fragt das CRM direkt ab (ohne Cache). Gibt null zurück, wenn nichts konfiguriert ist oder ein Fehler auftritt. */
    function visitfy_crm_fetch_tours(): ?int
    {
        $cfg = visitfy_crm_config();
        $driver = (string)($cfg['driver'] ?? '');
        try {
            if ($driver === 'mysql' || $driver === 'pdo') {
                if (empty($cfg['dsn']) || empty($cfg['tours_sql'])) {
                    return null;
                }
                $pdo = new PDO((string)$cfg['dsn'], (string)($cfg['user'] ?? ''), (string)($cfg['password'] ?? ''), [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 3,
                ]);
                $value = $pdo->query((string)$cfg['tours_sql'])->fetchColumn();
                return is_numeric($value) ? (int)$value : null;
            }
            if ($driver === 'url') {
                if (empty($cfg['url'])) {
                    return null;
                }
                $headers = [];
                if (!empty($cfg['token'])) {
                    $headers[] = 'Authorization: Bearer ' . $cfg['token'];
                }
                $ctx = stream_context_create(['http' => ['timeout' => 3, 'header' => implode("\r\n", $headers)]]);
                $raw = @file_get_contents((string)$cfg['url'], false, $ctx);
                $data = is_string($raw) ? json_decode($raw, true) : null;
                $field = (string)($cfg['tours_field'] ?? 'tours');
                $value = is_array($data) ? ($data[$field] ?? null) : (is_numeric(trim((string)$raw)) ? trim((string)$raw) : null);
                return is_numeric($value) ? (int)$value : null;
            }
        } catch (Throwable $e) {
            error_log('Visitfy CRM: ' . $e->getMessage());
        }
        return null;
    }

    /** Gesamtzahl Rundgänge (gecacht, Standard 1 Stunde). null, solange noch kein Wert bekannt ist. */
    function visitfy_crm_total_tours(): ?int
    {
        static $value = false;
        if ($value !== false) {
            return $value;
        }
        $ttl = (int)(visitfy_crm_config()['cache_seconds'] ?? 3600);
        $file = visitfy_crm_cache_file();
        $cache = is_file($file) ? json_decode((string)@file_get_contents($file), true) : null;
        $cached = is_array($cache) && isset($cache['tours']) ? (int)$cache['tours'] : null;

        if ($cached !== null && (time() - (int)($cache['ts'] ?? 0)) < $ttl) {
            return $value = $cached;
        }
        $fresh = visitfy_crm_fetch_tours();
        if ($fresh !== null) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            @file_put_contents($file, json_encode(['tours' => $fresh, 'ts' => time()]), LOCK_EX);
            return $value = $fresh;
        }
        if ($cached !== null) {
            /* CRM nicht erreichbar: letzten Wert behalten, in 5 Minuten neu versuchen */
            @file_put_contents($file, json_encode(['tours' => $cached, 'ts' => time() - $ttl + 300]), LOCK_EX);
            return $value = $cached;
        }
        /* Noch nie ein Wert: optionaler Ersatzwert aus config.crm.php, damit kein Satz ohne Zahl entsteht */
        $fallback = visitfy_crm_config()['fallback'] ?? null;
        if (!is_numeric($fallback)) {
            return $value = null;
        }
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($file, json_encode(['tours' => (int)$fallback, 'ts' => time() - $ttl + 300]), LOCK_EX);
        return $value = (int)$fallback;
    }

    /** Anzahl aktiver Partner aus partials/partner-network.php */
    function visitfy_partner_count(): int
    {
        static $count = null;
        if ($count === null) {
            $file = __DIR__ . '/partner-network.php';
            $net = is_file($file) ? require $file : [];
            $count = (int)($net['stats']['partners'] ?? 0);
        }
        return $count;
    }

    /** Platzhalter in einem Text ersetzen. $formatted=false liefert reine Ziffern (z. B. für data-target). */
    function visitfy_fill(string $text, bool $formatted = true): string
    {
        if (strpos($text, '{') === false) {
            return $text;
        }
        if (strpos($text, '{rundgaenge}') !== false) {
            $tours = visitfy_crm_total_tours();
            $text = str_replace('{rundgaenge}', $tours === null ? '' : ($formatted ? number_format($tours, 0, ',', '.') : (string)$tours), $text);
        }
        if (strpos($text, '{partner}') !== false) {
            $text = str_replace('{partner}', (string)visitfy_partner_count(), $text);
        }
        return $text;
    }
}
