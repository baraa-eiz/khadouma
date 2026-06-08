<?php

if (!function_exists('get_placeholder_svg')) {
    /**
     * Generate an inline inline base64 SVG theme placeholder.
     */
    function get_placeholder_svg(int $width = 300, int $height = 200, string $text = 'خدومة'): string
    {
        $bgColor = '#fdfbf7';     // Light cream warm color
        $textColor = '#8a7768';   // Soft warm taupe
        $borderColor = '#f3ebd9'; // Subtle border highlight
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        $svg .= '<rect width="100%" height="100%" fill="' . $bgColor . '" rx="12"/>';
        $svg .= '<rect x="2" y="2" width="' . ($width - 4) . '" height="' . ($height - 4) . '" fill="none" stroke="' . $borderColor . '" stroke-width="2" rx="10"/>';
        $svg .= '<text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif" font-size="16" font-weight="bold" fill="' . $textColor . '">' . $text . '</text>';
        $svg .= '</svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}

if (!function_exists('get_provider_image')) {
    /**
     * Check if provider photo exists, returning standard inline SVG placeholder if missing.
     */
    function get_provider_image(?string $path, int $width = 150, int $height = 150, string $text = 'صورة الحرفي'): string
    {
        $rootDir = dirname(dirname(__DIR__));
        if (!empty($path) && file_exists($rootDir . '/public/' . ltrim($path, '/'))) {
            return url($path);
        }
        return get_placeholder_svg($width, $height, $text);
    }
}
