<?php

namespace App\Helpers;

use App\Core\Lang;

/**
 * SeoHelper – generates all SEO output for a page
 *
 * Usage in controller:
 *   SeoHelper::set([
 *     'title'       => 'Project Name',
 *     'description' => '...',
 *     'canonical'   => base_url('portfolio/slug'),
 *     'schema'      => SeoHelper::schemaProject($project),
 *   ]);
 *
 * Usage in layout:
 *   <?= SeoHelper::renderHead() ?>
 */
final class SeoHelper
{
    private static array $meta = [];

    public static function set(array $meta): void
    {
        self::$meta = array_merge(self::defaults(), $meta);
    }

    public static function get(string $key, mixed $default = ''): mixed
    {
        return self::$meta[$key] ?? $default;
    }

    private static function defaults(): array
    {
        $siteName = config('app.app.name', 'Portfolio');
        $baseUrl  = rtrim(config('app.app.base_url', ''), '/');

        return [
            'title'          => $siteName,
            'title_raw'      => false,          // true = don't append site name
            'description'    => '',
            'keywords'       => '',
            'canonical'      => $baseUrl . $_SERVER['REQUEST_URI'],
            'robots'         => config('app.seo.robots', 'index, follow'),
            'og_type'        => config('app.seo.og_type', 'website'),
            'og_image'       => $baseUrl . '/public/assets/images/og-default.jpg',
            'twitter_card'   => config('app.seo.twitter_card', 'summary_large_image'),
            'locale'         => Lang::get(),
            'alternate_locale' => Lang::other(),
            'schema'         => [],
            'hreflang'       => true,           // output hreflang tags
        ];
    }

    /* ── HTML output ─────────────────────────────────────── */

    public static function renderHead(): string
    {
        if (empty(self::$meta)) self::set([]);

        $m        = self::$meta;
        $siteName = config('app.app.name', 'Portfolio');
        $title    = $m['title_raw']
            ? e($m['title'])
            : e($m['title'] . config('app.seo.title_suffix', ' | ' . $siteName));

        $baseUrl  = rtrim(config('app.app.base_url', ''), '/');
        $locale   = $m['locale'];
        $other    = $m['alternate_locale'];

        $html  = "\n<!-- SEO Meta -->\n";
        $html .= "<title>{$title}</title>\n";
        $html .= self::meta('description', $m['description']);
        if ($m['keywords']) $html .= self::meta('keywords', $m['keywords']);
        $html .= self::meta('robots', $m['robots']);
        $html .= '<link rel="canonical" href="' . e($m['canonical']) . '">' . "\n";

        // Hreflang
        if ($m['hreflang']) {
            $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
            // Strip existing ?lang= param
            $cleanUri   = preg_replace('/([&?])lang=[^&]*&?/', '$1', $currentUri);
            $cleanUri   = rtrim($cleanUri, '?&');
            $sep        = str_contains($cleanUri, '?') ? '&' : '?';
            $html .= '<link rel="alternate" hreflang="' . e($locale) . '" href="' . e($baseUrl . $cleanUri) . '">' . "\n";
            $html .= '<link rel="alternate" hreflang="' . e($other) . '" href="' . e($baseUrl . $cleanUri . $sep . 'lang=' . $other) . '">' . "\n";
            $html .= '<link rel="alternate" hreflang="x-default" href="' . e($baseUrl . $cleanUri) . '">' . "\n";
        }

        // Open Graph
        $html .= "\n<!-- Open Graph -->\n";
        $html .= self::og('title',       $title);
        $html .= self::og('description', $m['description']);
        $html .= self::og('type',        $m['og_type']);
        $html .= self::og('url',         $m['canonical']);
        $html .= self::og('image',       $m['og_image']);
        $html .= self::og('site_name',   $siteName);
        $html .= self::og('locale',      $locale === 'ar' ? 'ar_SA' : 'en_US');

        // Twitter Card
        $html .= "\n<!-- Twitter Card -->\n";
        $html .= self::tw('card',        $m['twitter_card']);
        $html .= self::tw('title',       $title);
        $html .= self::tw('description', $m['description']);
        $html .= self::tw('image',       $m['og_image']);

        // JSON-LD Structured Data
        if (!empty($m['schema'])) {
            $html .= "\n<!-- Structured Data -->\n";
            foreach ((array) $m['schema'] as $schema) {
                $html .= '<script type="application/ld+json">' . "\n";
                $html .= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                $html .= "\n</script>\n";
            }
        }

        return $html;
    }

    /* ── Structured data builders ────────────────────────── */

    public static function schemaPerson(array $settings): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Person',
            'name'        => $settings['owner_name'] ?? '',
            'jobTitle'    => $settings['owner_title'] ?? '',
            'email'       => $settings['owner_email'] ?? '',
            'telephone'   => $settings['owner_phone'] ?? '',
            'url'         => rtrim(config('app.app.base_url', ''), '/') . '/',
            'sameAs'      => array_filter([
                $settings['linkedin_url']  ?? '',
                $settings['github_url']    ?? '',
            ]),
        ];
    }

    public static function schemaWebSite(array $settings): array
    {
        $baseUrl = rtrim(config('app.app.base_url', ''), '/');
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => $settings['owner_name'] ?? config('app.app.name'),
            'url'             => $baseUrl . '/',
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => $baseUrl . '/portfolio?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public static function schemaProject(array $project, array $trans): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'CreativeWork',
            'name'        => $trans['title'] ?? '',
            'description' => $trans['summary'] ?? '',
            'dateCreated' => $project['year'] ? $project['year'] . '-01-01' : null,
            'url'         => base_url('portfolio/' . ($project['slug'] ?? '')),
            'image'       => $project['featured_image']
                ? base_url('public/uploads/projects/' . $project['featured_image'])
                : null,
        ];
    }

    /* ── Helpers ─────────────────────────────────────────── */

    private static function meta(string $name, string $content): string
    {
        if ($content === '' || $content === null) return '';
        return '<meta name="' . e($name) . '" content="' . e($content) . '">' . "\n";
    }

    private static function og(string $property, string $content): string
    {
        if ($content === '' || $content === null) return '';
        return '<meta property="og:' . e($property) . '" content="' . e($content) . '">' . "\n";
    }

    private static function tw(string $name, string $content): string
    {
        if ($content === '' || $content === null) return '';
        return '<meta name="twitter:' . e($name) . '" content="' . e($content) . '">' . "\n";
    }

    /** Truncate description to recommended length */
    public static function trimDesc(string $text, int $max = 160): string
    {
        $text = strip_tags($text);
        if (mb_strlen($text) <= $max) return $text;
        return rtrim(mb_substr($text, 0, $max - 3)) . '...';
    }
}