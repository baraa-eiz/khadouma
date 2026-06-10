<?php
/**
 * results.php
 * Khadomeh Public Search Results View
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

<div class="container search-results-page">
    <!-- Breadcrumbs -->
    <nav class="breadcrumb-nav" aria-label="مسار التنقل">
        <ul class="breadcrumb-list" style="display: flex; gap: 8px; font-size: 0.9rem; margin-bottom: 25px; list-style: none; padding: 0;">
            <li><a href="<?= base_url() ?>">الرئيسية</a></li>
            <span style="color: var(--text-secondary);">/</span>
            <li style="color: var(--text-secondary);">نتائج البحث</li>
        </ul>
    </nav>

    <div class="search-layout" style="display: grid; grid-template-columns: 280px 1fr; gap: 30px;">
        <!-- Mobile search toggle button (visible on mobile only) -->
        <style>
            .search-sidebar {
                background-color: var(--card-bg);
                border: 1px solid var(--border-color);
                border-radius: 12px;
                padding: 20px;
                height: fit-content;
                box-shadow: var(--shadow-subtle);
            }
            .results-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 20px;
            }
            @media (max-width: 768px) {
                .search-layout {
                    grid-template-columns: 1fr;
                }
                .search-sidebar {
                    margin-bottom: 20px;
                }
            }
            .badge-verified-icon {
                color: var(--success-color);
                font-weight: bold;
                font-size: 1rem;
            }
            .contact-btn {
                position: relative;
            }
            .contact-btn.loading::after {
                content: '';
                position: absolute;
                width: 14px;
                height: 14px;
                border: 2px solid transparent;
                border-top-color: currentColor;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>

        <!-- Filters Sidebar -->
        <aside class="search-sidebar">
            <h3 style="margin-bottom: 15px; font-size: 1.15rem; font-weight: 800; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">تصفية النتائج</h3>
            <form action="<?= base_url('search') ?>" method="GET" class="filter-form" style="display: flex; flex-direction: column; gap: 15px;">
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="filter-service">الخدمة المطلوبة</label>
                    <select name="service" id="filter-service" class="form-control">
                        <option value="">جميع الخدمات</option>
                        <?php foreach ($services as $srv): ?>
                            <option value="<?= e($srv['slug']) ?>" <?= ($filters['service'] === $srv['slug']) ? 'selected' : '' ?>>
                                <?= e($srv['display_name_ar']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="filter-city">المدينة</label>
                    <select name="city" id="filter-city" class="form-control">
                        <option value="">جميع المدن</option>
                        <?php foreach ($cities as $ct): ?>
                            <option value="<?= e($ct['slug']) ?>" data-id="<?= (int)$ct['id'] ?>" <?= ($filters['city'] === $ct['slug']) ? 'selected' : '' ?>>
                                <?= e($ct['display_name_ar']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="filter-area">المنطقة</label>
                    <select name="area" id="filter-area" class="form-control">
                        <option value="">جميع المناطق</option>
                        <!-- Populated by JS based on city selection -->
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="filter-keyword">كلمة مفتاحية</label>
                    <input type="text" name="keyword" id="filter-keyword" class="form-control" placeholder="اسم الحرفي، تخصص..." value="<?= e($filters['keyword']) ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">تطبيق الفلتر 🔍</button>
                <a href="<?= base_url('search') ?>" class="btn btn-secondary" style="width: 100%; text-align: center;">إعادة تعيين</a>
            </form>
        </aside>

        <!-- Results Main Section -->
        <main class="results-main">
            <!-- Results Counter & Search Summary -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary);">
                    <?php if ($selectedService && $selectedCity): ?>
                        <?= e($selectedService['display_name_ar']) ?> في <?= e($selectedCity['display_name_ar']) ?>
                        <?php if ($selectedArea): ?>
                            (<?= e($selectedArea['display_name_ar']) ?>)
                        <?php endif; ?>
                    <?php else: ?>
                        مزودو الخدمات المتاحون
                    <?php endif; ?>
                </h2>
                <span style="font-size: 0.95rem; font-weight: 600; color: var(--text-secondary); background: var(--bg-secondary); padding: 4px 12px; border-radius: 20px;">
                    <?= count($providers) ?> حرفيين
                </span>
            </div>

            <!-- Global notifications / errors -->
            <div id="contact-error-banner" class="alert alert-danger" style="display: none; margin-bottom: 20px;"></div>

            <!-- Providers Listing Grid -->
            <?php if (empty($providers)): ?>
                <?php
                // Include empty state component
                $empty_title = 'لم نجد أي نتائج تطابق بحثك';
                $empty_desc = 'جرب تغيير خيارات التصفية أو البحث عن كلمة مفتاحية أخرى للحصول على نتائج أفضل.';
                require APP_DIR . '/views/components/empty_state.php';
                ?>

                <div class="empty-suggestions-container" style="margin-top: 32px; background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 24px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">اقتراحات بديلة للبحث:</h3>
                    
                    <div style="margin-bottom: 20px;">
                        <span style="font-size: 13px; color: var(--text-secondary); display: block; margin-bottom: 8px;">تصفح الخدمات الشائعة:</span>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php 
                            $suggestedServices = array_slice($services, 0, 6);
                            foreach ($suggestedServices as $srv): 
                            ?>
                                <a href="<?= base_url('search?service=' . $srv['slug'] . ($selectedCity ? '&city=' . $selectedCity['slug'] : '')) ?>" class="badge badge-secondary" style="padding: 8px 14px; font-size: 13px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary); text-decoration: none; border-radius: 20px; transition: all 0.2s ease;">
                                    <?= e($srv['display_name_ar']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <span style="font-size: 13px; color: var(--text-secondary); display: block; margin-bottom: 8px;">أو ابحث في مدن أخرى:</span>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php 
                            $suggestedCities = array_slice($cities, 0, 6);
                            foreach ($suggestedCities as $cty): 
                            ?>
                                <a href="<?= base_url('search?city=' . $cty['slug'] . ($selectedService ? '&service=' . $selectedService['slug'] : '')) ?>" class="badge badge-secondary" style="padding: 8px 14px; font-size: 13px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary); text-decoration: none; border-radius: 20px; transition: all 0.2s ease;">
                                    <?= e($cty['display_name_ar']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                        <a href="<?= base_url('search') ?>" class="btn btn-primary btn-sm">إعادة تعيين البحث 🔄</a>
                        <a href="<?= base_url() ?>" class="btn btn-secondary btn-sm">العودة للرئيسية 🏠</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="results-grid">
                    <?php foreach ($providers as $prov): ?>
                        <article class="card provider-card">
                            <div>
                                <!-- Provider Header -->
                                <div class="provider-header" style="display: flex; gap: 15px; margin-bottom: 15px;">
                                    <div class="provider-img-wrapper" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border-color); flex-shrink: 0;">
                                        <img src="<?= get_provider_image($prov['profile_image'] ?? null, 60, 60, '👨‍🔧') ?>" alt="صورة <?= e($prov['display_name_ar']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                    </div>
                                    <div class="provider-info-header" style="display: flex; flex-direction: column; justify-content: center;">
                                        <h3 class="provider-name" style="font-size: 1.1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <a href="<?= base_url('provider/' . $prov['slug']) ?>" style="color: var(--text-primary);"><?= e($prov['display_name_ar']) ?></a>
                                            <?php if ($prov['verified']): ?>
                                                <span class="badge-tag badge-verified" style="font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; line-height: 1;">موثق</span>
                                            <?php endif; ?>
                                        </h3>
                                        <span class="provider-service-tag" style="font-size: 0.8rem; font-weight: 700; color: var(--accent-primary); background-color: #fdf2ee; padding: 2px 8px; border-radius: 4px; align-self: flex-start; margin-top: 4px;">
                                            <?= e($prov['service_name']) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Badges & Experience -->
                                <div class="provider-badges" style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                                    <?php if ($prov['verified']): ?>
                                        <span class="badge-tag badge-verified" style="font-size: 0.75rem; background-color: #eef7f0; color: var(--success-color); border: 1px solid #d9eedf; padding: 2px 8px; border-radius: 12px; font-weight: 600;">موثق</span>
                                    <?php endif; ?>
                                    <?php if ($prov['years_experience'] > 0): ?>
                                        <span class="badge-tag badge-exp" style="font-size: 0.75rem; background-color: #fcf6e8; color: #a8761e; border: 1px solid #f6eacf; padding: 2px 8px; border-radius: 12px; font-weight: 600;">خبرة <?= (int)$prov['years_experience'] ?> سنة</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Description -->
                                <p class="provider-desc-text" style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; height: 2.6rem;">
                                    <?= e($prov['short_description_ar']) ?>
                                </p>
                            </div>

                            <div>
                                <!-- Price, Rating, Location Meta -->
                                <div class="provider-meta" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 10px; margin-bottom: 15px; font-size: 0.85rem; color: var(--text-secondary);">
                                    <div class="provider-location" style="font-weight: 600;">
                                        📍 <?= e($prov['city_name']) ?>
                                        <?php if (!empty($prov['areas'])): ?>
                                            - <?= e($prov['areas'][0]['display_name_ar']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Contact & Action Buttons -->
                                <div class="provider-actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <button class="btn btn-primary contact-btn" data-provider-id="<?= (int)$prov['id'] ?>" data-method="phone_call" style="font-size: 0.85rem; padding: 8px 12px;">
                                        📞 اتصل الآن
                                    </button>
                                    <button class="btn btn-whatsapp-outline contact-btn" data-provider-id="<?= (int)$prov['id'] ?>" data-method="whatsapp_message" style="font-size: 0.85rem; padding: 8px 12px;">
                                        💬 واتساب
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Dynamic Area Selection Script & AJAX click interception -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dynamic Area Selector logic
    const areasList = [
        <?php
        // Generate complete lists of areas matching each city, so JS can swap them instantly offline
        $db = \App\Core\Database::getInstance();
        $allAreas = $db->fetchAll("SELECT id, city_id, slug, display_name_ar FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
        foreach ($allAreas as $ar) {
            echo "{id: " . (int)$ar['id'] . ", cityId: " . (int)$ar['city_id'] . ", slug: '" . addslashes($ar['slug']) . "', name: '" . addslashes($ar['display_name_ar']) . "'},\n";
        }
        ?>
    ];

    const citySelect = document.getElementById('filter-city');
    const areaSelect = document.getElementById('filter-area');
    const selectedAreaSlug = '<?= e($filters['area']) ?>';

    function updateAreas() {
        const selectedOption = citySelect.options[citySelect.selectedIndex];
        const cityId = selectedOption ? parseInt(selectedOption.getAttribute('data-id')) : null;

        // Clear previous options
        areaSelect.innerHTML = '<option value="">جميع المناطق</option>';

        if (!cityId) {
            areaSelect.disabled = true;
            return;
        }

        areaSelect.disabled = false;
        const filteredAreas = areasList.filter(a => a.cityId === cityId);
        filteredAreas.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.slug;
            opt.textContent = a.name;
            if (a.slug === selectedAreaSlug) {
                opt.selected = true;
            }
            areaSelect.appendChild(opt);
        });
    }

    if (citySelect) {
        citySelect.addEventListener('change', updateAreas);
        updateAreas(); // Run once initially
    }

    // 2. AJAX click intercept & event logging
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
                    source_page: 'search_results'
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
