<?php

use App\Core\Config;

if (!function_exists('seo_title')) {
    /**
     * Generate a website title tag content.
     */
    function seo_title(string $pageTitle = ''): string
    {
        $siteName = Config::get('app.name', 'خدومة');
        if (empty($pageTitle)) {
            return $siteName . ' | دليل الخدمات المنزلية والحرفيين في سوريا';
        }
        return e($pageTitle) . ' | ' . $siteName;
    }
}

if (!function_exists('seo_tags')) {
    /**
     * Compile complete HTML meta headers, canonicals, and OpenGraph parameters.
     */
    function seo_tags(array $data = []): string
    {
        $title = seo_title($data['title'] ?? '');
        
        $desc = $data['description'] ?? '';
        $defaultDesc = 'ابحث عن أفضل الحرفيين وسجل ورش الكهرباء، السباكة، والخدمات المنزلية في سوريا. تواصل مباشر بدون عمولات.';
        $cleanDesc = empty($desc) ? $defaultDesc : mb_substr(strip_tags($desc), 0, 160, 'UTF-8');
        
        $canonical = $data['canonical'] ?? (rtrim(Config::get('app.url', ''), '/') . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $ogImage = $data['image'] ?? asset('images/logo.png');

        $html = "\n    <title>" . $title . "</title>";
        $html .= "\n    <meta name=\"description\" content=\"" . e($cleanDesc) . "\">";
        $html .= "\n    <link rel=\"canonical\" href=\"" . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . "\">";
        
        // OpenGraph
        $html .= "\n    <meta property=\"og:title\" content=\"" . e($title) . "\">";
        $html .= "\n    <meta property=\"og:description\" content=\"" . e($cleanDesc) . "\">";
        $html .= "\n    <meta property=\"og:image\" content=\"" . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . "\">";
        $html .= "\n    <meta property=\"og:url\" content=\"" . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . "\">";
        $html .= "\n    <meta property=\"og:type\" content=\"" . e($data['og_type'] ?? 'website') . "\">";
        
        // Twitter
        $html .= "\n    <meta name=\"twitter:card\" content=\"summary_large_image\">";
        $html .= "\n    <meta name=\"twitter:title\" content=\"" . e($title) . "\">";
        $html .= "\n    <meta name=\"twitter:description\" content=\"" . e($cleanDesc) . "\">";
        $html .= "\n    <meta name=\"twitter:image\" content=\"" . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . "\">";
        
        return $html;
    }
}

if (!function_exists('json_ld_local_business')) {
    /**
     * Generate JSON-LD LocalBusiness markup.
     */
    function json_ld_local_business(array $provider): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $provider['full_name'] ?? '',
            'image' => !empty($provider['avatar_url']) ? url($provider['avatar_url']) : '',
            'telephone' => $provider['phone'] ?? '',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $provider['city_name'] ?? 'سوريا',
                'addressRegion' => $provider['area_name'] ?? ''
            ]
        ];

        if (!empty($provider['average_rating']) && !empty($provider['reviews_count'])) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $provider['average_rating'],
                'reviewCount' => $provider['reviews_count'],
                'bestRating' => '5',
                'worstRating' => '1'
            ];
        }

        return "\n<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n</script>";
    }
}

if (!function_exists('json_ld_breadcrumbs')) {
    /**
     * Generate JSON-LD BreadcrumbList markup.
     */
    function json_ld_breadcrumbs(array $crumbs): string
    {
        $items = [];
        $i = 1;
        foreach ($crumbs as $name => $link) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $i++,
                'name' => $name,
                'item' => url($link)
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];

        return "\n<script type=\"application/ld+json\">\n" . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n</script>";
    }
}
