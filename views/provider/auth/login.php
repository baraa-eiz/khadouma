<?php
/**
 * login.php
 * Provider Portal Login Page
 */
use App\Core\Config;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دخول مقدمي الخدمات - منصة خدومة</title>
    <!-- Outfit & Tajawal Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --dark-teal: #111827;
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-gradient: radial-gradient(circle at 10% 20%, rgba(4, 159, 108, 0.15) 0%, rgba(17, 24, 39, 0.05) 90.2%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            background-image: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.7);
            width: 100%;
            max-width: 460px;
            padding: 40px;
            text-align: center;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #10b981, #3b82f6);
        }

        .logo-section {
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1));
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            color: var(--primary);
        }

        .logo-icon svg {
            width: 36px;
            height: 36px;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--dark-teal);
        }

        .header-section {
            margin-bottom: 35px;
        }

        .title {
            font-size: 1.6rem;
            font-weight: 900;
            color: #111827;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .alert-box {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #b91c1c;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: right;
        }

        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background-color: #ffffff;
            border: 1px solid #dadce0;
            color: #3c4043;
            font-size: 1rem;
            font-weight: 700;
            padding: 12px 24px;
            border-radius: 12px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            text-decoration: none;
        }

        .google-btn:hover {
            background-color: #f7f9fa;
            border-color: #d2d4d7;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            transform: translateY(-1px);
        }

        .google-btn svg {
            width: 20px;
            height: 20px;
        }

        .footer-note {
            margin-top: 30px;
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.5;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: color 0.15s;
        }

        .back-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Logo -->
        <div class="logo-section">
            <div class="logo-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="logo-text">منصة خدومة</div>
        </div>

        <!-- Header -->
        <div class="header-section">
            <h1 class="title">بوابة مزودي الخدمات</h1>
            <p class="subtitle">سجل دخولك لإدارة حسابك المهني، استقبال الطلبات، وتعديل ملفك الشخصي المعروض للعملاء.</p>
        </div>

        <!-- Flash messages -->
        <?php
        // Manually show errors if present
        $flashError = \App\Core\Flash::get('error');
        if ($flashError):
        ?>
            <div class="alert-box">
                <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>

        <!-- Google Login Button -->
        <a href="<?= url('provider/auth/google') ?>" class="google-btn">
            <svg viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                <g transform="matrix(1, 0, 0, 1, 0, 0)">
                    <path d="M21.35,11.1H12v2.7h5.38c-0.24,1.28 -0.96,2.37 -2.04,3.1v2.58h3.3c1.93,-1.78 3.04,-4.4 3.04,-7.42c0,-0.65 -0.06,-1.27 -0.17,-1.96Z" fill="#4285F4"/>
                    <path d="M12,20.62c2.43,0 4.47,-0.8 5.96,-2.18l-3.3,-2.58c-0.9,0.6 -2.07,0.98 -3.3,0.98c-2.35,0 -4.33,-1.58 -5.04,-3.72H2.9v2.66c1.49,2.96 4.54,4.84 8.04,4.84Z" fill="#34A853"/>
                    <path d="M6.96,13.12c-0.18,-0.54 -0.28,-1.11 -0.28,-1.7c0,-0.59 0.1,-1.16 0.28,-1.7V7.06H2.9C2.29,8.27 1.94,9.64 1.94,11.42c0,1.78 0.35,3.15 0.96,4.36l4.06,-2.66Z" fill="#FBBC05"/>
                    <path d="M12,5.38c1.32,0 2.5,0.45 3.44,1.35l2.58,-2.58C16.46,2.71 14.43,1.9 12,1.9C8.5,1.9 5.44,3.78 3.96,6.74l4.06,2.66c0.71,-2.14 2.69,-3.72 5.04,-3.72Z" fill="#EA4335"/>
                </g>
            </svg>
            <span>الدخول السريع باستخدام حساب Google</span>
        </a>

        <?php if (isset($devLoginEnabled) && $devLoginEnabled): ?>
            <!-- Developer Credentials Form -->
            <form action="<?= url('provider/auth/dev') ?>" method="POST" style="margin-top: 25px; text-align: right; display: flex; flex-direction: column; gap: 15px;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div style="text-align: center; margin-bottom: 5px; color: var(--text-muted); font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <span style="flex-grow: 1; height: 1px; background: #e5e7eb;"></span>
                    <span>الدخول المباشر للمطورين (Dev Login)</span>
                    <span style="flex-grow: 1; height: 1px; background: #e5e7eb;"></span>
                </div>

                <div>
                    <label for="username" style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">معرّف مقدم الخدمة (ID / Slug / البريد)</label>
                    <input type="text" id="username" name="username" placeholder="مثال: 37 أو plumber-ali" required style="width: 100%; padding: 12px; border: 1px solid #dadce0; border-radius: 10px; font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#dadce0'">
                </div>

                <div>
                    <label for="password" style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">كلمة مرور التطوير</label>
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة مرور التطوير الخاصة بالمنصة" required style="width: 100%; padding: 12px; border: 1px solid #dadce0; border-radius: 10px; font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#dadce0'">
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: var(--primary); border: none; color: #fff; font-family: inherit; font-size: 1rem; font-weight: 700; border-radius: 10px; cursor: pointer; transition: background 0.2s; margin-top: 5px;" onmouseover="this.style.background='var(--primary-hover)'" onmouseout="this.style.background='var(--primary)'">
                    تسجيل الدخول المباشر 🔑
                </button>
            </form>
        <?php endif; ?>

        <!-- Footer Notes -->
        <div class="footer-note">
            ملاحظة: البوابة تتكامل حصرياً مع نظام التحقق الموحد من Google لضمان أمن حسابات المهنيين ومصداقية التقييمات.
        </div>

        <a href="/" class="back-link">← العودة للرئيسية</a>
    </div>

</body>
</html>
