<?php
/**
 * google_stub.php
 * Simulated Google Auth Screen
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محاكاة بوابة التحقق الآمنة - Google Accounts</title>
    <!-- Tajawal & Outfit Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #ffffff;
            color: #202124;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            border: 1px solid #dadce0;
            border-radius: 8px;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
        }

        .google-logo {
            display: flex;
            justify-content: center;
            gap: 2px;
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .google-logo span:nth-child(1) { color: #4285F4; }
        .google-logo span:nth-child(2) { color: #EA4335; }
        .google-logo span:nth-child(3) { color: #FBBC05; }
        .google-logo span:nth-child(4) { color: #4285F4; }
        .google-logo span:nth-child(5) { color: #34A853; }
        .google-logo span:nth-child(6) { color: #EA4335; }

        .auth-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #202124;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            font-size: 0.9rem;
            color: #5f6368;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .alert-box {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #b91c1c;
            border-radius: 6px;
            padding: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            text-align: right;
        }

        .form-group {
            text-align: right;
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #3c4043;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 0.95rem;
            border: 1px solid #dadce0;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-control:focus {
            border-color: #1a73e8;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }

        .action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        .btn-link {
            color: #1a73e8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .btn-link:hover {
            color: #1557b0;
            text-decoration: underline;
        }

        .btn-submit {
            background-color: #1a73e8;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            font-size: 0.9rem;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .btn-submit:hover {
            background-color: #1557b0;
        }

        .sandbox-badge {
            display: inline-block;
            background-color: #e8f0fe;
            color: #1a73e8;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <!-- Sandbox Indicator -->
        <span class="sandbox-badge">بيئة اختبار محاكاة</span>

        <!-- Google Logo -->
        <div class="google-logo">
            <span>G</span><span>o</span><span>o</span><span>g</span><span>l</span><span>e</span>
        </div>

        <h1 class="auth-title">تسجيل الدخول باستخدام Google</h1>
        <p class="auth-subtitle">للمتابعة إلى <strong>منصة خدومة (بوابة مقدمي الخدمات)</strong>. يرجى إدخال أي اسم وبريد إلكتروني لمحاكاة الاستجابة الآمنة.</p>

        <!-- Flash error -->
        <?php
        $flashError = \App\Core\Flash::get('error');
        if ($flashError):
        ?>
            <div class="alert-box">
                <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?= url('provider/auth/google/stub') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label for="name" class="form-label">الاسم الكامل (Display Name)</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="أبو أحمد النجار" value="أبو أحمد النجار" required autofocus>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">البريد الإلكتروني (Google Account Email)</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="abu_ahmad@gmail.com" value="abu_ahmad@gmail.com" required>
            </div>

            <div class="action-row">
                <a href="<?= url('provider/login') ?>" class="btn-link">إلغاء</a>
                <button type="submit" class="btn-submit">التالي</button>
            </div>
        </form>
    </div>

</body>
</html>
