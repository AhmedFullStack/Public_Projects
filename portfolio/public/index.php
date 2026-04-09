<?php
/**
 * ElectroMech Portfolio — Front Controller
 * Entry point for all HTTP requests
 *
 * All requests are rewritten here via .htaccess
 */

defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));

/* ── Bootstrap ───────────────────────────────────────────── */
require ROOT_PATH . '/app/bootstrap.php';

use App\Core\Security;
use App\Core\Lang;
use App\Core\Router;
use App\Helpers\SeoHelper;

/* ── Security headers ────────────────────────────────────── */
Security::setHeaders();

/* ── Start session ───────────────────────────────────────── */
Security::sessionStart();

/* ── Locale detection ────────────────────────────────────── */
$locale = Lang::detect();
Lang::set($locale);

/* ── Router ──────────────────────────────────────────────── */
$router = new Router();

// ── Public routes ────────────────────────────────────────

$router->get('/',               [\App\Controllers\HomeController::class, 'index']);
$router->get('/portfolio',      [\App\Controllers\PortfolioController::class, 'index']);
$router->get('/portfolio/{slug}', [\App\Controllers\PortfolioController::class, 'show']);
$router->get('/about',          [\App\Controllers\AboutController::class, 'index']);
$router->get('/contact',        [\App\Controllers\ContactController::class, 'index']);
$router->post('/contact/send',  [\App\Controllers\ContactController::class, 'send']);
$router->get('/privacy',        [\App\Controllers\PrivacyController::class, 'index']);
$router->get('/cv/download',    [\App\Controllers\CvDownloadController::class, 'download']);

// ── Sitemap & Robots ─────────────────────────────────────
$router->get('/sitemap.xml', function() {
    require ROOT_PATH . '/public/sitemap.php';
});
$router->get('/robots.txt', function() {
    header('Content-Type: text/plain; charset=UTF-8');
    $settings = (new \App\Models\SettingsModel())->getValue('robots_txt', "User-agent: *\nAllow: /");
    echo $settings;
    exit;
});

// ── Admin routes ─────────────────────────────────────────
$adminMw = [\App\Middleware\AuthMiddleware::class];

$router->get( '/admin',                [\App\Controllers\Admin\DashboardController::class, 'index'], $adminMw);
$router->get( '/admin/login',          [\App\Controllers\Admin\AuthController::class, 'showLogin']);
$router->post('/admin/login',          [\App\Controllers\Admin\AuthController::class, 'login']);
$router->get( '/admin/logout',         [\App\Controllers\Admin\AuthController::class, 'logout'], $adminMw);

$router->get( '/admin/projects',       [\App\Controllers\Admin\ProjectsController::class, 'index'],   $adminMw);
$router->get( '/admin/projects/create',[\App\Controllers\Admin\ProjectsController::class, 'create'],  $adminMw);
$router->post('/admin/projects/create',[\App\Controllers\Admin\ProjectsController::class, 'store'],   $adminMw);
$router->get( '/admin/projects/{id}/edit', [\App\Controllers\Admin\ProjectsController::class, 'edit'], $adminMw);
$router->post('/admin/projects/{id}/edit', [\App\Controllers\Admin\ProjectsController::class, 'update'], $adminMw);
$router->post('/admin/projects/{id}/delete', [\App\Controllers\Admin\ProjectsController::class, 'destroy'], $adminMw);

$router->get( '/admin/skills',         [\App\Controllers\Admin\SkillsController::class, 'index'],  $adminMw);
$router->post('/admin/skills/save',    [\App\Controllers\Admin\SkillsController::class, 'save'],   $adminMw);

$router->get( '/admin/messages',       [\App\Controllers\Admin\MessagesController::class, 'index'], $adminMw);
$router->get( '/admin/messages/{id}',  [\App\Controllers\Admin\MessagesController::class, 'show'],  $adminMw);
$router->post('/admin/messages/{id}/delete', [\App\Controllers\Admin\MessagesController::class, 'destroy'], $adminMw);

$router->get( '/admin/settings',       [\App\Controllers\Admin\SettingsController::class, 'index'],  $adminMw);
$router->post('/admin/settings',       [\App\Controllers\Admin\SettingsController::class, 'save'],   $adminMw);

$router->get( '/admin/cv',             [\App\Controllers\Admin\CvController::class, 'index'],   $adminMw);
$router->post('/admin/cv/upload',      [\App\Controllers\Admin\CvController::class, 'upload'],  $adminMw);
$router->post('/admin/cv/{id}/delete', [\App\Controllers\Admin\CvController::class, 'destroy'], $adminMw);

$router->get( '/admin/seo',            [\App\Controllers\Admin\SeoController::class, 'index'], $adminMw);
$router->post('/admin/seo',            [\App\Controllers\Admin\SeoController::class, 'save'],  $adminMw);

// ── Error handlers ────────────────────────────────────────
$router->setNotFound(function() {
    http_response_code(404);
    require ROOT_PATH . '/views/pages/404.php';
});

/* ── Dispatch ────────────────────────────────────────────── */
$router->dispatch();