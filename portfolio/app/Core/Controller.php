<?php

namespace App\Core;

/**
 * Controller – base for all controllers
 *
 * Provides:
 *   - view() rendering with layout
 *   - json() response
 *   - redirect()
 *   - flash messages
 *   - request input helpers
 */
abstract class Controller
{
    protected string $layout = 'main';

    /* ── View rendering ──────────────────────────────────── */

    /**
     * Render a view inside a layout.
     *
     * @param string $view   e.g. 'pages/home'
     * @param array  $data   Variables passed to the view
     */
    protected function view(string $view, array $data = []): void
    {
        // Make data available as variables
        extract($data, EXTR_SKIP);

        // Capture view output
        ob_start();
        $viewFile = ROOT_PATH . '/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Render layout
        $layoutFile = ROOT_PATH . '/views/layouts/' . $this->layout . '.php';
        if (is_file($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /** Render a partial without layout */
    protected function partial(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require ROOT_PATH . '/views/' . $view . '.php';
        return ob_get_clean();
    }

    /* ── JSON responses ──────────────────────────────────── */

    protected function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function jsonSuccess(string $message = 'OK', array $extra = []): never
    {
        $this->json(array_merge(['success' => true, 'message' => $message], $extra));
    }

    protected function jsonError(string $message, int $code = 400): never
    {
        $this->json(['success' => false, 'message' => $message], $code);
    }

    /* ── Redirect ────────────────────────────────────────── */

    protected function redirect(string $url, int $code = 302): never
    {
        redirect($url, $code);
    }

    protected function back(): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? base_url();
        redirect($ref);
    }

    /* ── Flash messages ──────────────────────────────────── */

    protected function flash(string $type, string $message): void
    {
        Security::sessionStart();
        $_SESSION['_flash'][$type][] = $message;
    }

    public static function getFlash(): array
    {
        Security::sessionStart();
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    /* ── Request helpers ─────────────────────────────────── */

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    protected function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    protected function currentUrl(): string
    {
        return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /* ── Pagination helper ───────────────────────────────── */

    protected function currentPage(int $default = 1): int
    {
        $page = (int) ($this->get('page', $default));
        return max(1, $page);
    }

    /* ── Auth guard ──────────────────────────────────────── */

    protected function requireAuth(): void
    {
        Security::sessionStart();
        if (empty($_SESSION['admin_id'])) {
            $this->flash('error', \App\Core\Lang::t('auth.login_required'));
            $this->redirect(base_url('admin/login'));
        }
    }
}