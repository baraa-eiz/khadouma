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

// Fetch profile images for latest providers
foreach ($latestProviders as &$p) {
    $p['profile_image'] = $providerRepo->getProviderProfileImage($p['id']);
}
unset($p);

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

<!-- Inject structured data (JSON-LD) for Homepage -->
<?= json_ld_breadcrumbs([
    'الرئيسية' => base_url()
]) ?>

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

<!-- Error Banner for AJAX contact calls -->
<div class="container" style="margin-top: 20px;">
    <div id="contact-error-banner" class="alert alert-danger" style="display: none; margin-bottom: 20px;"></div>
</div>

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
                <div class="card provider-card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <?php
                    $cardContent = '
                        <div class="provider-header" style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div class="provider-img-wrapper" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border-color); flex-shrink: 0;">
                                <img src="' . get_provider_image($p['profile_image'] ?? null, 60, 60, '👨‍🔧') . '" alt="صورة ' . e($p['display_name_ar']) . '" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="provider-info-header" style="display: flex; flex-direction: column; justify-content: center;">
                                <h3 class="provider-name" style="font-size: 1.1rem; font-weight: 700; margin: 0; color: var(--text-primary); text-align: right;">' . e($p['display_name_ar']) . '</h3>
                                <span class="provider-service-tag" style="font-size: 0.8rem; font-weight: 700; color: var(--accent-primary); background-color: #fdf2ee; padding: 2px 8px; border-radius: 4px; align-self: flex-start; margin-top: 4px;">' . e($p['service_name']) . '</span>
                            </div>
                        </div>
                        
                        <div class="provider-badges" style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                            ' . ($p['verified'] ? '<span class="badge-tag badge-verified" style="font-size: 0.75rem; background-color: #eef7f0; color: var(--success-color); border: 1px solid #d9eedf; padding: 2px 8px; border-radius: 12px; font-weight: 600;">موثق</span>' : '') . '
                            ' . ($p['years_experience'] > 0 ? '<span class="badge-tag badge-exp" style="font-size: 0.75rem; background-color: #fcf6e8; color: #a8761e; border: 1px solid #f6eacf; padding: 2px 8px; border-radius: 12px; font-weight: 600;">خبرة ' . (int)$p['years_experience'] . ' سنة</span>' : '') . '
                        </div>
                        
                        <p class="provider-desc-text" style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; height: 2.6rem; text-align: right;">' . e($p['short_description_ar']) . '</p>
                    ';
                    echo '<div class="provider-card-body" style="flex-grow: 1;">' . $cardContent . '</div>';
                    ?>
                    
                    <div>
                        <div class="provider-meta" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 10px; margin-bottom: 15px; font-size: 0.85rem; color: var(--text-secondary);">
                            <span class="provider-location">📍 <?= e($p['city_name']) ?></span>
                        </div>
                        
                        <div class="provider-actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <button class="btn btn-primary contact-btn" data-provider-id="<?= (int)$p['id'] ?>" data-method="phone_call" style="font-size: 0.85rem; padding: 8px 12px;">
                                📞 اتصل الآن
                            </button>
                            <button class="btn btn-whatsapp-outline contact-btn" data-provider-id="<?= (int)$p['id'] ?>" data-method="whatsapp_message" style="font-size: 0.85rem; padding: 8px 12px;">
                                💬 واتساب
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- AJAX click tracking script for homepage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactButtons = document.querySelectorAll('.contact-btn');
    const errorBanner = document.getElementById('contact-error-banner');

    contactButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const providerId = btn.getAttribute('data-provider-id');
            const method = btn.getAttribute('data-method');
            
            if (btn.classList.contains('loading')) return;

            btn.classList.add('loading');
            errorBanner.style.display = 'none';

            fetch('<?= base_url("api/contact") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    provider_id: providerId,
                    method: method,
                    source_page: 'homepage'
                })
            })
            .then(response => {
                if (response.status === 429) {
                    throw new Error('لقد تجاوزت الحد الأقصى اليومي المسموح به للاتصال بمزودي الخدمات (5 مزودين).');
                }
                if (!response.ok) {
                    throw new Error('عذراً، فشل الاتصال بالخادم. يرجى المحاولة لاحقاً.');
                }
                return response.json();
            })
            .then(data => {
                btn.classList.remove('loading');
                if (data.success) {
                    if (method === 'phone_call') {
                        btn.innerHTML = '📞 ' + data.phone;
                        window.location.href = data.tel;
                    } else if (method === 'whatsapp_message') {
                        window.open(data.whatsapp, '_blank');
                    }
                } else {
                    errorBanner.textContent = data.message || 'حدث خطأ ما.';
                    errorBanner.style.display = 'block';
                    window.scrollTo({ top: errorBanner.offsetTop - 100, behavior: 'smooth' });
                }
            })
            .catch(err => {
                btn.classList.remove('loading');
                errorBanner.textContent = err.message || 'فشل الاتصال بالخادم. يرجى التأكد من اتصال الإنترنت.';
                errorBanner.style.display = 'block';
                window.scrollTo({ top: errorBanner.offsetTop - 100, behavior: 'smooth' });
            });
        });
    });
});
</script>

