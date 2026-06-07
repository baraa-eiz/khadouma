<?php
/**
 * about-us.php
 * Khadomeh Platform About Us Page
 */

if (!defined('IN_APP')) {
    exit;
}

// Fetch the page model from database
$db = \App\Core\Database::getInstance();
$pageData = $db->fetch("SELECT * FROM `static_pages` WHERE `slug` = 'about-us' AND `is_active` = 1 AND `deleted_at` IS NULL LIMIT 1");

if (!$pageData) {
    \App\Core\Response::setStatusCode(404);
    require APP_DIR . '/pages/not-found.php';
    exit;
}

$pageTitle = $pageData['title_ar'];
$metaDesc = $pageData['meta_description_ar'];

// Include in the shared layout wrapper
$viewPath = __FILE__;
if (isset($isLayoutCalled) && $isLayoutCalled) {
    // Content rendering phase
} else {
    $isLayoutCalled = true;
    require APP_DIR . '/includes/layout.php';
    exit;
}
?>

<div class="container" style="max-width: 800px; padding: 40px 20px;">
    <h1 style="font-family: var(--font-arabic); font-size: 2.2rem; font-weight: 800; margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 15px; color: var(--text-primary);">
        <?= e($pageData['title_ar']) ?>
    </h1>
    <div class="static-content-block" style="font-size: 1.1rem; line-height: 1.9; color: var(--text-secondary);">
        <?= $pageData['content_ar'] ?>
    </div>
</div>
