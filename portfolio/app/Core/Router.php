<?php

namespace App\Core;

use RuntimeException;

/**
 * Router – lightweight front-controller router
 *
 * Supports:
 *   - GET / POST / ANY verb matching
 *   - Named parameters  /project/{slug}
 *   - Middleware groups
 *   - 404 / 405 handlers
 *   - Dynamic base_path (subfolder) support
 */
final class Router
{
    private array  $routes     = [];
    private array  $middleware = [];
    private string $basePath;

    /** @var callable|null */
    private mixed $notFoundHandler  = null;
    /** @var callable|null */
    private mixed $notAllowedHandler = null;

    public function __construct()
    {
        // Strip trailing slash, keep empty string for root
        $this->basePath = rtrim(config('app.app.base_path', ''), '/');
    }

    /* ── Route registration ──────────────────────────────── */

    public function get(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function any(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET|POST', $pattern, $handler, $middleware);
    }

    private function add(string $methods, string $pattern, callable|array $handler, array $middleware): void
    {
        $this->routes[] = [
            'methods'    => explode('|', strtoupper($methods)),
            'pattern'    => $this->compilePattern($pattern),
            'handler'    => $handler,
            'middleware' => $middleware,
            'original'   => $pattern,
        ];
    }

    public function setNotFound(callable $fn): void  { $this->notFoundHandler   = $fn; }
    public function setNotAllowed(callable $fn): void { $this->notAllowedHandler = $fn; }

    /* ── Dispatch ────────────────────────────────────────── */

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri    = $this->currentUri();

        $matchedMethods = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $uri, $m)) continue;

            $matchedMethods = array_merge($matchedMethods, $route['methods']);

            if (!in_array($method, $route['methods'], true)) continue;

            // Extract named params
            $params = array_filter($m, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);

            // Run middleware chain
            foreach ($route['middleware'] as $mwClass) {
                $mw = new $mwClass();
                if (method_exists($mw, 'handle')) {
                    $mw->handle();  // may redirect/exit
                }
            }

            // Resolve and call handler
            $this->callHandler($route['handler'], $params);
            return;
        }

        if (!empty($matchedMethods)) {
            $this->handleNotAllowed($matchedMethods);
            return;
        }

        $this->handleNotFound();
    }

    /* ── URL generation ──────────────────────────────────── */

    public function url(string $pattern, array $params = []): string
    {
        $path = $pattern;
        foreach ($params as $key => $val) {
            $path = preg_replace('/\{' . preg_quote($key, '/') . '(\?)?\}/', (string)$val, $path);
        }
        // Remove optional segments that weren't filled
        $path = preg_replace('/\/\{[^}]+\?\}/', '', $path);
        return rtrim(config('app.app.base_url', ''), '/') . '/' . ltrim($path, '/');
    }

    /* ── Internals ───────────────────────────────────────── */

    private function currentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = rawurldecode($uri ?? '/');

        // Strip base path prefix
        if ($this->basePath !== '' && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        $uri = '/' . ltrim($uri, '/');

        // Normalise trailing slash (keep / for root only)
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    private function compilePattern(string $pattern): string
    {
        // Convert {param} and {param?} to named capture groups
        $regex = preg_replace_callback('/\{(\w+)(\?)?\}/', function ($m) {
            $name     = $m[1];
            $optional = isset($m[2]);
            $segment  = "(?P<{$name}>[^/]+)";
            return $optional ? "(?:/{$segment})?" : $segment;
        }, $pattern);

        return '#^' . $regex . '$#u';
    }

    private function callHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $obj = new $class();
            $obj->{$method}($params);
        } else {
            $handler($params);
        }
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        if ($this->notFoundHandler) {
            ($this->notFoundHandler)();
        } else {
            echo '<h1>404 Not Found</h1>';
        }
    }

    private function handleNotAllowed(array $allowed): void
    {
        http_response_code(405);
        header('Allow: ' . implode(', ', array_unique($allowed)));
        if ($this->notAllowedHandler) {
            ($this->notAllowedHandler)($allowed);
        } else {
            echo '<h1>405 Method Not Allowed</h1>';
        }
    }
}