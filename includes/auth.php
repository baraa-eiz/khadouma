<?php
/**
 * auth.php
 * Khadomeh Admin Panel Authentication Guard
 * 
 * Verifies if an admin session is active.
 * Redirects unauthorized requests to the admin login page.
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . '/init.php';

// Check if session contains validated admin state
if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) {
    // Save current URL to session for redirecting after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login page
    \App\Core\Response::redirect(admin_url('login.php'));
}
