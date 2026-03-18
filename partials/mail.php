<?php
/**
 * Mailgun helpers for Visitfy contact forms.
 */

if (!function_exists('visitfy_mail_config_path')) {
    function visitfy_mail_config_path(): string
    {
        return dirname(__DIR__) . '/config.mail.php';
    }
}

if (!function_exists('visitfy_mail_defaults')) {
    function visitfy_mail_defaults(): array
    {
        return [
            'MAILGUN_API_KEY' => '',
            'MAILGUN_DOMAIN' => '',
            'MAILGUN_API_BASE' => 'https://api.mailgun.net',
            'MAILGUN_FROM_EMAIL' => 'info@visitfy.de',
            'MAILGUN_FROM_NAME' => 'Visitfy',
            'MAILGUN_TO_EMAIL' => 'info@visitfy.de',
        ];
    }
}

if (!function_exists('visitfy_load_mail_config')) {
    function visitfy_load_mail_config(): array
    {
        $config = visitfy_mail_defaults();
        $configPath = visitfy_mail_config_path();

        if (is_file($configPath)) {
            $loaded = require $configPath;
            if (is_array($loaded)) {
                foreach ($config as $key => $defaultValue) {
                    if (array_key_exists($key, $loaded)) {
                        $config[$key] = trim((string)$loaded[$key]);
                    }
                }
            }
        }

        foreach (array_keys($config) as $key) {
            $envValue = getenv($key);
            if ($envValue !== false && $envValue !== '') {
                $config[$key] = trim((string)$envValue);
            }
        }

        $config['MAILGUN_API_BASE'] = rtrim((string)$config['MAILGUN_API_BASE'], '/');

        return $config;
    }
}

if (!function_exists('visitfy_mail_is_configured')) {
    function visitfy_mail_is_configured(array $config): bool
    {
        return $config['MAILGUN_API_KEY'] !== ''
            && $config['MAILGUN_DOMAIN'] !== ''
            && $config['MAILGUN_FROM_EMAIL'] !== ''
            && $config['MAILGUN_TO_EMAIL'] !== '';
    }
}

if (!function_exists('visitfy_format_from_header')) {
    function visitfy_format_from_header(string $name, string $email): string
    {
        $safeName = trim(preg_replace('/[\r\n]+/', ' ', $name) ?? '');
        if ($safeName === '') {
            return $email;
        }

        return sprintf('"%s" <%s>', addcslashes($safeName, '"\\'), $email);
    }
}

if (!function_exists('visitfy_send_via_mailgun')) {
    function visitfy_send_via_mailgun(array $config, array $payload): array
    {
        $endpoint = $config['MAILGUN_API_BASE'] . '/v3/' . rawurlencode($config['MAILGUN_DOMAIN']) . '/messages';
        $encodedPayload = http_build_query($payload);

        if (function_exists('curl_init')) {
            return visitfy_send_via_mailgun_curl($endpoint, $config['MAILGUN_API_KEY'], $encodedPayload);
        }

        return visitfy_send_via_mailgun_stream($endpoint, $config['MAILGUN_API_KEY'], $encodedPayload);
    }
}

if (!function_exists('visitfy_send_contact_mail')) {
    function visitfy_send_contact_mail(array $config, array $payload): array
    {
        if (!visitfy_mail_is_configured($config)) {
            return ['ok' => false, 'error' => 'Mailgun ist nicht konfiguriert (API-Key oder Domain fehlt).'];
        }

        $result = visitfy_send_via_mailgun($config, $payload);
        if ($result['ok']) {
            $result['provider'] = 'mailgun';
        }

        return $result;
    }
}

if (!function_exists('visitfy_send_via_mailgun_curl')) {
    function visitfy_send_via_mailgun_curl(string $endpoint, string $apiKey, string $encodedPayload): array
    {
        $ch = curl_init($endpoint);
        if ($ch === false) {
            return ['ok' => false, 'error' => 'cURL konnte nicht initialisiert werden.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $encodedPayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => 'api:' . $apiKey,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 20,
        ]);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            return ['ok' => false, 'error' => 'cURL error: ' . $curlError];
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            return ['ok' => false, 'error' => 'HTTP ' . $statusCode . ' - ' . trim((string)$responseBody)];
        }

        return ['ok' => true];
    }
}

if (!function_exists('visitfy_send_via_mailgun_stream')) {
    function visitfy_send_via_mailgun_stream(string $endpoint, string $apiKey, string $encodedPayload): array
    {
        $headers = [
            'Authorization: Basic ' . base64_encode('api:' . $apiKey),
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($encodedPayload),
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $encodedPayload,
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ]);

        $responseBody = @file_get_contents($endpoint, false, $context);
        $statusCode = visitfy_extract_http_status_code($http_response_header ?? []);

        if ($responseBody === false) {
            return ['ok' => false, 'error' => 'HTTP request failed'];
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            return ['ok' => false, 'error' => 'HTTP ' . $statusCode . ' - ' . trim((string)$responseBody)];
        }

        return ['ok' => true];
    }
}

