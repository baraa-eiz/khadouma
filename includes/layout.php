<?php
/**
 * layout.php
 * Khadomeh Global Layout Coordinator
 * 
 * Includes the global header, renders the specific page content view,
 * and appends the global footer.
 */

if (!defined('IN_APP')) {
    exit;
}

// Ensure the view path is valid and provided
if (isset($viewPath) && file_exists($viewPath)) {
    require_once APP_DIR . '/includes/header.php';
    require $viewPath;
    require_once APP_DIR . '/includes/footer.php';
} else {
    // Fallback error if view is missing
    require_once APP_DIR . '/includes/header.php';
    echo '<div class="container error-container"><p class="alert alert-danger text-center">خطأ: الصفحة المطلوبة غير متوفرة.</p></div>';
    require_once APP_DIR . '/includes/footer.php';
}
