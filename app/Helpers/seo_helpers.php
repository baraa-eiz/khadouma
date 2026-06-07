<?php
/**
 * seo_helpers.php
 * Khadomeh Search Engine Optimization Helpers
 * 
 * Provides features to dynamically manage meta title and description tags
 * across public pages.
 */

/**
 * Generate a dynamic website title tag.
 */
if (!function_exists('seo_title')) {
    function seo_title($pageTitle = '') {
        $siteName = APP_NAME;
        if (empty($pageTitle)) {
            return $siteName . ' | دليل الحرفيين والخدمات المنزلية في سوريا';
        }
        return e($pageTitle) . ' | ' . $siteName;
    }
}

/**
 * Generate a safe metadata description block.
 */
if (!function_exists('seo_meta_description')) {
    function seo_meta_description($description = '') {
        $defaultDesc = 'ابحث عن أفضل الكهربائيين، السباكين، عمال التنظيف، الدهانين، ونقّالي الأثاث الموثوقين في دمشق وسوريا. تواصل مباشر بدون عمولات.';
        $cleanDesc = empty($description) ? $defaultDesc : $description;
        // Limit length and escape quotes
        $cleanDesc = mb_substr(strip_tags($cleanDesc), 0, 160, 'UTF-8');
        return '<meta name="description" content="' . e($cleanDesc) . '">';
    }
}
