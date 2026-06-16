<?php
/**
 * admin.php
 * Reusable layout shell for the Khadomeh Admin Panel.
 * Expects variables:
 *  - string $content (rendered child view content)
 *  - string $title (optional page title)
 *  - array $breadcrumbs (optional array of crumbs)
 */
use App\Core\Config;
use App\Core\Session;

$navItems = require Config::get('app.paths.root') . '/config/navigation.php';

// Detect current route path for active state highlighting
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = '/' . trim(parse_url($requestUri, PHP_URL_PATH), '/');

// Subdirectory path correction
$appUrlPath = parse_url(Config::get('app.url', ''), PHP_URL_PATH);
if ($appUrlPath && $appUrlPath !== '/' && strpos($requestPath, $appUrlPath) === 0) {
    $requestPath = substr($requestPath, strlen($appUrlPath));
}
$requestPath = '/' . trim($requestPath, '/');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'لوحة التحكم') ?> - منصة خدومة</title>
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body>
    <div class="admin-layout">
        <!-- HEADER -->
        <header class="admin-header">
            <div style="display: flex; align-items: center; gap: 16px;">
                <button type="button" id="sidebar-toggle" class="btn btn-secondary" style="padding: 6px 10px; display: none;" aria-label="تعديل القائمة">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="header-brand">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>لوحة تحكم خدومة</span>
                </div>
            </div>
            
            <div class="header-profile">
                <div class="profile-info" style="text-align: right;">
                    <div class="profile-name"><?= e(Session::get('admin_user_name', 'مدير النظام')) ?></div>
                    <div class="profile-role">
                        <?php
                            $role = Session::get('admin_user_role', 'admin');
                            if ($role === 'superadmin') echo 'مدير عام';
                            elseif ($role === 'moderator') echo 'مشرف';
                            else echo 'مدير';
                        ?>
                    </div>
                </div>
                <form action="<?= url('admin/logout') ?>" method="POST" style="margin: 0; display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 13px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </header>

        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <nav class="sidebar-nav">
                <?php foreach ($navItems as $item): 
                    // Verify if active pattern matches
                    $activeClass = '';
                    $pattern = str_replace('*', '.*', $item['active_pattern']);
                    if (preg_match('#^' . $pattern . '$#', $requestPath)) {
                        $activeClass = 'active';
                    }
                    
                    $isPlaceholder = ($item['url'] === '#');
                ?>
                    <a href="<?= $isPlaceholder ? '#' : url($item['url']) ?>" 
                       class="nav-item <?= $activeClass ?>" 
                       style="<?= $isPlaceholder ? 'cursor: not-allowed; opacity: 0.7;' : '' ?>"
                       <?= $isPlaceholder ? 'onclick="event.preventDefault(); alert(\'هذا القسم سيتم تفعيله في المراحل القادمة.\');"' : '' ?>>
                        
                        <?php if ($item['icon'] === 'home'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <?php elseif ($item['icon'] === 'briefcase'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <?php elseif ($item['icon'] === 'globe'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5A2.5 2.5 0 0119 14.5v.5a2.5 2.5 0 01-2.5 2.5H14M9 21h3m-3 0a9 9 0 119-9m-9 9a9 9 0 01-9-9"/></svg>
                        <?php elseif ($item['icon'] === 'map-pin'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <?php elseif ($item['icon'] === 'users'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <?php elseif ($item['icon'] === 'zap'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <?php elseif ($item['icon'] === 'star'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.243.577 1.824l-3.974 2.89a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.89a1 1 0 00-1.176 0l-3.976 2.89c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118l-3.976-2.89c-.783-.58-.38-1.824.576-1.824h4.908a1 1 0 00.95-.69l1.518-4.674z"/></svg>
                        <?php elseif ($item['icon'] === 'alert-triangle'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <?php elseif ($item['icon'] === 'settings'): ?>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <?php endif; ?>
                        
                        <span><?= e($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            
            <div style="font-size: 11px; color: var(--text-muted); text-align: center; border-top: 1px solid var(--border-color); padding-top: var(--spacing-md);">
                نظام تشغيل وإدارة الموارد v1.0.0
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <!-- FLASH MESSAGES -->
            <div class="flash-messages-container" style="display: flex; flex-direction: column; gap: 8px;">
                <?php include Config::get('app.paths.root') . '/views/components/flash.php'; ?>
            </div>

            <!-- BREADCRUMBS -->
            <?php if (isset($breadcrumbs)): ?>
                <?php include Config::get('app.paths.root') . '/views/components/breadcrumb.php'; ?>
            <?php endif; ?>

            <!-- PRIMARY YIELD -->
            <div class="page-container">
                <?= $content ?>
            </div>

            <!-- FOOTER -->
            <footer class="admin-footer">
                <div>جميع الحقوق محفوظة &copy; <?= date('Y') ?> منصة خدومة</div>
                <div>البيئة: <strong><?= e(Config::get('app.env')) ?></strong> | الإصدار: 1.0.0</div>
            </footer>
        </main>
    </div>

    <!-- LIGHTWEIGHT CONFIRM DIALOG -->
    <?php include Config::get('app.paths.root') . '/views/components/confirm_dialog.php'; ?>

    <script src="<?= url('assets/js/admin.js') ?>"></script>
    
    <script>
        // Media query sidebar helper for tablet/mobile
        const toggleBtn = document.getElementById('sidebar-toggle');
        if (toggleBtn) {
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
            }
            window.addEventListener('resize', () => {
                if (window.innerWidth <= 768) {
                    toggleBtn.style.display = 'block';
                } else {
                    toggleBtn.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
