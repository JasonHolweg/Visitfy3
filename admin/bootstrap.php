<?php
/**
 * Visitfy3 Admin Bootstrap
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function admin_root_path(): string
{
    return dirname(__DIR__);
}

function admin_read_env_file(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $vars = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return [];
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $key = trim($parts[0]);
        $val = trim($parts[1]);
        $val = trim($val, "\"'");
        if ($key !== '') {
            $vars[$key] = $val;
        }
    }

    return $vars;
}

function admin_password(): string
{
    // 1. Check config.admin.php (highest priority - set via admin panel)
    $configPath = admin_config_path();
    if (is_file($configPath)) {
        $loaded = @require $configPath;
        if (is_array($loaded) && isset($loaded['password']) && (string)$loaded['password'] !== '') {
            return (string)$loaded['password'];
        }
    }

    // 2. Check VISITFY_ADMIN_PASSWORD env var
    $fromEnv = getenv('VISITFY_ADMIN_PASSWORD');
    if (is_string($fromEnv) && $fromEnv !== '') {
        return $fromEnv;
    }

    // 3. Check .deploy.env
    $envFileVars = admin_read_env_file(admin_root_path() . '/.deploy.env');
    if (!empty($envFileVars['VISITFY_ADMIN_PASSWORD'])) {
        return (string)$envFileVars['VISITFY_ADMIN_PASSWORD'];
    }

    if (!empty($envFileVars['ADMIN_PASSWORD'])) {
        return (string)$envFileVars['ADMIN_PASSWORD'];
    }

    // 4. Fallback
    return 'visitfy-admin';
}

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function admin_login(string $password): bool
{
    $ok = hash_equals(admin_password(), $password);
    if ($ok) {
        $_SESSION['admin_logged_in'] = true;
    }
    return $ok;
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)($params['secure'] ?? false), (bool)($params['httponly'] ?? true));
    }
    session_destroy();
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
    }
    return (string)$_SESSION['admin_csrf'];
}

function admin_validate_csrf(?string $token): bool
{
    $stored = $_SESSION['admin_csrf'] ?? '';
    return is_string($token) && $token !== '' && is_string($stored) && $stored !== '' && hash_equals($stored, $token);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function admin_editable_files(): array
{
    return [
        'Startseite (index.php)' => 'index.php',
        'Head Partial (partials/head.php)' => 'partials/head.php',
        'Header Partial (partials/header.php)' => 'partials/header.php',
        'Footer Partial (partials/footer.php)' => 'partials/footer.php',
        'About Page (pages/about.php)' => 'pages/about.php',
        'FAQ Page (pages/faq.php)' => 'pages/faq.php',
        'Kontakt Page (pages/kontakt.php)' => 'pages/kontakt.php',
        'Partner Page (pages/partner.php)' => 'pages/partner.php',
        'Impressum (pages/impressum.php)' => 'pages/impressum.php',
        'Datenschutz (pages/datenschutz.php)' => 'pages/datenschutz.php',
        'Stylesheet (assets/css/style.css)' => 'assets/css/style.css',
        'Main Script (assets/js/main.js)' => 'assets/js/main.js',
        'Intro Script (assets/js/intro.js)' => 'assets/js/intro.js',
        'Tour Data (assets/data/tours.json)' => 'assets/data/tours.json',
    ];
}

function admin_upload_folders(): array
{
    return [
        'Allgemeine Bilder (assets/img)' => 'assets/img',
        'Mockup Bilder (assets/img/mockups)' => 'assets/img/mockups',
        'Client Logos (assets/img/client-logos)' => 'assets/img/client-logos',
    ];
}

function admin_content_config_path(): string
{
    return admin_root_path() . '/assets/data/content.json';
}

function admin_script_config_path(): string
{
    return admin_root_path() . '/assets/data/script-config.json';
}

function admin_mail_config_path(): string
{
    return admin_root_path() . '/config.mail.php';
}

function admin_read_json(string $path, array $fallback = []): array
{
    if (!is_file($path)) {
        return $fallback;
    }
    $raw = file_get_contents($path);
    if (!is_string($raw) || $raw === '') {
        return $fallback;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $fallback;
}

function admin_write_json(string $path, array $data): bool
{
    $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (!is_string($encoded)) {
        return false;
    }
    return file_put_contents($path, $encoded . PHP_EOL, LOCK_EX) !== false;
}

function admin_absolute_path(string $relativePath): string
{
    $root = admin_root_path();
    return $root . '/' . ltrim($relativePath, '/');
}

function admin_mail_settings_defaults(): array
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

function admin_read_mail_settings(): array
{
    $settings = admin_mail_settings_defaults();
    $configPath = admin_mail_config_path();

    if (is_file($configPath)) {
        $loaded = require $configPath;
        if (is_array($loaded)) {
            foreach ($settings as $key => $defaultValue) {
                if (array_key_exists($key, $loaded)) {
                    $settings[$key] = trim((string)$loaded[$key]);
                }
            }
        }
    }

    foreach (array_keys($settings) as $key) {
        $envValue = getenv($key);
        if ($envValue !== false && $envValue !== '') {
            $settings[$key] = trim((string)$envValue);
        }
    }

    return $settings;
}

function admin_write_mail_settings(array $settings): bool
{
    $defaults = admin_mail_settings_defaults();
    $normalized = [];

    foreach ($defaults as $key => $defaultValue) {
        $normalized[$key] = trim((string)($settings[$key] ?? $defaultValue));
    }

    $export = var_export($normalized, true);
    $php = "<?php\nreturn " . $export . ";\n";

    return file_put_contents(admin_mail_config_path(), $php, LOCK_EX) !== false;
}

function admin_safe_relative_path(string $path): string
{
    $path = trim($path);
    $path = str_replace('\\', '/', $path);
    $path = ltrim($path, '/');
    $path = preg_replace('#/+#', '/', $path) ?? '';
    return $path;
}

function admin_config_path(): string
{
    return dirname(__DIR__) . '/config.admin.php';
}

function admin_write_password(string $password): bool
{
    $export = var_export(['password' => $password], true);
    $php = "<?php\nreturn " . $export . ";\n";
    return file_put_contents(admin_config_path(), $php, LOCK_EX) !== false;
}

function admin_turnstile_config_path(): string
{
    return admin_root_path() . '/config.turnstile.php';
}

function admin_turnstile_settings_defaults(): array
{
    return [
        'TURNSTILE_SITE_KEY'   => '',
        'TURNSTILE_SECRET_KEY' => '',
        'TURNSTILE_ENABLED'    => '0',
    ];
}

function admin_read_turnstile_settings(): array
{
    $settings = admin_turnstile_settings_defaults();
    $path = admin_turnstile_config_path();
    if (is_file($path)) {
        $loaded = @require $path;
        if (is_array($loaded)) {
            foreach ($settings as $key => $val) {
                if (array_key_exists($key, $loaded)) {
                    $settings[$key] = (string)$loaded[$key];
                }
            }
        }
    }
    return $settings;
}

function admin_write_turnstile_settings(array $settings): bool
{
    $defaults = admin_turnstile_settings_defaults();
    $normalized = [];
    foreach ($defaults as $key => $val) {
        $normalized[$key] = trim((string)($settings[$key] ?? $val));
    }
    $export = var_export($normalized, true);
    $php = "<?php\nreturn " . $export . ";\n";
    return file_put_contents(admin_turnstile_config_path(), $php, LOCK_EX) !== false;
}

if (!function_exists('admin_field')) {
    function admin_field(array $src, string $path, string $fallback = ''): string
    {
        $parts = explode('.', $path);
        $cur = $src;
        foreach ($parts as $p) {
            if (!is_array($cur) || !array_key_exists($p, $cur)) return $fallback;
            $cur = $cur[$p];
        }
        return is_scalar($cur) ? (string)$cur : $fallback;
    }
}

if (!function_exists('admin_lines')) {
    function admin_lines(array $src, string $path): string
    {
        $parts = explode('.', $path);
        $cur = $src;
        foreach ($parts as $p) {
            if (!is_array($cur) || !array_key_exists($p, $cur)) return '';
            $cur = $cur[$p];
        }
        if (is_array($cur)) return implode("\n", $cur);
        return is_scalar($cur) ? (string)$cur : '';
    }
}

if (!function_exists('admin_bool_field')) {
    function admin_bool_field(array $src, string $path, bool $fallback = false): bool
    {
        $parts = explode('.', $path);
        $cur = $src;
        foreach ($parts as $p) {
            if (!is_array($cur) || !array_key_exists($p, $cur)) return $fallback;
            $cur = $cur[$p];
        }
        return (bool)$cur;
    }
}
