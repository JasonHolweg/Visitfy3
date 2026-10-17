<?php
/**
 * Visitfy3 – partials/stats.php
 * Cookielose Besucherstatistik – ohne Cookies, ohne LocalStorage, ohne gespeicherte IP-Adressen.
 *
 * Speicherort: assets/data/stats/YYYY-MM.jsonl (eine Zeile pro Ereignis, Ordner per .htaccess gesperrt).
 * Besucher werden über einen Tages-Hash aus Salt + IP + User-Agent gezählt. Das Salt wird jeden Tag
 * neu erzeugt und nach zwei Tagen gelöscht – Besuche sind dadurch nicht über Tage hinweg verknüpfbar.
 *
 * Alles wird serverseitig beim Ausliefern der Seite erfasst – ohne JavaScript, ohne Zugriff auf das Endgerät.
 *
 * Ereignisse (Feld k):
 *   pv   Seitenaufruf durch einen Menschen (Pfad, Quelle aus dem Referer, Gerät aus dem User-Agent, HTTP-Status)
 *   bot  Aufruf durch einen bekannten Crawler (Google, Bing, ChatGPT, Claude, Perplexity …)
 *   ev   Ziel-Ereignis, z. B. lead_kontakt oder lead_partner (im Formular-Handler)
 */

