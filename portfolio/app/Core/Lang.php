<?php

namespace App\Core;

/**
 * Lang – bilingual translation manager (AR / EN)
 *
 * Usage:
 *   Lang::set('ar');
 *   echo Lang::t('nav.home');          // → الرئيسية
 *   echo Lang::t('greeting', ['name' => 'Ahmad']);
 */
final class Lang
{
    private static string $locale  = 'ar';
    private static array  $strings = [];
    private static bool   $loaded  = false;

    public static function set(string $locale): void
    {
        $available = config('app.locale.available', ['ar', 'en']);
        self::$locale  = in_array($locale, $available, true) ? $locale : config('app.locale.default', 'ar');
        self::$loaded  = false;
        self::$strings = [];
    }

    public static function get(): string { return self::$locale; }

    public static function isRtl(): bool
    {
        return in_array(self::$locale, config('app.locale.rtl', ['ar']), true);
    }

    /**
     * Translate a key, with optional interpolation.
     * Key uses dot notation: 'nav.home', 'validation.required'
     */
    public static function t(string $key, array $replace = []): string
    {
        self::load();

        $parts = explode('.', $key, 2);
        $group = $parts[0];
        $sub   = $parts[1] ?? null;

        $val = self::$strings[$group] ?? null;
        if ($sub !== null && is_array($val)) {
            foreach (explode('.', $sub) as $segment) {
                if (!is_array($val) || !isset($val[$segment])) {
                    return $key; // key not found – return raw key
                }
                $val = $val[$segment];
            }
        }

        if (!is_string($val)) return $key;

        // Interpolate :placeholder or {placeholder}
        foreach ($replace as $k => $v) {
            $val = str_replace([":{$k}", "{{$k}}"], (string)$v, $val);
        }

        return $val;
    }

    /** Shorthand for current locale switch */
    public static function choose(string $ar, string $en): string
    {
        return self::$locale === 'ar' ? $ar : $en;
    }

    /** Detect locale from cookie → query string → Accept-Language → default */
    public static function detect(): string
    {
        // 1. URL query param: ?lang=en
        if (isset($_GET['lang'])) {
            $l = strtolower(trim($_GET['lang']));
            if (self::isValid($l)) {
                self::setCookie($l);
                return $l;
            }
        }

        // 2. Cookie
        $cookieName = config('app.locale.cookie', 'app_locale');
        if (!empty($_COOKIE[$cookieName]) && self::isValid($_COOKIE[$cookieName])) {
            return $_COOKIE[$cookieName];
        }

        // 3. Accept-Language header (first tag only)
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $tag = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
            if (self::isValid($tag)) return $tag;
        }

        // 4. Default
        return config('app.locale.default', 'ar');
    }

    public static function setCookie(string $locale): void
    {
        $ttl  = config('app.locale.cookie_ttl', 86400 * 30);
        $name = config('app.locale.cookie', 'app_locale');
        setcookie($name, $locale, [
            'expires'  => time() + $ttl,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function isValid(string $l): bool
    {
        return in_array($l, config('app.locale.available', ['ar', 'en']), true);
    }

    /** Opposite locale (for lang-switcher link) */
    public static function other(): string
    {
        return self::$locale === 'ar' ? 'en' : 'ar';
    }

    private static function load(): void
    {
        if (self::$loaded) return;

        $path = ROOT_PATH . '/app/Language/' . self::$locale . '.php';
        if (is_file($path)) {
            self::$strings = require $path;
        }
        self::$loaded = true;
    }
}