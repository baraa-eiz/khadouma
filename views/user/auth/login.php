<?php
/**
 * login.php
 * User Portal Login Page
 */
use App\Core\Config;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المستخدمين - منصة خدومة</title>
    <!-- Tajawal Font -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --dark-indigo: #1e1b4b;
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-gradient: radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.12) 0%, rgba(30, 27, 75, 0.05) 90.2%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
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
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.8);
            width: 100%;
            max-width: 460px;
            padding: 40px;
            text-align: center;
            backdrop-filter: blur(12px);
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
            background: linear-gradient(90deg, var(--primary), #8b5cf6);
        }

        .logo-section {
            margin-bottom: 25px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
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
            color: var(--dark-indigo);
        }

        .header-section {
            margin-bottom: 30px;
        }

        .title {
            font-size: 1.6rem;
            font-weight: 900;
            color: #0f172a;
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

        .success-box {
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #065f46;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: right;
        }

        .footer-note {
            margin-top: 30px;
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.5;
            border-top: 1px solid #e2e8f0;
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="logo-text">منصة خدومة</div>
        </div>

        <!-- Header -->
        <div class="header-section">
            <h1 class="title">بوابة المستخدمين</h1>
            <p class="subtitle">سجل دخولك للوصول إلى لوحة التحكم الخاصة بك، وإدارة المفضلة وتحديث ملفك الشخصي.</p>
        </div>

        <!-- Flash messages -->
        <?php
        $flashError = \App\Core\Flash::get('error');
        if ($flashError):
        ?>
            <div class="alert-box">
                <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>

        <?php
        $flashSuccess = \App\Core\Flash::get('success');
        if ($flashSuccess):
        ?>
            <div class="success-box">
                <?= htmlspecialchars($flashSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($devLoginEnabled) && $devLoginEnabled): ?>
            <!-- Developer Credentials Form -->
            <form action="<?= url('user/login') ?>" method="POST" style="text-align: right; display: flex; flex-direction: column; gap: 15px;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div>
                    <label for="username" style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">البريد الإلكتروني أو رقم الهاتف</label>
                    <input type="text" id="username" name="username" placeholder="example@domain.com أو 09xxxxxxxx" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#cbd5e1'">
                </div>

                <div>
                    <label for="password" style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة مرور التطوير الخاصة بك" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#cbd5e1'">
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: var(--primary); border: none; color: #fff; font-family: inherit; font-size: 1rem; font-weight: 700; border-radius: 10px; cursor: pointer; transition: background 0.2s; margin-top: 5px;" onmouseover="this.style.background='var(--primary-hover)'" onmouseout="this.style.background='var(--primary)'">
                    تسجيل الدخول المباشر 🔑
                </button>
            </form>
        <?php else: ?>
            <div class="alert-box" style="text-align: center;">
                تسجيل الدخول مغلق حالياً. يرجى التواصل مع إدارة النظام لتفعيل حسابك.
            </div>
        <?php endif; ?>

        <!-- Footer Notes -->
        <div class="footer-note">
            جميع الحقوق محفوظة &copy; <?= date('Y') ?> منصة خدومة.
        </div>

        <a href="/" class="back-link">← العودة للرئيسية</a>
    </div>

</body>
</html>
