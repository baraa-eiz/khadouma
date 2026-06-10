<?php
/**
 * provider.php
 * Khadomeh Public Provider Profile View
 */

if (!defined('IN_APP')) {
    exit;
}

if (isset($isLayoutCalled) && $isLayoutCalled) {
    // Content rendering phase
} else {
    $isLayoutCalled = true;
    $viewPath = __FILE__;
    require APP_DIR . '/includes/layout.php';
    return;
}
?>

<!-- Inject structured data (JSON-LD) -->
<?= json_ld_breadcrumbs($breadcrumbs) ?>
<?php
$schemaData = [
    'full_name' => $provider['display_name_ar'],
    'avatar_url' => $profileImage ?: '',
    'phone' => $provider['phone'],
    'city_name' => $provider['city_name'],
    'area_name' => !empty($areasCovered) ? $areasCovered[0]['display_name_ar'] : '',
    'average_rating' => $provider['rating'],
    'reviews_count' => $provider['reviews_count']
];
echo json_ld_local_business($schemaData);
?>

<div class="container provider-profile-page" style="margin-top: 20px;">
    <!-- Top Nav Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb-nav" aria-label="مسار التنقل" style="margin-bottom: 0;">
            <ul class="breadcrumb-list" style="display: flex; flex-wrap: wrap; gap: 8px; font-size: 0.9rem; list-style: none; padding: 0; margin: 0;">
                <?php $i = 0; $totalCrumbs = count($breadcrumbs); ?>
                <?php foreach ($breadcrumbs as $name => $link): ?>
                    <?php $i++; ?>
                    <?php if ($i === $totalCrumbs): ?>
                        <li style="color: var(--text-secondary);"><?= e($name) ?></li>
                    <?php else: ?>
                        <li><a href="<?= e($link) ?>" style="color: var(--text-primary); text-decoration: none;"><?= e($name) ?></a></li>
                        <span style="color: var(--text-secondary);">/</span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
        <a href="javascript:history.back()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-weight: bold;">
            ← العودة لنتائج البحث
        </a>
    </div>

    <!-- Error Banner for AJAX contact calls -->
    <div id="contact-error-banner" class="alert alert-danger" style="display: none; margin-bottom: 20px;"></div>

    <div class="profile-layout">
        
        <!-- Sidebar Info -->
        <aside class="profile-sidebar" style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Main Details Card -->
            <div class="card" style="padding: 25px; text-align: center; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px;">
                <div class="avatar-large-wrapper" style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid var(--border-color); margin: 0 auto 15px auto; background-color: var(--bg-secondary);">
                    <img src="<?= get_provider_image($profileImage ?? null, 120, 120, '👨‍🔧') ?>" alt="صورة <?= e($provider['display_name_ar']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>

                <h1 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin-bottom: 5px;"><?= e($provider['display_name_ar']) ?></h1>
                
                <span style="font-size: 0.9rem; font-weight: 700; color: var(--accent-primary); background-color: #fdf2ee; padding: 4px 12px; border-radius: 4px; display: inline-block; margin-bottom: 15px;">
                    <?= e($provider['service_name']) ?>
                </span>

                <!-- Contact Buttons (Priority 1) -->
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; border-top: 1px solid var(--border-color); padding-top: 15px;">
                    <button class="btn btn-primary contact-btn" data-provider-id="<?= (int)$provider['id'] ?>" data-method="phone_call" style="font-size: 1rem; font-weight: 700; width: 100%;">
                        📞 اتصل بالطلب
                    </button>
                    <button class="btn btn-whatsapp-outline contact-btn" data-provider-id="<?= (int)$provider['id'] ?>" data-method="whatsapp_message" style="font-size: 1rem; font-weight: 700; width: 100%;">
                        💬 مراسلة عبر واتساب
                    </button>
                </div>

                <!-- Verified status badges (Priority 2) -->
                <div class="verified-badges-list" style="display: flex; flex-direction: column; gap: 8px; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 15px 0; margin-bottom: 20px; text-align: right;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                        <span style="font-size: 1.1rem;"><?= $provider['verified'] ? '✅' : '❌' ?></span>
                        <span>توثيق الهوية والملف</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                        <span style="font-size: 1.1rem;"><?= $provider['phone_verified'] ? '✅' : '❌' ?></span>
                        <span>التحقق من رقم الهاتف</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                        <span style="font-size: 1.1rem;"><?= $provider['identity_verified'] ? '✅' : '❌' ?></span>
                        <span>فحص السجل الجنائي والخلفية</span>
                    </div>
                </div>

                <!-- Pricing (Priority 3) -->
                <div style="margin-bottom: 10px;">
                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 4px;">بداية الأسعار المتوقعة</div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary);">
                        <?php if (!empty($provider['starting_price'])): ?>
                            <?= number_format($provider['starting_price']) ?> ل.س <span style="font-size: 0.85rem; font-weight: 400; color: var(--text-secondary);">/ <?= e($provider['price_unit'] === 'hour' ? 'ساعة' : ($provider['price_unit'] === 'job' ? 'خدمة' : 'يوم')) ?></span>
                        <?php else: ?>
                            <span style="font-size: 0.95rem; font-weight: 600; color: var(--text-secondary);">غير محدد</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Meta Quick Details Card -->
            <div class="card" style="padding: 20px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; font-size: 0.9rem; display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <span style="color: var(--text-secondary);">نوع العمل:</span>
                    <strong style="float: left; color: var(--text-primary);"><?= $provider['business_type'] === 'company' ? 'شركة / ورشة' : 'حرفي مستقل' ?></strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary);">سنوات الخبرة:</span>
                    <strong style="float: left; color: var(--text-primary);"><?= (int)$provider['years_experience'] ?> سنوات</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary);">التواجد اليوم:</span>
                    <strong style="float: left; color: <?= $provider['available_today'] ? 'var(--success-color)' : 'var(--danger-color)' ?>;"><?= $provider['available_today'] ? 'متاح للعمل' : 'غير متاح حالياً' ?></strong>
                </div>
            </div>
        </aside>

        <!-- Main Profile Info -->
        <main class="profile-main" style="display: flex; flex-direction: column; gap: 25px;">
            <!-- Description -->
            <section class="card" style="padding: 30px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px;">
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">نبذة عن الحرفي</h2>
                <p style="font-size: 1rem; color: var(--text-primary); line-height: 1.7; white-space: pre-line; margin-bottom: 20px;">
                    <?= e($provider['description_ar'] ?: $provider['short_description_ar']) ?>
                </p>

                <!-- Areas covered -->
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">📍 مناطق التغطية والخدمة</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php if (empty($areasCovered)): ?>
                        <span style="font-size: 0.9rem; color: var(--text-secondary);">يغطي جميع مناطق مدينة <?= e($provider['city_name']) ?></span>
                    <?php else: ?>
                        <?php foreach ($areasCovered as $ar): ?>
                            <span style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                <?= e($ar['display_name_ar']) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Secondary Services -->
                <?php if (!empty($secondaryServices)): ?>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-top: 20px; margin-bottom: 10px;">🛠️ خدمات إضافية وتخصصات</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($secondaryServices as $ss): ?>
                            <span style="background-color: #fbf5f3; border: 1px solid #f2e2dd; color: var(--accent-primary); padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                <?= e($ss['display_name_ar']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Work Photos Gallery -->
            <?php if (!empty($workPhotos)): ?>
                <section class="card" style="padding: 30px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px;">
                    <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">معرض أعمالي وصوري</h2>
                    <div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px;">
                        <?php foreach ($workPhotos as $photo): ?>
                            <?php $imgUrl = get_provider_image($photo['image_path'], 200, 150, e($photo['alt_text_ar'] ?: 'عمل فني')); ?>
                            <div class="gallery-item" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color); aspect-ratio: 4/3; cursor: pointer; background: #000;">
                                <img src="<?= $imgUrl ?>" alt="<?= e($photo['alt_text_ar'] ?: 'عمل فني') ?>" style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=1" onclick="openLightbox('<?= base_url($photo['image_path']) ?>')">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- Simple Lightbox Modal (For Gallery Images) -->
<div id="gallery-lightbox" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background: rgba(0,0,0,0.85); z-index: 1000; justify-content: center; align-items: center;" onclick="closeLightbox()">
    <img id="lightbox-img" src="" alt="صورة مكبرة" style="max-width: 90%; max-height: 90%; border-radius: 8px; border: 3px solid #fff;">
</div>

<script>
// Lightbox functions
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('gallery-lightbox').style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('gallery-lightbox').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // AJAX click intercept & event logging
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
                    source_page: 'provider_profile'
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
                        // Open whatsapp link
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
