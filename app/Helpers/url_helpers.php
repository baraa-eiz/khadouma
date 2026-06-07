<?php
/**
 * url_helpers.php
 * Khadomeh URL Resolution Helpers
 * 
 * Provides functions for absolute and relative routing references,
 * matching current environment config.
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

/**
 * Generate an absolute URL pointing to a path.
 */
if (!function_exists('base_url')) {
    function base_url($path = '') {
        return APP_URL . '/' . ltrim($path, '/');
    }
}

/**
 * Generate an absolute URL pointing to a public asset.
 */
if (!function_exists('asset_url')) {
    function asset_url($path = '') {
        return base_url('public/assets/' . ltrim($path, '/'));
    }
}

/**
 * Generate an absolute URL pointing to an admin route.
 */
if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        return base_url('admin/' . ltrim($path, '/'));
    }
}
