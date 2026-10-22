<?php
/**
 * Visitfy3 – partials/formguard.php
 * Unsichtbarer Schutz für Formulare – ohne Captcha, ohne Cookie, ohne Zusatzfeld für Besucher.
 *
 *  1. Formular-Token: Zeitstempel + Signatur mit einem Server-Geheimnis (assets/data/cache/.form-secret).
 *     Wer direkt an den Handler postet, ohne die Seite geladen zu haben, hat kein gültiges Token.
 *     Wer schneller als 3 Sekunden nach dem Laden abschickt, ist kein Mensch.
 *  2. Begrenzung pro Absender: höchstens 3 erfolgreiche Anfragen pro Stunde. Die IP wird dafür nur als
 *     nicht umkehrbarer Kurzwert gespeichert und nach einer Stunde verworfen (assets/data/cache/form-rate.json).
 *  3. Link-Spam: Nachrichten mit vielen Links oder Links in Name/Stadt werden still verworfen.
 */
require_once __DIR__ . '/stats.php';

if (!function_exists('visitfy_formguard_secret')) {

    function visitfy_formguard_secret(): string
    {
        static $secret = null;
        if ($secret !== null) {
            return $secret;
        }
        $dir = dirname(__DIR__) . '/assets/data/cache';
        $file = $dir . '/.form-secret';
        $value = is_file($file) ? trim((string)@file_get_contents($file)) : '';
        if (strlen($value) < 32) {
            $value = bin2hex(random_bytes(32));
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (@file_put_contents($file, $value, LOCK_EX) === false) {
                // Ordner nicht beschreibbar: festes Geheimnis aus dem Installationspfad (schwächer, aber stabil)
                $value = hash('sha256', __FILE__ . php_uname('n'));
            }
        }
        return $secret = $value;
    }

    /** Token für ein Formular (in ein verstecktes Feld „ft“ schreiben). */
    function visitfy_formguard_token(string $form): string
    {
        /* Begrenzungsdatei aufräumen: wurde sie seit einer Stunde nicht angefasst, ist jeder Eintrag abgelaufen */
        $rate = dirname(__DIR__) . '/assets/data/cache/form-rate.json';
        if (is_file($rate) && filemtime($rate) < time() - 3600) {
            @unlink($rate);
        }
        $ts = (string)time();
        return $ts . '.' . substr(hash_hmac('sha256', $form . '|' . $ts, visitfy_formguard_secret()), 0, 32);
    }

    /**
     * Token prüfen. Rückgabe:
     *   'ok'        gültig
     *   'missing'   kein Token (typisch für Bots, die direkt posten)
     *   'invalid'   Signatur passt nicht (Bot – oder Geheimnis wurde erneuert)
     *   'too_fast'  schneller abgeschickt als ein Mensch tippen kann
     *   'expired'   Seite war sehr lange offen
     */
    function visitfy_formguard_check(string $form, string $token, int $minSeconds = 3, int $maxSeconds = 604800): string
    {
        $token = trim($token);
        if ($token === '') {
            return 'missing';
        }
        if (!preg_match('/^(\d{9,11})\.([a-f0-9]{32})$/', $token, $m)) {
            return 'invalid';
        }
        $expected = substr(hash_hmac('sha256', $form . '|' . $m[1], visitfy_formguard_secret()), 0, 32);
        if (!hash_equals($expected, $m[2])) {
            return 'invalid';
        }
        $age = time() - (int)$m[1];
        if ($age < $minSeconds) {
            return 'too_fast';
        }
        if ($age > $maxSeconds) {
            return 'expired';
        }
        return 'ok';
    }

    /**
     * Begrenzung pro Absender. $record = false prüft nur, $record = true zählt eine erfolgreiche Anfrage.
     * Gibt true zurück, wenn (noch) erlaubt. Im Zweifel (Datei nicht beschreibbar) wird nie blockiert.
     */
    function visitfy_formguard_rate(string $form, bool $record = false, int $max = 3, int $window = 3600): bool
    {
        $ip = function_exists('visitfy_stats_client_ip') ? visitfy_stats_client_ip() : (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $key = substr(hash_hmac('sha256', $form . '|' . $ip, visitfy_formguard_secret()), 0, 20);
        $dir = dirname(__DIR__) . '/assets/data/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $fh = @fopen($dir . '/form-rate.json', 'c+');
        if (!$fh) {
            return true;
        }
        flock($fh, LOCK_EX);
        $data = json_decode((string)stream_get_contents($fh), true);
        $data = is_array($data) ? $data : [];
        $now = time();
        foreach ($data as $k => $times) {
            $data[$k] = array_values(array_filter((array)$times, static fn($t) => (int)$t > $now - $window));
            if (!$data[$k]) {
                unset($data[$k]);
            }
        }
        $allowed = count($data[$key] ?? []) < $max;
        if ($record && $allowed) {
            $data[$key][] = $now;
        }
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($data));
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
        return $allowed;
    }

    /** Typischer Link-Spam: mehr als 2 Links in der Nachricht oder überhaupt ein Link in kurzen Feldern. */
    function visitfy_formguard_is_spam(string $message, array $shortFields = []): bool
    {
        $links = preg_match_all('~(https?://|www\.)~i', $message);
        if ($links > 2) {
            return true;
        }
        foreach ($shortFields as $field) {
            if (preg_match('~(https?://|www\.|\[url|<a\s)~i', (string)$field)) {
                return true;
            }
        }
        return false;
    }
}
