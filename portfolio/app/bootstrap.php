<?php
/**
 * Bootstrap – loaded once by index.php
 * Defines global helpers, loads .env, registers autoloader
 */

defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));

/* ── Timezone ──────────────────────────────────────────────── */
date_default_timezone_set('UTC');

/* ── .env loader (no external dependency) ─────────────────── */
$envFile = ROOT_PATH . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $val;
            putenv("{$key}={$val}");
        }
    }
}

/* ── Global helpers ────────────────────────────────────────── */
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        $val = $_ENV[$key] ?? getenv($key);
        if ($val === false) return $default;
        return match(strtolower((string)$val)) {
            'true','(true)'   => true,
            'false','(false)' => false,
            'null','(null)'   => null,
            default           => $val,
        };
    }
}

if (!function_exists('config')) {
    $__config_cache = [];
    function config(string $key, mixed $default = null): mixed {
        global $__config_cache;
        $parts  = explode('.', $key, 2);
        $file   = $parts[0];
        $subKey = $parts[1] ?? null;
        if (!isset($__config_cache[$file])) {
            $path = ROOT_PATH . '/config/' . $file . '.php';
            $__config_cache[$file] = is_file($path) ? require $path : [];
        }
        if ($subKey === null) return $__config_cache[$file];
        $data = $__config_cache[$file];
        foreach (explode('.', $subKey) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) return $default;
            $data = $data[$segment];
        }
        return $data;
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $base = rtrim(config('app.app.base_url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return base_url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('e')) {
    /** HTML-escape */
    function e(mixed $val): string {
        return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): never {
        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never {
        if (config('app.app.debug')) {
            foreach ($vars as $v) { echo '<pre>'; var_dump($v); echo '</pre>'; }
        }
        exit;
    }
}

/* ── PSR-4 style autoloader ────────────────────────────────── */
spl_autoload_register(function (string $class): void {
    // Namespace App\Core\Database  →  ROOT_PATH/app/Core/Database.php
    if (!str_starts_with($class, 'App\\')) return;
    $rel  = str_replace(['App\\', '\\'], ['', '/'], $class);
    $file = ROOT_PATH . '/app/' . $rel . '.php';
    if (is_file($file)) require_once $file;
});

/* ── PHP runtime settings ──────────────────────────────────── */
if (config('app.app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/storage/logs/php_errors.log');