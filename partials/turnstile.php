<?php
/**
 * Visitfy3 – Cloudflare Turnstile helpers
 *
 * Load with require_once. All functions are guarded with function_exists
 * so this file is safe to include from both frontend and admin context.
 */

if (!function_exists('visitfy_turnstile_config_path')) {
    function visitfy_turnstile_config_path(): string
    {
        return dirname(__DIR__) . '/config.turnstile.php';
    }
}

if (!function_exists('visitfy_turnstile_config')) {
    function visitfy_turnstile_config(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $defaults = [
            'TURNSTILE_SITE_KEY'   => '',
            'TURNSTILE_SECRET_KEY' => '',
            'TURNSTILE_ENABLED'    => '0',
        ];
        $path = visitfy_turnstile_config_path();
        if (!is_file($path)) {
            return $cache = $defaults;
        }
        $loaded = @require $path;
        if (!is_array($loaded)) {
            return $cache = $defaults;
        }
        foreach ($defaults as $key => $val) {
            if (!array_key_exists($key, $loaded)) {
                $loaded[$key] = $val;
            }
        }
        return $cache = $loaded;
    }
}

if (!function_exists('visitfy_turnstile_is_enabled')) {
    function visitfy_turnstile_is_enabled(): bool
    {
        $cfg = visitfy_turnstile_config();
        return !empty($cfg['TURNSTILE_ENABLED'])
            && $cfg['TURNSTILE_ENABLED'] !== '0'
            && (string)($cfg['TURNSTILE_SITE_KEY'] ?? '') !== ''
            && (string)($cfg['TURNSTILE_SECRET_KEY'] ?? '') !== '';
    }
}

if (!function_exists('visitfy_turnstile_site_key')) {
    function visitfy_turnstile_site_key(): string
    {
        return (string)(visitfy_turnstile_config()['TURNSTILE_SITE_KEY'] ?? '');
    }
}

if (!function_exists('visitfy_turnstile_widget')) {
    /**
     * Returns the Turnstile widget HTML, or '' if Turnstile is not enabled.
     * @param string $theme  'dark' | 'light' | 'auto'
     */
    function visitfy_turnstile_widget(string $theme = 'dark'): string
    {
        if (!visitfy_turnstile_is_enabled()) {
            return '';
        }
        $key   = htmlspecialchars(visitfy_turnstile_site_key(), ENT_QUOTES, 'UTF-8');
        $th    = htmlspecialchars($theme, ENT_QUOTES, 'UTF-8');
        return '<div class="cf-turnstile" data-sitekey="' . $key . '" data-theme="' . $th . '" style="margin-bottom:1rem"></div>' . "\n";
    }
}

if (!function_exists('visitfy_turnstile_verify')) {
    /**
     * Server-side token verification via Cloudflare Turnstile API.
     * Returns true when the token is valid, false on any failure.
     */
    function visitfy_turnstile_verify(string $token, string $remoteIp = ''): bool
    {
        if ($token === '') {
            return false;
        }
        $cfg    = visitfy_turnstile_config();
        $secret = (string)($cfg['TURNSTILE_SECRET_KEY'] ?? '');
        if ($secret === '') {
            return false;
        }

        $postFields = ['secret' => $secret, 'response' => $token];
        if ($remoteIp !== '') {
            $postFields['remoteip'] = $remoteIp;
        }

        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($postFields),
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $body = curl_exec($ch);
        curl_close($ch);

        if (!is_string($body) || $body === '') {
            return false;
        }
        $json = json_decode($body, true);
        return is_array($json) && !empty($json['success']);
    }
}
