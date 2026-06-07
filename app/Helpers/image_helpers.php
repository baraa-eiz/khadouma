<?php
/**
 * image_helpers.php
 * Khadomeh Image Resolution & Placeholder Helpers
 * 
 * Provides base64 SVG generators to render theme-compliant visual placeholders
 * dynamically in the browser, bypassing the need for heavy or external images.
 */

/**
 * Generate a dynamic SVG placeholder inline data URI.
 * Complies with the Light Warm Trust design palette.
 */
if (!function_exists('get_placeholder_svg')) {
    function get_placeholder_svg($width = 300, $height = 200, $text = 'خدومة') {
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

/**
 * Check if the target provider image exists, otherwise return a styled SVG placeholder.
 */
if (!function_exists('get_provider_image')) {
    function get_provider_image($path, $width = 150, $height = 150, $text = 'صورة الحرفي') {
        if (!empty($path) && file_exists(APP_DIR . '/' . $path)) {
            return base_url($path);
        }
        return get_placeholder_svg($width, $height, $text);
    }
}
