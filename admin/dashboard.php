<?php
/**
 * dashboard.php
 * Khadomeh Admin Panel Dashboard
 * 
 * Secure control panel home page, displaying dynamic stats
 * from database repositories.
 */

// Include authentication guard (automatically runs init.php and session checks)
require_once dirname(__DIR__) . '/includes/auth.php';

use App\Repositories\ProviderRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\CityRepository;
use App\Repositories\AreaRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ReportRepository;

// Instantiate repositories
$providerRepo = new ProviderRepository();
$serviceRepo = new ServiceRepository();
$cityRepo = new CityRepository();
$areaRepo = new AreaRepository();
$reviewRepo = new ReviewRepository();
$reportRepo = new ReportRepository();

// Load statistics
$stats = [
    'providers_total' => $providerRepo->count(),
    'providers_pending' => $providerRepo->countByStatus('pending'),
    'providers_approved' => $providerRepo->countByStatus('approved'),
    'services_count' => $serviceRepo->count(),
    'cities_count' => $cityRepo->count(),
    'areas_count' => $areaRepo->count(),
    'reviews_pending' => $reviewRepo->countByStatus('pending'),
    'reports_open' => $reportRepo->countByStatus('open'),
];

$pageTitle = 'لوحة التحكم الإدارية';
require_once APP_DIR . '/includes/header.php';
?>

<div class="container">
    <!-- Title Bar -->
    <div class="dashboard-title-bar">
        <div>
            <h1 class="dashboard-title">لوحة الإشراف والمتابعة</h1>
            <p style="color: var(--text-secondary); margin-top: 5px;">أهلاً بك، <?= e($_SESSION['admin_name']) ?> (بصلاحيات: <?= e($_SESSION['admin_role']) ?>)</p>
        </div>
        <div>
            <a href="<?= base_url() ?>" target="_blank" class="btn btn-secondary">🔗 معاينة الموقع</a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <h2 style="font-size: 1.3rem; margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">إحصائيات المنصة العامة</h2>
    <div class="grid grid-4" style="margin-bottom: 50px;">
        <!-- 1. Active Providers -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">المزودون المعتمدون</span>
                <span class="stat-value"><?= $stats['providers_approved'] ?></span>
            </div>
            <div class="stat-icon">👷</div>
        </div>

        <!-- 2. Services -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">الخدمات المتوفرة</span>
                <span class="stat-value"><?= $stats['services_count'] ?></span>
            </div>
            <div class="stat-icon">⚙️</div>
        </div>

        <!-- 3. Cities -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">المحافظات المغطاة</span>
                <span class="stat-value"><?= $stats['cities_count'] ?></span>
            </div>
            <div class="stat-icon">📍</div>
        </div>

        <!-- 4. Areas -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">المناطق والأحياء</span>
                <span class="stat-value"><?= $stats['areas_count'] ?></span>
            </div>
            <div class="stat-icon">🗺️</div>
        </div>
    </div>

    <!-- Action items / Moderation Alerts -->
    <h2 style="font-size: 1.3rem; margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">حالة الطلبات والمراجعات الإدارية</h2>
    <div class="grid grid-3">
        <!-- 1. Providers Pending approval -->
        <div class="card" style="border-top: 4px solid var(--accent-primary);">
            <h3 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700;">طلبات تسجيل الحرفيين</h3>
            <p style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 20px;">حرفيون بانتظار مراجعة أوراقهم الثبوتية وأرقام هواتفهم للموافقة على عرضهم.</p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-latin);"><?= $stats['providers_pending'] ?> طلبات معلقة</span>
                <button class="btn btn-outline btn-sm" onclick="alert('سيتم تفعيل هذه الميزة في المرحلة الثانية (لوحة التحكم CRUD).')">عرض التفاصيل</button>
            </div>
        </div>

        <!-- 2. Reviews Pending -->
        <div class="card" style="border-top: 4px solid var(--accent-primary);">
            <h3 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700;">تقييمات بانتظار الموافقة</h3>
            <p style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 20px;">تعليقات وتقييمات العملاء المضافة حديثاً بانتظار تصفية المحتوى المسيء أو العشوائي.</p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-latin);"><?= $stats['reviews_pending'] ?> تقييمات معلقة</span>
                <button class="btn btn-outline btn-sm" onclick="alert('سيتم تفعيل هذه الميزة في المرحلة الثانية (لوحة التحكم CRUD).')">مراجعة التقييمات</button>
            </div>
        </div>

        <!-- 3. Reports Open -->
        <div class="card" style="border-top: 4px solid var(--error-color);">
            <h3 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700; color: var(--error-color);">شكاوى وبلاغات نشطة</h3>
            <p style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 20px;">شكاوى مرفوعة من قبل العملاء ضد مقدمي الخدمات بخصوص الأسعار أو المعاملة.</p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-latin); color: var(--error-color);"><?= $stats['reports_open'] ?> شكاوى مفتوحة</span>
                <button class="btn btn-outline btn-sm" onclick="alert('سيتم تفعيل هذه الميزة في المرحلة الثانية (لوحة التحكم CRUD).')">متابعة الشكاوى</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once APP_DIR . '/includes/footer.php';
?>