if (!function_exists('visitfy_extract_http_status_code')) {
    function visitfy_extract_http_status_code(array $headers): int
    {
        foreach ($headers as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', (string)$header, $matches)) {
                return (int)$matches[1];
            }
        }

        return 0;
    }
}

if (!function_exists('visitfy_send_via_php_mail')) {
    function visitfy_send_via_php_mail(array $payload): array
    {
        $to = trim((string)($payload['to'] ?? ''));
        $subject = trim((string)($payload['subject'] ?? ''));
        $text = (string)($payload['text'] ?? '');
        $html = (string)($payload['html'] ?? '');
        $from = trim((string)($payload['from'] ?? ''));
        $replyTo = trim((string)($payload['h:Reply-To'] ?? ''));

        if ($to === '' || $subject === '' || ($text === '' && $html === '')) {
            return ['ok' => false, 'error' => 'Unvollstaendige Maildaten fuer PHP mail().'];
        }

        $headers = [];
        if ($from !== '') {
            $headers[] = 'From: ' . visitfy_mail_safe_header_value($from);
        }
        if ($replyTo !== '') {
            $headers[] = 'Reply-To: ' . visitfy_mail_safe_header_value($replyTo);
        }
        $headers[] = 'MIME-Version: 1.0';

        if ($html !== '') {
            $boundary = 'visitfy-' . bin2hex(random_bytes(12));
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
            $body = "--{$boundary}\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: 8bit\r\n\r\n"
                . ($text !== '' ? $text : trim(strip_tags($html))) . "\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: 8bit\r\n\r\n"
                . $html . "\r\n"
                . "--{$boundary}--";
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $body = $text;
        }

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $ok = @mail($to, $encodedSubject, $body, implode("\r\n", $headers));

        return $ok
            ? ['ok' => true]
            : ['ok' => false, 'error' => 'mail() hat die Nachricht nicht angenommen.'];
    }
}

if (!function_exists('visitfy_email_wrap')) {
    /**
     * Wraps $innerHtml in the branded Visitfy email shell.
     * $accentRow: optional HTML row inserted between logo and card (e.g. a coloured bar).
     */
    function visitfy_email_wrap(string $innerHtml, string $footerHtml = '', string $siteUrl = 'https://visitfy.de'): string
    {
        $logo = '<img src="' . $siteUrl . '/assets/img/logo-white.svg"'
            . ' alt="Visitfy" width="180" height="56"'
            . ' style="display:block;border:0;max-width:180px;">';

        return '<!DOCTYPE html>'
            . '<html lang="de"><head>'
            . '<meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1.0">'
            . '</head>'
            . '<body style="margin:0;padding:0;background-color:#000000;'
            .   'font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',\'Helvetica Neue\',Arial,sans-serif;'
            .   '-webkit-font-smoothing:antialiased;">'

            /* outer */
            . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation"'
            .   ' style="background-color:#000000;padding:48px 20px;">'
            . '<tr><td align="center">'
            . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:580px;">'

            /* logo row */
            . '<tr><td style="padding:0 0 28px 4px;">' . $logo . '</td></tr>'

            /* card */
            . '<tr><td style="background-color:#0f0f0f;border-radius:20px;'
            .   'border:1px solid rgba(255,255,255,0.11);overflow:hidden;">'

            /* accent top line */
            . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation">'
            . '<tr><td style="height:2px;background-color:#ffffff;font-size:0;line-height:0;">&nbsp;</td></tr>'
            . '</table>'

            /* content */
            . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation">'
            . '<tr><td style="padding:44px 48px 40px;">'
            . $innerHtml
            . '</td></tr>'
            . '</table>'

            /* footer inside card */
            . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation">'
            . '<tr><td style="padding:20px 48px 24px;border-top:1px solid rgba(255,255,255,0.08);">'
            . ($footerHtml !== '' ? $footerHtml
                : '<p style="margin:0;font-size:12px;color:rgba(255,255,255,0.28);line-height:1.6;">'
                . 'Visitfy &middot; Flensburg, Deutschland</p>')
            . '</td></tr>'
            . '</table>'

            . '</td></tr>' /* end card */
            . '</table>'
            . '</td></tr>'
            . '</table>'
            . '</body></html>';
    }
}

if (!function_exists('visitfy_mail_safe_header_value')) {
    function visitfy_mail_safe_header_value(string $value): string
    {
        return trim((string)preg_replace('/[\r\n]+/', ' ', $value));
    }
}
