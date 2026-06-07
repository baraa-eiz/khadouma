<?php
/**
 * not-found.php
 * Khadomeh Platform 404 Route Not Found Page
 * 
 * Displayed when an invalid URL is requested.
 */

if (!defined('IN_APP')) {
    exit;
}

$pageTitle = 'الصفحة غير موجودة - ٤٠٤';
$metaDesc = 'عذراً، الصفحة التي تحاول الوصول إليها غير موجودة في دليل خدومة للخدمات المنزلية والصيانة في سوريا.';

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

<div class="container text-center" style="padding: 60px 20px;">
    <div class="notfound-wrapper">
        <div class="notfound-emoji">🔍</div>
        <h1 class="notfound-title" style="font-family: var(--font-arabic); font-weight: 800;">عذراً، الصفحة غير موجودة!</h1>
        <p class="notfound-text">يبدو أن الرابط الذي اتبعته غير صحيح أو قد تم تعديل الصفحة مؤخراً.</p>
        <a href="<?= base_url() ?>" class="btn btn-primary btn-lg" style="margin-top: 15px;">العودة إلى الصفحة الرئيسية</a>
    </div>
</div>
