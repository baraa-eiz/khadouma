<?php
/**
 * login.php
 * Standalone Admin Login Page.
 */
use App\Core\Config;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة تحكم خدومة</title>
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="login-layout">

    <div class="login-card">
        <!-- Logo -->
        <div class="login-logo">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>منصة خدومة</span>
        </div>

        <!-- Title -->
        <div class="login-title-section">
            <h1 class="login-title">تسجيل دخول الإدارة</h1>
            <p class="login-desc">أدخل بيانات الاعتماد الخاصة بك للوصول إلى لوحة التحكم.</p>
        </div>

        <!-- Flash messages -->
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <?php include Config::get('app.paths.root') . '/views/components/flash.php'; ?>
        </div>

        <!-- Form -->
        <form action="<?= url('admin/login') ?>" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="form-group">
                <label for="email" class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="admin@khadomeh.local" required autofocus>
            </div>

            <div class="form-group" style="margin-bottom: 8px;">
                <label for="password" class="form-label">كلمة المرور</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 15px;">
                تسجيل الدخول
            </button>
        </form>
    </div>

    <script src="<?= url('assets/js/admin.js') ?>"></script>
</body>
</html>
