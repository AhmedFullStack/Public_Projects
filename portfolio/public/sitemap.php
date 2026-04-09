<?php
/**
 * Sitemap XML generator
 * Accessed via /sitemap.xml route
 */

defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$db      = \App\Core\Database::getInstance();
$baseUrl = rtrim(config('app.app.base_url', ''), '/');
$locales = config('app.locale.available', ['ar', 'en']);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

$staticPages = ['', 'portfolio', 'about', 'contact'];

foreach ($staticPages as $page) {
    $url = $baseUrl . '/' . $page;
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($url) . "</loc>\n";
    echo "    <changefreq>" . ($page === '' ? 'weekly' : 'monthly') . "</changefreq>\n";
    echo "    <priority>" . ($page === '' ? '1.0' : '0.8') . "</priority>\n";
    foreach ($locales as $l) {
        $alt = $url . (str_contains($url, '?') ? '&' : '?') . "lang={$l}";
        echo '    <xhtml:link rel="alternate" hreflang="' . $l . '" href="' . htmlspecialchars($alt) . '"/>' . "\n";
    }
    echo "  </url>\n";
}

// Project pages
$projects = $db->fetchAll(
    "SELECT p.slug, p.updated_at, p.is_active FROM projects p WHERE p.is_active = 1 ORDER BY p.updated_at DESC"
);

foreach ($projects as $proj) {
    $url = $baseUrl . '/portfolio/' . rawurlencode($proj['slug']);
    $lastmod = date('Y-m-d', strtotime($proj['updated_at']));
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($url) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    foreach ($locales as $l) {
        $alt = $url . '?lang=' . $l;
        echo '    <xhtml:link rel="alternate" hreflang="' . $l . '" href="' . htmlspecialchars($alt) . '"/>' . "\n";
    }
    echo "  </url>\n";
}

echo '</urlset>';