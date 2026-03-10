<?php
/**
 * Simple CMS helpers for JSON-based content/script config.
 */

if (!function_exists('visitfy_load_json')) {
    function visitfy_load_json(string $absolutePath, array $fallback = []): array
    {
        if (!is_file($absolutePath)) {
            return $fallback;
        }
        $raw = file_get_contents($absolutePath);
        if (!is_string($raw) || $raw === '') {
            return $fallback;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : $fallback;
    }
}

if (!function_exists('visitfy_get')) {
    function visitfy_get(array $source, string $path, $fallback = '')
    {
        $segments = explode('.', $path);
        $cursor = $source;
        foreach ($segments as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $fallback;
            }
            $cursor = $cursor[$segment];
        }
        return $cursor;
    }
}

if (!function_exists('visitfy_split_lines')) {
    function visitfy_split_lines(string $value): array
    {
        $parts = preg_split('/\r\n|\r|\n/', $value) ?: [];
        $parts = array_map(static fn($v) => trim((string)$v), $parts);
        return array_values(array_filter($parts, static fn($v) => $v !== ''));
    }
}

if (!function_exists('visitfy_base_path')) {
    function visitfy_base_path(): string
    {
        $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName === '') {
            return '/';
        }

        if (substr($scriptName, -9) === '/index.php') {
            $base = substr($scriptName, 0, -9);
        } else {
            $base = rtrim(dirname($scriptName), '/\\');
        }

        $base = trim((string)$base, '/\\');
        return $base === '' ? '/' : '/' . $base . '/';
    }
}

if (!function_exists('visitfy_url')) {
    function visitfy_url(string $path = ''): string
    {
        if ($path === '') {
            return visitfy_base_path();
        }

        if (preg_match('#^(?:https?:)?//#i', $path) || strpos($path, 'mailto:') === 0 || strpos($path, 'tel:') === 0) {
            return $path;
        }

        if (strpos($path, '#') === 0) {
            return $path;
        }

        return visitfy_base_path() . ltrim($path, '/');
    }
}
