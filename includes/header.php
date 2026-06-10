<?php
/**
 * header.php
 * Khadomeh Global Layout Header
 */
if (!defined('IN_APP')) {
    exit;
}

$pageTitle = isset($pageTitle) ? $pageTitle : '';
$metaDesc = isset($metaDesc) ? $metaDesc : '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $seoParams = isset($seoData) ? $seoData : [];
    if (!isset($seoParams['title']) && !empty($pageTitle)) {
        $seoParams['title'] = $pageTitle;
    }
    if (!isset($seoParams['description']) && !empty($metaDesc)) {
        $seoParams['description'] = $metaDesc;
    }
    if (isset($canonicalUrl)) {
        $seoParams['canonical'] = $canonicalUrl;
    }
    echo seo_tags($seoParams);
    ?>
    <?php if (isset($prevPageUrl)): ?>
        <link rel="prev" href="<?= htmlspecialchars($prevPageUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (isset($nextPageUrl)): ?>
        <link rel="next" href="<?= htmlspecialchars($nextPageUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    
    <!-- Google Fonts: Cairo (Arabic) & Outfit (Latin/Numbers) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Core CSS Framework -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body>
    <!-- Accessibility Skip Link -->
    <a href="#main-content" class="skip-link">انتقل إلى المحتوى الرئيسي</a>

    <!-- Top Navigation Bar -->
    <header class="main-header">
        <div class="container header-container">
            <!-- Brand Logo -->
            <a href="<?= base_url() ?>" class="brand-logo" aria-label="الصفحة الرئيسية لمنصة خدومة">
                <span class="logo-icon">🛠️</span>
                <span class="logo-text">خدومة</span>
            </a>

            <!-- Main Nav Links -->
            <nav class="main-nav" aria-label="التنقل الرئيسي">
                <ul class="nav-list">
                    <li><a href="<?= base_url() ?>" class="nav-link">الرئيسية</a></li>
                    <li><a href="<?= base_url('about-us') ?>" class="nav-link">من نحن</a></li>
                    <li><a href="<?= base_url('terms') ?>" class="nav-link">الشروط والأحكام</a></li>
                </ul>
            </nav>

            <!-- User Auth Actions -->
            <div class="nav-actions">
                <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                    <span class="user-welcome">مرحباً، <?= e($_SESSION['admin_name']) ?></span>
                    <a href="<?= admin_url('dashboard.php') ?>" class="btn btn-secondary">لوحة التحكم</a>
                    <a href="<?= admin_url('logout.php') ?>" class="btn btn-outline btn-sm">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="<?= admin_url('login.php') ?>" class="btn btn-outline btn-sm">لوحة الإشراف</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="main-body">