if (!function_exists('visitfy_stats_dir')) {

    function visitfy_stats_dir(): string
    {
        $dir = dirname(__DIR__) . '/assets/data/stats';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $guard = $dir . '/.htaccess';
        if (is_dir($dir) && !is_file($guard)) {
            @file_put_contents($guard, "Require all denied\n");
        }
        return $dir;
    }

    /** Hängt eine Zeile an die Monatsdatei an. Fehler werden still ignoriert – Statistik darf nie die Seite stören. */
    function visitfy_stats_append(array $row): void
    {
        $dir = visitfy_stats_dir();
        if (!is_dir($dir) || !is_writable($dir)) {
            return;
        }
        $file = $dir . '/' . gmdate('Y-m') . '.jsonl';
        if (is_file($file) && filesize($file) > 60 * 1024 * 1024) {
            return; /* Notbremse gegen Spam */
        }
        $row = ['t' => gmdate('Y-m-d\TH:i:s\Z')] + $row;
        @file_put_contents($file, json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    }

    /** Tages-Salt; ältere Salts werden gelöscht. */
    function visitfy_stats_salt(): string
    {
        $dir = visitfy_stats_dir();
        $day = gmdate('Y-m-d');
        $file = $dir . '/.salt-' . $day;
        if (is_file($file)) {
            $salt = (string)@file_get_contents($file);
            if (strlen($salt) >= 32) {
                return $salt;
            }
        }
        $salt = bin2hex(random_bytes(32));
        @file_put_contents($file, $salt, LOCK_EX);
        foreach ((array)glob($dir . '/.salt-*') as $old) {
            if (is_string($old) && $old !== $file && filemtime($old) < time() - 2 * 86400) {
                @unlink($old);
            }
        }
        return $salt;
    }

    /** Echte Besucher-IP: visitfy.de läuft über Cloudflare, REMOTE_ADDR ist dort die IP des Cloudflare-Knotens. */
    function visitfy_stats_client_ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR'] as $key) {
            $ip = trim((string)($_SERVER[$key] ?? ''));
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return '';
    }

    function visitfy_stats_visitor(): string
    {
        $ip = visitfy_stats_client_ip();
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        return substr(hash('sha256', visitfy_stats_salt() . '|' . $ip . '|' . $ua), 0, 16);
    }

    /** Bekannte Crawler, deren Besuche wir zählen wollen (Suchmaschinen + KI). */
    function visitfy_stats_bot_name(string $ua): ?string
    {
        static $bots = [
            'OAI-SearchBot'      => 'ChatGPT-Suche',
            'ChatGPT-User'       => 'ChatGPT (Nutzer)',
            'GPTBot'             => 'OpenAI GPTBot',
            'Claude-SearchBot'   => 'Claude-Suche',
            'Claude-User'        => 'Claude (Nutzer)',
            'ClaudeBot'          => 'ClaudeBot',
            'PerplexityBot'      => 'Perplexity',
            'Perplexity-User'    => 'Perplexity (Nutzer)',
            'Google-CloudVertexBot' => 'Google Vertex',
            'Googlebot'          => 'Googlebot',
            'GoogleOther'        => 'GoogleOther',
            'bingbot'            => 'Bingbot',
            'Applebot'           => 'Applebot',
            'DuckDuckBot'        => 'DuckDuckGo',
            'YandexBot'          => 'Yandex',
            'meta-externalagent' => 'Meta KI',
            'Amazonbot'          => 'Amazonbot',
            'CCBot'              => 'Common Crawl',
            'Bytespider'         => 'Bytespider',
            'MistralAI-User'     => 'Mistral',
            'cohere-ai'          => 'Cohere',
        ];
        foreach ($bots as $needle => $name) {
            if (stripos($ua, $needle) !== false) {
                return $name;
            }
        }
        return null;
    }

    function visitfy_stats_is_bot(string $ua): bool
    {
        if ($ua === '' || visitfy_stats_bot_name($ua) !== null) {
            return true;
        }
        return (bool)preg_match('/bot|crawl|spider|slurp|headless|lighthouse|pagespeed|preview|monitor|uptime|curl|wget|python|httpclient|java\/|go-http|axios|node-fetch|scrapy|facebookexternalhit|whatsapp|telegram/i', $ua);
    }

    /** Quelle aus Referrer bzw. utm_source. */
    function visitfy_stats_source(string $refHost, string $utm = ''): string
    {
        $utm = strtolower(trim($utm));
        if ($utm !== '') {
            if (strpos($utm, 'chatgpt') !== false || strpos($utm, 'openai') !== false) return 'ChatGPT';
            if (strpos($utm, 'perplexity') !== false) return 'Perplexity';
            return 'utm: ' . substr(preg_replace('/[^a-z0-9._-]/', '', $utm), 0, 40);
        }
        $h = strtolower(preg_replace('/^www\./', '', $refHost));
        if ($h === '') return 'Direkt';
        $map = [
            'chatgpt.com' => 'ChatGPT', 'chat.openai.com' => 'ChatGPT',
            'perplexity.ai' => 'Perplexity',
            'gemini.google.com' => 'Gemini',
            'copilot.microsoft.com' => 'Copilot',
            'claude.ai' => 'Claude',
            'you.com' => 'You.com', 'phind.com' => 'Phind',
            'bing.com' => 'Bing', 'duckduckgo.com' => 'DuckDuckGo', 'ecosia.org' => 'Ecosia',
            'search.yahoo.com' => 'Yahoo', 'yahoo.com' => 'Yahoo', 'startpage.com' => 'Startpage', 'qwant.com' => 'Qwant',
            'instagram.com' => 'Instagram', 'l.instagram.com' => 'Instagram',
            'facebook.com' => 'Facebook', 'm.facebook.com' => 'Facebook', 'l.facebook.com' => 'Facebook', 'lm.facebook.com' => 'Facebook',
            'linkedin.com' => 'LinkedIn', 'lnkd.in' => 'LinkedIn',
            't.co' => 'X', 'x.com' => 'X', 'twitter.com' => 'X',
            'youtube.com' => 'YouTube',
        ];
        if (isset($map[$h])) return $map[$h];
        if (preg_match('/(^|\.)google\.[a-z.]+$/', $h)) return 'Google';
        if (preg_match('/(^|\.)bing\.com$/', $h)) return 'Bing';
        if (preg_match('/(^|\.)facebook\.com$/', $h)) return 'Facebook';
        if (preg_match('/(^|\.)instagram\.com$/', $h)) return 'Instagram';
        return $h;
    }

    /** Quellen, die als KI-Suche zählen (für die Auswertung). */
    function visitfy_stats_ai_sources(): array
    {
        return ['ChatGPT', 'Perplexity', 'Gemini', 'Copilot', 'Claude', 'You.com', 'Phind'];
    }

    /** Pfad säubern: nur Pfad, ohne Query, ohne Domain, max. 200 Zeichen. */
    function visitfy_stats_clean_path(string $path): string
    {
        $path = (string)parse_url($path, PHP_URL_PATH);
        if ($path === '' || $path[0] !== '/') {
            $path = '/';
        }
        $path = preg_replace('#/{2,}#', '/', $path);
        if ($path !== '/' ) {
            $path = rtrim($path, '/');
        }
        return substr($path, 0, 200);
    }

    /** Gerätetyp aus dem User-Agent (wird ohnehin mit jeder Anfrage gesendet). */
    function visitfy_stats_device(string $ua): string
    {
        if (preg_match('/iPad|Tablet|PlayBook|Silk|Kindle/i', $ua) || (stripos($ua, 'Android') !== false && stripos($ua, 'Mobile') === false)) {
            return 't';
        }
        if (preg_match('/Mobi|iPhone|iPod|Android|Windows Phone/i', $ua)) {
            return 'm';
        }
        return 'd';
    }

    /**
     * Aufruf der aktuellen Seite zählen (wird in partials/head.php aufgerufen):
     * bekannte Crawler als „bot“, Menschen als „pv“. Nur GET, keine Vorab-Ladevorgänge des Browsers.
     */
    function visitfy_stats_log_request(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return;
        }
        $purpose = strtolower((string)($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? $_SERVER['HTTP_X_MOZ'] ?? ''));
        if (strpos($purpose, 'prefetch') !== false || strpos($purpose, 'prerender') !== false) {
            return;
        }
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $path = visitfy_stats_clean_path($uri);
        $status = http_response_code() ?: 200;

        $bot = $ua !== '' ? visitfy_stats_bot_name($ua) : null;
        if ($bot !== null) {
            visitfy_stats_append(['k' => 'bot', 'b' => $bot, 'p' => $path, 'c' => $status]);
            return;
        }
        if (visitfy_stats_is_bot($ua)) {
            return;
        }

        $query = [];
        parse_str((string)parse_url($uri, PHP_URL_QUERY), $query);
        if (($query['__deploy'] ?? '') === 'check') {
            $path = '/__deploy-check';
        }

        $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
        $refHost = $ref !== '' ? strtolower((string)parse_url($ref, PHP_URL_HOST)) : '';
        $ownHost = strtolower(preg_replace(['/^www\./', '/:\d+$/'], '', (string)($_SERVER['HTTP_HOST'] ?? '')));
        $internal = $refHost !== '' && preg_replace('/^www\./', '', $refHost) === $ownHost;
        $source = $internal ? 'Intern' : visitfy_stats_source($refHost, (string)($query['utm_source'] ?? ''));

        $row = ['k' => 'pv', 'p' => $path, 's' => $source, 'v' => visitfy_stats_visitor(), 'd' => visitfy_stats_device($ua)];
        if ($status !== 200) {
            $row['c'] = $status;
        }
        visitfy_stats_append($row);
    }

    /** Serverseitig: Ziel-Ereignis zählen (z. B. erfolgreich abgeschickte Anfrage). */
    function visitfy_stats_event(string $name, string $path = ''): void
    {
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (visitfy_stats_is_bot($ua)) {
            return;
        }
        $name = substr(preg_replace('/[^a-z0-9_]/', '', strtolower($name)), 0, 40);
        if ($path === '') {
            $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
            $path = $ref !== '' ? (string)parse_url($ref, PHP_URL_PATH) : '/';
        }
        visitfy_stats_append(['k' => 'ev', 'e' => $name, 'p' => visitfy_stats_clean_path($path), 'v' => visitfy_stats_visitor()]);
    }
}
