<?php
/**
 * home.php
 * Khadomeh Platform Public Homepage
 * 
 * Fetches services, cities, and verified providers,
 * rendering them inside the shared "Light Warm Trust" layout.
 */

if (!defined('IN_APP')) {
    exit;
}

use App\Repositories\ServiceRepository;
use App\Repositories\ProviderRepository;
use App\Repositories\CityRepository;

// Instantiate repositories
$serviceRepo = new ServiceRepository();
$providerRepo = new ProviderRepository();
$cityRepo = new CityRepository();

// Load required data from database
$services = $serviceRepo->getAllActive();
$cities = $cityRepo->getAllActive();
$latestProviders = $providerRepo->getLatestApproved(6);

$pageTitle = 'الصفحة الرئيسية | دليل الحرفيين والخدمات المنزلية بدمشق وسوريا';
$metaDesc = 'دليل منصة خدومة يربطك بأفضل الفنيين والعمال للخدمات المنزلية والصيانة في سوريا مباشرة بدون عمولات. تنظيف، سباكة، كهرباء، دهان، ونقل أثاث.';

// front-controller bootstrap: load inside layout wrapper
$viewPath = __FILE__;
if (isset($isLayoutCalled) && $isLayoutCalled) {
    // Content rendering phase
} else {
    $isLayoutCalled = true;
    require APP_DIR . '/includes/layout.php';
    exit;
}
?>

<!-- Hero Welcoming Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="hero-title" style="font-family: var(--font-arabic);">تبحث عن معلم صيانة؟ تواصل مع حرفيي دمشق مباشرة</h1>
        <p class="hero-subtitle">دليلك الموثوق للوصول إلى مزودي الخدمات المنزلية والصيانة في منطقتك. مجاني بالكامل وبدون أي عمولة.</p>
        
        <!-- Search Filter Component -->
        <div class="search-container">
            <form action="<?= base_url('search') ?>" method="GET" class="search-form">
                <div class="search-field">
                    <select name="service" class="form-control" aria-label="اختر الخدمة">
                        <option value="">جميع الخدمات</option>
                        <?php foreach ($services as $srv): ?>
                            <option value="<?= e($srv['slug']) ?>"><?= e($srv['display_name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="search-field">
                    <select name="city" class="form-control" aria-label="اختر المدينة">
                        <option value="">جميع المدن</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= e($city['slug']) ?>"><?= e($city['display_name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">ابحث الآن 🔍</button>
            </form>
        </div>
    </div>
</section>

<!-- Services Grid List -->
<section class="services-section container" style="margin-top: 60px;">
    <h2 class="section-title">تصفح الخدمات المتوفرة</h2>
    <div class="grid grid-5" style="margin-top: 30px;">
        <?php foreach ($services as $srv): ?>
            <a href="<?= base_url('services/' . $srv['slug']) ?>" class="card service-card">
                <div class="service-icon">
                    <?php
                    $icon = '🛠️';
                    if ($srv['key'] === 'cleaning') $icon = '🧹';
                    if ($srv['key'] === 'plumbing') $icon = '🚰';
                    if ($srv['key'] === 'electricity') $icon = '⚡';
                    if ($srv['key'] === 'painting') $icon = '🎨';
                    if ($srv['key'] === 'moving') $icon = '📦';
                    echo $icon;
                    ?>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-top: 5px;"><?= e($srv['short_name_ar']) ?></h3>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured and Latest Providers -->
<section class="providers-section container" style="margin-top: 80px; margin-bottom: 40px;">
    <h2 class="section-title">أحدث الحرفيين المعتمدين بدمشق</h2>
    <div class="grid grid-3" style="margin-top: 40px;">
        <?php if (empty($latestProviders)): ?>
            <p class="text-center" style="grid-column: 1 / -1; color: var(--text-secondary); padding: 40px 0;">لا يوجد مزودو خدمات معتمدون حالياً.</p>
        <?php else: ?>
            <?php foreach ($latestProviders as $p): ?>
                <div class="card provider-card">
                    <div class="provider-header">
                        <div class="provider-img-wrapper">
                            <!-- Fallback to base64 SVG dynamically -->
                            <img src="<?= get_provider_image('', 150, 150, mb_substr($p['display_name_ar'], 0, 8)) ?>" alt="<?= e($p['display_name_ar']) ?>">
                        </div>
                        <div class="provider-info-header">
                            <h3 class="provider-name"><?= e($p['display_name_ar']) ?></h3>
                            <span class="provider-service-tag"><?= e($p['service_name']) ?></span>
                        </div>
                    </div>
                    
                    <div class="provider-badges">
                        <?php if ($p['verified']): ?>
                            <span class="badge-tag badge-verified">✓ موثق</span>
                        <?php endif; ?>
                        <?php if ($p['phone_verified']): ?>
                            <span class="badge-tag badge-verified">✓ هاتف موثق</span>
                        <?php endif; ?>
                        <?php if ($p['years_experience'] > 0): ?>
                            <span class="badge-tag badge-exp">★ <?= e($p['years_experience']) ?> سنة خبرة</span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="provider-desc-text"><?= e($p['short_description_ar']) ?></p>
                    
                    <div class="provider-meta">
                        <span class="provider-location">📍 دمشق، <?= e($p['city_name']) ?></span>
                        <div class="provider-rating">
                            <span class="star-icon">★</span>
                            <span style="font-family: var(--font-latin);"><?= number_format($p['rating'], 1) ?></span>
                            <span style="font-size: 0.8rem; color: var(--text-secondary);"> (<?= e($p['reviews_count']) ?> تقييم)</span>
                        </div>
                    </div>
                    
                    <div class="provider-actions">
                        <a href="tel:<?= e($p['phone']) ?>" class="btn btn-secondary btn-sm">📞 اتصل الآن</a>
                        <?php if (!empty($p['whatsapp'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $p['whatsapp']) ?>" target="_blank" class="btn btn-whatsapp btn-sm">💬 راسل واتساب</a>
                        <?php else: ?>
                            <button class="btn btn-outline btn-sm" disabled style="opacity: 0.5;">لا يوجد واتساب</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
