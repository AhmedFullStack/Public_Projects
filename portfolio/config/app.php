<?php
/**
 * Application Configuration
 * All environment-sensitive values should be overridden via .env or environment variables
 */

return [

    /* ─── Application ─────────────────────────────────────── */
    'app' => [
        'name'        => env('APP_NAME', 'ElectroMech Portfolio'),
        'version'     => '1.0.0',
        'env'         => env('APP_ENV', 'production'),
        'debug'       => (bool) env('APP_DEBUG', false),
        'base_url'    => env('APP_URL', ''),           // e.g. https://example.com/portfolio
        'base_path'   => env('APP_BASE_PATH', ''),     // e.g. /portfolio  (subfolder support)
        'timezone'    => env('APP_TIMEZONE', 'Asia/Riyadh'),
        'charset'     => 'UTF-8',
    ],

    /* ─── Database ────────────────────────────────────────── */
    'db' => [
        'host'     => env('DB_HOST', '127.0.0.1'),
        'port'     => (int) env('DB_PORT', 3306),
        'name'     => env('DB_NAME', 'portfolio_db'),
        'user'     => env('DB_USER', 'root'),
        'password' => env('DB_PASS', ''),
        'charset'  => 'utf8mb4',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ],
    ],

    /* ─── Localisation ────────────────────────────────────── */
    'locale' => [
        'default'   => env('DEFAULT_LOCALE', 'ar'),
        'available' => ['ar', 'en'],
        'rtl'       => ['ar'],
        'cookie'    => 'app_locale',
        'cookie_ttl'=> 30 * 24 * 3600, // 30 days
    ],

    /* ─── Security ────────────────────────────────────────── */
    'security' => [
        'csrf_token_length' => 32,
        'csrf_cookie'       => 'csrf_token',
        'csrf_ttl'          => 3600,                // 1 hour
        'session_name'      => 'pm_sess',
        'session_lifetime'  => 7200,                // 2 hours idle
        'remember_me_days'  => 30,
        'bcrypt_cost'       => 12,
        'login_max_attempts'=> 5,
        'login_lockout_min' => 15,
        'rate_limit_contact'=> [3, 3600],           // 3 submissions per hour
        'allowed_image_types'  => ['image/jpeg','image/png','image/webp'],
        'max_image_size'       => 5 * 1024 * 1024,  // 5 MB
        'allowed_cv_types'     => ['application/pdf'],
        'max_cv_size'          => 10 * 1024 * 1024, // 10 MB
    ],

    /* ─── SEO defaults ────────────────────────────────────── */
    'seo' => [
        'title_suffix'    => ' | ElectroMech Portfolio',
        'og_type'         => 'website',
        'twitter_card'    => 'summary_large_image',
        'robots'          => 'index, follow',
        'canonical_base'  => env('APP_URL', ''),
    ],

    /* ─── Upload paths (relative to project root) ─────────── */
    'upload' => [
        'projects_dir' => 'public/uploads/projects/',
        'cv_dir'       => 'public/uploads/cv/',
        'thumb_width'  => 800,
        'thumb_height' => 600,
    ],

    /* ─── Cache ───────────────────────────────────────────── */
    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),    // file | none
        'path'   => __DIR__ . '/../storage/cache/',
        'ttl'    => 3600,
    ],

    /* ─── Pagination ──────────────────────────────────────── */
    'pagination' => [
        'projects_per_page' => 9,
        'admin_per_page'    => 15,
    ],

];