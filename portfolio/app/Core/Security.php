<?php

namespace App\Core;

/**
 * Security – OWASP-compliant helpers
 *
 * Covers:
 *  - CSRF token generation & validation
 *  - Input sanitization / validation
 *  - Rate limiting (DB-backed)
 *  - Security headers
 *  - File upload validation
 */
final class Security
{
    /* ── CSRF ────────────────────────────────────────────── */

    public static function csrfToken(): string
    {
        self::sessionStart();
        $key = config('app.security.csrf_cookie', 'csrf_token');
        $ttl = config('app.security.csrf_ttl', 3600);

        // Regenerate if expired or missing
        if (empty($_SESSION[$key]) || empty($_SESSION[$key . '_exp']) || time() > $_SESSION[$key . '_exp']) {
            $_SESSION[$key]       = bin2hex(random_bytes(config('app.security.csrf_token_length', 32)));
            $_SESSION[$key . '_exp'] = time() + $ttl;
        }
        return $_SESSION[$key];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::csrfToken()) . '">';
    }

    public static function verifyCsrf(): void
    {
        self::sessionStart();
        $key      = config('app.security.csrf_cookie', 'csrf_token');
        $received = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $stored   = $_SESSION[$key] ?? '';

        if (empty($received) || !hash_equals($stored, $received)) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }
        // Rotate token after use
        unset($_SESSION[$key], $_SESSION[$key . '_exp']);
    }

    /* ── Session ─────────────────────────────────────────── */

    public static function sessionStart(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        $cfg = config('app.security');
        session_name($cfg['session_name'] ?? 'pm_sess');

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();

        // Session fixation protection
        if (empty($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
        }

        // Session timeout
        $lifetime = $cfg['session_lifetime'] ?? 7200;
        if (!empty($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > $lifetime) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['_last_activity'] = time();
    }

    /* ── Rate limiting ───────────────────────────────────── */

    /**
     * Returns false if limit exceeded, increments counter if not.
     * @param int[] $limit [maxAttempts, windowSeconds]
     */
    public static function checkRateLimit(string $action, array $limit): bool
    {
        [$max, $window] = $limit;
        $identifier     = hash('sha256', self::clientIp() . $action);

        $db  = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT * FROM rate_limits WHERE identifier = ? AND action = ?",
            [$identifier, $action]
        );

        $now = time();

        if (!$row) {
            $db->execute(
                "INSERT INTO rate_limits (identifier, action, attempts, window_start) VALUES (?,?,1,NOW())",
                [$identifier, $action]
            );
            return true;
        }

        // Past window – reset
        if (($now - strtotime($row['window_start'])) > $window) {
            $db->execute(
                "UPDATE rate_limits SET attempts=1, window_start=NOW(), blocked_until=NULL WHERE identifier=? AND action=?",
                [$identifier, $action]
            );
            return true;
        }

        // Still blocked
        if (!empty($row['blocked_until']) && strtotime($row['blocked_until']) > $now) {
            return false;
        }

        // Exceeded – block
        if ((int)$row['attempts'] >= $max) {
            $blockedUntil = date('Y-m-d H:i:s', $now + $window);
            $db->execute(
                "UPDATE rate_limits SET blocked_until=? WHERE identifier=? AND action=?",
                [$blockedUntil, $identifier, $action]
            );
            return false;
        }

        $db->execute(
            "UPDATE rate_limits SET attempts = attempts + 1 WHERE identifier=? AND action=?",
            [$identifier, $action]
        );
        return true;
    }

    /* ── Sanitization ────────────────────────────────────── */

    public static function sanitizeString(mixed $val, int $maxLen = 255): string
    {
        $val = strip_tags(trim((string)$val));
        $val = preg_replace('/[\x00-\x1F\x7F]/u', '', $val); // remove control chars
        return mb_substr($val, 0, $maxLen, 'UTF-8');
    }

    public static function sanitizeEmail(mixed $val): string|false
    {
        $val = filter_var(trim((string)$val), FILTER_SANITIZE_EMAIL);
        return filter_var($val, FILTER_VALIDATE_EMAIL) ? strtolower($val) : false;
    }

    public static function sanitizeUrl(mixed $val): string|false
    {
        $val = filter_var(trim((string)$val), FILTER_SANITIZE_URL);
        return filter_var($val, FILTER_VALIDATE_URL) ? $val : false;
    }

    public static function sanitizeInt(mixed $val, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int|false
    {
        $int = filter_var($val, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        return ($int !== false) ? (int)$int : false;
    }

    /** Strip any HTML, allow only plain text */
    public static function plainText(mixed $val, int $maxLen = 10000): string
    {
        return mb_substr(strip_tags((string)$val), 0, $maxLen, 'UTF-8');
    }

    /* ── File upload validation ──────────────────────────── */

    /** Validate uploaded file. Returns error string or true */
    public static function validateUpload(array $file, string $type = 'image'): true|string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return 'Upload error: ' . ($file['error'] ?? 'unknown');
        }

        $cfg = config('app.security');

        if ($type === 'image') {
            $allowed  = $cfg['allowed_image_types'];
            $maxBytes = $cfg['max_image_size'];
        } else {
            $allowed  = $cfg['allowed_cv_types'];
            $maxBytes = $cfg['max_cv_size'];
        }

        if ($file['size'] > $maxBytes) {
            return 'File too large (max ' . round($maxBytes / 1048576, 1) . ' MB).';
        }

        // MIME check via finfo (not browser-provided type)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($file['tmp_name']);

        if (!in_array($mimeReal, $allowed, true)) {
            return "File type not allowed ({$mimeReal}).";
        }

        return true;
    }

    /** Generate a safe unique filename */
    public static function safeFilename(string $original, string $ext = ''): string
    {
        if ($ext === '') {
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        }
        return bin2hex(random_bytes(16)) . '.' . preg_replace('/[^a-z0-9]/', '', $ext);
    }

    /* ── Security headers ────────────────────────────────── */

    public static function setHeaders(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // CSP – tightened for portfolio (adjust if embedding YouTube etc.)
        $csp  = "default-src 'self'; ";
        $csp .= "script-src 'self' 'nonce-" . self::nonce() . "' https://www.googletagmanager.com https://pagead2.googlesyndication.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "frame-src https://www.google.com; ";
        $csp .= "connect-src 'self';";
        header("Content-Security-Policy: {$csp}");

        if (isset($_SERVER['HTTPS'])) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /** Per-request nonce for inline scripts */
    public static function nonce(): string
    {
        static $nonce;
        if ($nonce === null) {
            $nonce = base64_encode(random_bytes(18));
        }
        return $nonce;
    }

    /* ── Misc ────────────────────────────────────────────── */

    public static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => config('app.security.bcrypt_cost', 12)]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** Slow comparison to prevent timing attacks */
    public static function safeCompare(string $a, string $b): bool
    {
        return hash_equals($a, $b);
    }
}