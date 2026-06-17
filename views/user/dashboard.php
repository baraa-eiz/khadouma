<?php
/**
 * dashboard.php
 * User Portal Dashboard Shell
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - منصة خدومة</title>
    <!-- Tajawal Font -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Top Navbar */
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand svg {
            width: 32px;
            height: 32px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .user-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #dbeafe;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        @media (min-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 320px 1fr;
            }
        }

        /* Sidebar/Profile Card */
        .profile-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 30px;
            box-shadow: var(--shadow-sm);
            text-align: center;
            height: fit-content;
        }

        .profile-card-avatar {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            margin: 0 auto 15px;
        }

        .profile-card-avatar-placeholder {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background-color: #dbeafe;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            margin: 0 auto 15px;
        }

        .profile-name {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-info {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .profile-completeness {
            margin-bottom: 25px;
            text-align: right;
        }

        .completeness-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .progress-bar-bg {
            background-color: #f1f5f9;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, var(--primary), #8b5cf6);
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease-in-out;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(59, 130, 246, 0.05);
        }

        .btn-danger {
            background-color: #ef4444;
            color: #ffffff;
            border: none;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        /* Content Area */
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .content-section {
            background-color: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 30px;
            box-shadow: var(--shadow-sm);
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }

        /* Favorites Grid */
        .favorites-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (min-width: 992px) {
            .favorites-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .provider-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            gap: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .provider-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .provider-avatar {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
        }

        .provider-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
        }

        .provider-name {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-dark);
            text-decoration: none;
        }

        .provider-name:hover {
            color: var(--primary);
        }

        .provider-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            gap: 10px;
            margin-top: 4px;
        }

        .remove-fav-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s;
        }

        .remove-fav-btn:hover {
            color: #dc2626;
        }

        .empty-favorites {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-favorites svg {
            width: 48px;
            height: 48px;
            margin-bottom: 15px;
            color: #cbd5e1;
        }

        /* Alert and Success boxes */
        .alert-box {
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #065f46;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 30px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="/" class="navbar-brand">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>بوابة المستخدمين</span>
        </a>
        <div class="user-menu">
            <?php if ($user->avatar): ?>
                <img src="/<?= htmlspecialchars($user->avatar) ?>" alt="Avatar" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder">
                    <?= mb_substr($user->display_name, 0, 1) ?>
                </div>
            <?php endif; ?>
            <span style="font-weight: 700;"><?= htmlspecialchars($user->display_name) ?></span>
        </div>
    </nav>

    <div class="container">
        <!-- Flash Alert -->
        <?php
        $flashSuccess = \App\Core\Flash::get('success');
        if ($flashSuccess):
        ?>
            <div class="alert-box">
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Sidebar -->
            <div class="profile-card">
                <?php if ($user->avatar): ?>
                    <img src="/<?= htmlspecialchars($user->avatar) ?>" alt="Avatar" class="profile-card-avatar">
                <?php else: ?>
                    <div class="profile-card-avatar-placeholder">
                        <?= mb_substr($user->display_name, 0, 1) ?>
                    </div>
                <?php endif; ?>

                <h2 class="profile-name"><?= htmlspecialchars($user->display_name) ?></h2>
                
                <div class="profile-info">
                    <?php if ($user->email): ?>
                        <span><?= htmlspecialchars($user->email) ?></span>
                    <?php endif; ?>
                    <?php if ($user->phone): ?>
                        <span><?= htmlspecialchars($user->phone) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Completeness -->
                <div class="profile-completeness">
                    <div class="completeness-label">
                        <span>نسبة اكتمال الملف الشخصي</span>
                        <span><?= $completionScore ?>%</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $completionScore ?>%;"></div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="/user/profile/edit" class="btn btn-primary">تعديل الملف الشخصي ✏️</a>
                    <form action="/user/logout" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="btn btn-danger">تسجيل الخروج 🚪</button>
                    </form>
                </div>
            </div>

            <!-- Content Area -->
            <div class="main-content">
                <div class="content-section">
                    <h3 class="section-title">المفضلة الخاصة بي ⭐</h3>
                    
                    <?php if (empty($favoriteProviders)): ?>
                        <div class="empty-favorites">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <p>ليس لديك أي حرفيين مفضلين حتى الآن.</p>
                            <a href="/" class="btn btn-outline" style="margin-top: 15px; width: auto; display: inline-flex;">تصفح مقدمي الخدمات</a>
                        </div>
                    <?php else: ?>
                        <div class="favorites-grid">
                            <?php foreach ($favoriteProviders as $prov): ?>
                                <div class="provider-card">
                                    <div class="provider-details">
                                        <div>
                                            <a href="/provider/<?= htmlspecialchars($prov['slug']) ?>" class="provider-name">
                                                <?= htmlspecialchars($prov['display_name_ar']) ?>
                                            </a>
                                            <div class="provider-meta">
                                                <span>💼 <?= htmlspecialchars($prov['service_name'] ?? 'غير محدد') ?></span>
                                                <span>📍 <?= htmlspecialchars($prov['city_name'] ?? 'غير محدد') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <form action="/user/favorites/toggle" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="provider_id" value="<?= htmlspecialchars($prov['public_id']) ?>">
                                        <button type="submit" class="remove-fav-btn" title="إزالة من المفضلة">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 24px; height: 24px;">
                                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
