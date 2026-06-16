<?php
/**
 * wizard.php
 * Provider Profile Onboarding Wizard
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

<div class="container provider-wizard-container" style="margin-top: 30px; margin-bottom: 60px;">
    <!-- Top Progress Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">إعداد وتعديل ملفك المهني</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">أدخل تفاصيلك بدقة لجذب العملاء وتسهيل الموافقة على ملفك.</p>
        </div>
        <a href="<?= url('provider/dashboard') ?>" class="btn btn-secondary btn-sm" style="font-weight: 700;">
            ← العودة للوحة التحكم
        </a>
    </div>

    <!-- Wizard Steps Indicator -->
    <div class="wizard-steps-indicator" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; background: #ffffff; border: 1px solid var(--border-color); border-radius: 12px; padding: 15px 25px; gap: 10px; overflow-x: auto;">
        <?php 
        $steps = [
            1 => ['label' => 'بيانات الهوية', 'icon' => '🆔'],
            2 => ['label' => 'الموقع والتخصص', 'icon' => '📍'],
            3 => ['label' => 'التفاصيل والأسعار', 'icon' => '🛠️'],
            4 => ['label' => 'الصور والشعار', 'icon' => '🖼️'],
            5 => ['label' => 'محركات البحث والنشر', 'icon' => '🚀']
        ];
        foreach ($steps as $num => $stepData):
        ?>
            <div class="step-indicator-item" id="step-indicator-<?= $num ?>" data-step="<?= $num ?>" style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.85rem; color: <?= $num === 1 ? 'var(--accent-primary)' : 'var(--text-secondary)' ?>; white-space: nowrap; cursor: pointer;">
                <span class="step-number-circle" style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: <?= $num === 1 ? 'var(--accent-primary)' : '#e5e7eb' ?>; color: #fff; font-size: 0.75rem;">
                    <?= $num ?>
                </span>
                <span><?= e($stepData['label']) ?></span>
            </div>
            <?php if ($num < 5): ?>
                <div style="flex: 1; height: 2px; background-color: #e5e7eb; min-width: 20px;"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Floating Autosave Status -->
    <div id="autosave-status" style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 15px; justify-content: flex-end; height: 20px;">
        <span id="autosave-indicator-dot" style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span>
        <span id="autosave-text">تم حفظ جميع التغييرات</span>
    </div>

    <!-- Main Wizard Form -->
    <form id="wizard-form" method="POST" enctype="multipart/form-data" action="<?= url('provider/wizard/submit') ?>" class="card" style="padding: 30px; border-radius: 16px; border: 1px solid var(--border-color); background: #ffffff;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="current_step" id="current_step" value="1">

        <!-- ================= STEP 1: IDENTITY & CORE ================= -->
        <div class="wizard-step-panel" id="step-panel-1" style="display: block;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">1. بيانات الهوية الأساسية والاتصال</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">الاسم التجاري أو الشخصي بالكامل <span style="color:red;">*</span></label>
                    <input type="text" name="display_name_ar" id="display_name_ar" class="form-control" value="<?= e($draft['display_name_ar']) ?>" required placeholder="مثال: أبو أحمد للسباكة المنزلية">
                </div>

                <div class="form-group">
                    <label class="form-label">الرابط الفريد لملفك (Slug) <span style="color:red;">*</span></label>
                    <input type="text" name="slug" id="slug" class="form-control" value="<?= e($draft['slug']) ?>" required placeholder="auto-generated-slug-ar">
                    <small style="color: var(--text-secondary); font-size: 0.75rem;">سيصبح رابط ملفك: khadomeh.com/provider/your-slug</small>
                </div>

                <div class="form-group">
                    <label class="form-label">نوع مقدم الخدمة <span style="color:red;">*</span></label>
                    <select name="business_type" class="form-control">
                        <option value="individual" <?= $draft['business_type'] === 'individual' ? 'selected' : '' ?>>حرفي مستقل (فردي)</option>
                        <option value="company" <?= $draft['business_type'] === 'company' ? 'selected' : '' ?>>شركة / ورشة عمل جماعية</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">رقم الهاتف الأساسي <span style="color:red;">*</span></label>
                    <input type="text" name="phone" class="form-control" value="<?= e($draft['phone']) ?>" required placeholder="09xxxxxxxx">
                </div>

                <div class="form-group">
                    <label class="form-label">رقم الواتساب <span style="color:red;">*</span></label>
                    <input type="text" name="whatsapp" class="form-control" value="<?= e($draft['whatsapp']) ?>" required placeholder="09xxxxxxxx">
                </div>

                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني المهني</label>
                    <input type="email" name="email" class="form-control" value="<?= e($draft['email']) ?>" placeholder="your-email@example.com">
                </div>
            </div>
        </div>

        <!-- ================= STEP 2: LOCATION & CATEGORY ================= -->
        <div class="wizard-step-panel" id="step-panel-2" style="display: none;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">2. التصنيف المهني ونطاق التغطية الجغرافية</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">
                <div class="form-group">
                    <label class="form-label">المدينة الرئيسية للخدمة <span style="color:red;">*</span></label>
                    <select name="city_id" id="city_id" class="form-control" required>
                        <option value="">-- اختر المدينة --</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= $city['id'] ?>" <?= (int)$draft['city_id'] === (int)$city['id'] ? 'selected' : '' ?>><?= e($city['display_name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">المهنة الرئيسية <span style="color:red;">*</span></label>
                    <select name="primary_service_id" class="form-control" required>
                        <option value="">-- اختر الخدمة/التخصص الرئيسي --</option>
                        <?php foreach ($services as $srv): ?>
                            <option value="<?= $srv['id'] ?>" <?= (int)$draft['primary_service_id'] === (int)$srv['id'] ? 'selected' : '' ?>><?= e($srv['display_name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Secondary Services Checkboxes -->
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label" style="font-weight: 800; display: block; margin-bottom: 10px;">🛠️ خدمات وتخصصات ثانوية تقدمها:</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;">
                    <?php foreach ($services as $srv): ?>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                            <input type="checkbox" name="secondary_services[]" value="<?= $srv['id'] ?>" <?= in_array((int)$srv['id'], $draft['secondary_services_json'] ?? []) ? 'checked' : '' ?>>
                            <span><?= e($srv['display_name_ar']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Coverage Areas (Filtered dynamically via JS) -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 800; display: block; margin-bottom: 10px;">📍 تحديد مناطق التغطية بالتفصيل:</label>
                <p id="no-city-notice" style="color: var(--text-secondary); font-size: 0.9rem; display: <?= $draft['city_id'] ? 'none' : 'block' ?>;">يرجى تحديد المدينة أولاً لعرض المناطق المتوفرة.</p>
                
                <div id="areas-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;">
                    <?php foreach ($areas as $area): ?>
                        <label class="area-checkbox-wrapper" data-city-id="<?= $area['city_id'] ?>" style="display: <?= (int)$draft['city_id'] === (int)$area['city_id'] ? 'flex' : 'none' ?>; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                            <input type="checkbox" name="coverage_areas[]" class="area-chk" value="<?= $area['id'] ?>" <?= in_array((int)$area['id'], $draft['coverage_areas_json'] ?? []) ? 'checked' : '' ?>>
                            <span><?= e($area['display_name_ar']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ================= STEP 3: PROFESSIONAL DETAILS ================= -->
        <div class="wizard-step-panel" id="step-panel-3" style="display: none;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">3. التفاصيل والخبرات المهنية والأسعار</h2>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">نبذة مختصرة عن عملك (تظهر في نتائج البحث) <span style="color:red;">*</span></label>
                <input type="text" name="short_description_ar" class="form-control" value="<?= e($draft['short_description_ar']) ?>" required placeholder="مثال: خبرة 10 سنوات في فك وتركيب كافة أنواع الأدوات الصحية وصيانة الشبكات">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">وصف تفصيلي وخبراتك (يظهر بداخل ملفك الشخصي) <span style="color:red;">*</span></label>
                <textarea name="description_ar" rows="6" class="form-control" required placeholder="اكتب بالتفصيل عن خدماتك، الأدوات المستخدمة، طريقتك في العمل، وأوقات الاستجابة..."><?= e($draft['description_ar']) ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 25px;">
                <div class="form-group">
                    <label class="form-label">سنوات الخبرة الفعلية <span style="color:red;">*</span></label>
                    <input type="number" name="years_experience" class="form-control" min="0" value="<?= (int)$draft['years_experience'] ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">بداية الأسعار المتوقعة (ل.س) <span style="color:red;">*</span></label>
                    <input type="number" name="starting_price" class="form-control" min="0" value="<?= $draft['starting_price'] !== null ? (float)$draft['starting_price'] : '' ?>" required placeholder="مثال: 10000">
                </div>

                <div class="form-group">
                    <label class="form-label">وحدة تسعير الخدمة <span style="color:red;">*</span></label>
                    <select name="price_unit" class="form-control" required>
                        <option value="hour" <?= $draft['price_unit'] === 'hour' ? 'selected' : '' ?>>لكل ساعة</option>
                        <option value="job" <?= $draft['price_unit'] === 'job' ? 'selected' : '' ?>>لكل خدمة</option>
                        <option value="day" <?= $draft['price_unit'] === 'day' ? 'selected' : '' ?>>لكل يوم</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">الموقع الإلكتروني الشخصي</label>
                    <input type="url" name="website" class="form-control" value="<?= e($draft['website']) ?>" placeholder="https://example.com">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">أوقات وساعات العمل</label>
                <input type="text" name="working_hours" class="form-control" value="<?= e($draft['working_hours']) ?>" placeholder="مثال: يومياً من 8 صباحاً حتى 9 مساءً عدا الجمعة">
            </div>

            <!-- Social Links -->
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-primary); margin-bottom: 15px;">🔗 روابط ومواقع التواصل الاجتماعي</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">صفحة فيسبوك (Facebook)</label>
                        <input type="url" name="social_links[facebook]" class="form-control" value="<?= e($draft['social_links']['facebook'] ?? '') ?>" placeholder="https://facebook.com/username">
                    </div>
                    <div class="form-group">
                        <label class="form-label">رابط إنستغرام (Instagram)</label>
                        <input type="url" name="social_links[instagram]" class="form-control" value="<?= e($draft['social_links']['instagram'] ?? '') ?>" placeholder="https://instagram.com/username">
                    </div>
                    <div class="form-group">
                        <label class="form-label">يوتيوب (YouTube)</label>
                        <input type="url" name="social_links[youtube]" class="form-control" value="<?= e($draft['social_links']['youtube'] ?? '') ?>" placeholder="https://youtube.com/channel">
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= STEP 4: MEDIA GALLERY ================= -->
        <div class="wizard-step-panel" id="step-panel-4" style="display: none;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">4. معرض الصور والشعار التعريفي</h2>
            
            <!-- Logo Section -->
            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 25px;">
                <div class="logo-preview-box" style="width: 100px; height: 100px; border-radius: 50%; border: 2px dashed var(--border-color); overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: var(--bg-secondary);">
                    <?php if (!empty($draft['logo_path'])): ?>
                        <img id="logo-preview" src="<?= url($draft['logo_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span id="logo-preview-placeholder" style="font-size: 1.8rem; color: var(--text-secondary);">👨‍🔧</span>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <label class="form-label" style="font-weight: 800; margin-bottom: 8px;">صورة الملف الشخصي أو الشعار (Logo)</label>
                    <input type="file" name="logo" id="logo-file-input" accept="image/*" style="margin-bottom: 10px; display: block;">
                    
                    <?php if (!empty($draft['logo_path'])): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-logo" style="font-weight: 600;">
                            🗑️ حذف الشعار الحالي
                        </button>
                    <?php endif; ?>
                    <p style="color: var(--text-secondary); font-size: 0.75rem; margin-top: 5px;">الحجم المسموح: 5 ميغابايت كحد أقصى (امتداد JPG, PNG, WEBP).</p>
                </div>
            </div>

            <!-- Gallery Section -->
            <div>
                <label class="form-label" style="font-weight: 800; display: block; margin-bottom: 8px;">🖼️ صور لأعمالك السابقة (معرض الصور)</label>
                <input type="file" name="gallery_photos[]" id="gallery-file-input" accept="image/*" multiple style="margin-bottom: 20px; display: block;">
                
                <div id="gallery-previews" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px;">
                    <?php 
                    $photos = $draft['work_photos_json'] ?? [];
                    foreach ($photos as $photo):
                    ?>
                        <div class="gallery-photo-card" style="position: relative; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color); aspect-ratio: 4/3;">
                            <img src="<?= url($photo) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <button type="button" class="btn-delete-photo" data-path="<?= e($photo) ?>" style="position: absolute; top: 5px; left: 5px; background: rgba(220, 38, 38, 0.9); border: none; color: #fff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.8rem;" title="حذف الصورة">
                                ✕
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ================= STEP 5: SEO & SUBMISSION ================= -->
        <div class="wizard-step-panel" id="step-panel-5" style="display: none;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">5. تحسين محركات البحث (SEO) والتقديم النهائي</h2>
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="form-group">
                    <label class="form-label">عنوان صفحة جوجل (Meta Title)</label>
                    <input type="text" name="meta_title_ar" class="form-control" value="<?= e($draft['meta_title_ar']) ?>" placeholder="مثال: نجار تركيب وصيانة غرف نوم في دمشق - منصة خدومة">
                    <small style="color: var(--text-secondary); font-size: 0.75rem;">العنوان الذي يظهر في نتائج بحث محركات البحث (يفضل ألا يتجاوز 60 حرفاً).</small>
                </div>

                <div class="form-group">
                    <label class="form-label">وصف البحث (Meta Description)</label>
                    <textarea name="meta_description_ar" rows="3" class="form-control" placeholder="اكتب وصفاً جذاباً لجذب الباحثين في محركات البحث..."><?= e($draft['meta_description_ar']) ?></textarea>
                    <small style="color: var(--text-secondary); font-size: 0.75rem;">الملخص الصغير الذي يظهر تحت العنوان في نتائج بحث جوجل (يفضل ألا يتجاوز 160 حرفاً).</small>
                </div>
            </div>

            <!-- Complete Check Notice -->
            <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 25px; border-radius: 12px; text-align: center;">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">مستعد لإرسال التغييرات؟</h3>
                <p style="color: var(--text-secondary); font-size: 0.9rem; max-width: 500px; margin: 0 auto 20px auto; line-height: 1.6;">
                    عند النقر على زر تقديم المراجعة، سيتم قفل إمكانية تعديل مسودتك وإرسالها لمشرف المنصة للتحقق منها واعتمادها على الفور.
                </p>
                <button type="submit" class="btn btn-primary" style="font-weight: 800; padding: 12px 36px; font-size: 1rem;">
                    🚀 تقديم الملف للمراجعة والنشر
                </button>
            </div>
        </div>

        <!-- Navigation buttons (Footer of form) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 35px; border-top: 1px solid var(--border-color); padding-top: 20px;">
            <button type="button" class="btn btn-secondary" id="btn-prev" style="font-weight: 700; display: none;">
                ← الخطوة السابقة
            </button>
            <span style="flex: 1;"></span>
            <button type="button" class="btn btn-primary" id="btn-next" style="font-weight: 700;">
                الخطوة التالية →
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 5;
    
    const form = document.getElementById('wizard-form');
    const prevBtn = document.getElementById('btn-prev');
    const nextBtn = document.getElementById('btn-next');
    const currentStepInput = document.getElementById('current_step');
    
    const autosaveStatus = document.getElementById('autosave-text');
    const autosaveDot = document.getElementById('autosave-indicator-dot');

    // Dynamic Areas Filtering by City
    const citySelect = document.getElementById('city_id');
    const noCityNotice = document.getElementById('no-city-notice');
    const areaCheckboxes = document.querySelectorAll('.area-checkbox-wrapper');

    citySelect.addEventListener('change', function() {
        const cityId = this.value;
        if (cityId) {
            noCityNotice.style.display = 'none';
            areaCheckboxes.forEach(wrapper => {
                if (wrapper.getAttribute('data-city-id') === cityId) {
                    wrapper.style.display = 'flex';
                } else {
                    wrapper.style.display = 'none';
                    // Uncheck hidden checkboxes to prevent pollution
                    wrapper.querySelector('input').checked = false;
                }
            });
        } else {
            noCityNotice.style.display = 'block';
            areaCheckboxes.forEach(wrapper => {
                wrapper.style.display = 'none';
                wrapper.querySelector('input').checked = false;
            });
        }
        // Save dynamically when city changes
        triggerAutosave();
    });

    // Auto-slugify Display Name
    const nameInput = document.getElementById('display_name_ar');
    const slugInput = document.getElementById('slug');
    nameInput.addEventListener('blur', function() {
        if (!slugInput.value.trim() && nameInput.value.trim()) {
            // Basic client-side arabic slugification
            let slug = nameInput.value.trim()
                .replace(/[^\u0600-\u06FFa-zA-Z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .toLowerCase();
            slugInput.value = slug;
            triggerAutosave();
        }
    });

    // Step indicators click behavior
    document.querySelectorAll('.step-indicator-item').forEach(item => {
        item.addEventListener('click', function() {
            const targetStep = parseInt(this.getAttribute('data-step'));
            switchPanel(targetStep);
        });
    });

    // Next/Prev buttons click handlers
    nextBtn.addEventListener('click', function() {
        if (currentStep < totalSteps) {
            switchPanel(currentStep + 1);
        }
    });

    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            switchPanel(currentStep - 1);
        }
    });

    function switchPanel(step) {
        // Hide all panels
        document.querySelectorAll('.wizard-step-panel').forEach(panel => {
            panel.style.display = 'none';
        });

        // Show selected panel
        document.getElementById(`step-panel-${step}`).style.display = 'block';

        // Update indicators
        document.querySelectorAll('.step-indicator-item').forEach(ind => {
            const num = parseInt(ind.getAttribute('data-step'));
            const circle = ind.querySelector('.step-number-circle');
            if (num === step) {
                ind.style.color = 'var(--accent-primary)';
                circle.style.backgroundColor = 'var(--accent-primary)';
            } else if (num < step) {
                ind.style.color = '#10b981';
                circle.style.backgroundColor = '#10b981';
            } else {
                ind.style.color = 'var(--text-secondary)';
                circle.style.backgroundColor = '#e5e7eb';
            }
        });

        // Show/hide buttons
        if (step === 1) {
            prevBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'inline-block';
        }

        if (step === totalSteps) {
            nextBtn.style.display = 'none';
        } else {
            nextBtn.style.display = 'inline-block';
        }

        // Trigger autosave on change of steps
        triggerAutosave();

        currentStep = step;
        currentStepInput.value = currentStep;
    }

    // Input monitoring for Autosave
    let autosaveTimeout = null;
    form.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('input', function() {
            if (input.type !== 'file') {
                scheduleAutosave();
            }
        });
        input.addEventListener('change', function() {
            if (input.type !== 'file') {
                scheduleAutosave();
            }
        });
    });

    function scheduleAutosave() {
        showSaving();
        clearTimeout(autosaveTimeout);
        autosaveTimeout = setTimeout(triggerAutosave, 1500); // 1.5 seconds delay
    }

    function showSaving() {
        autosaveStatus.textContent = 'جاري حفظ التعديلات تلقائياً...';
        autosaveDot.style.backgroundColor = '#d97706';
    }

    function showSaved() {
        autosaveStatus.textContent = 'تم حفظ جميع التغييرات';
        autosaveDot.style.backgroundColor = '#10b981';
    }

    function triggerAutosave() {
        const formData = new FormData(form);
        formData.append('step', currentStep);

        fetch('<?= url("provider/wizard/save") ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSaved();
            } else {
                autosaveStatus.textContent = 'خطأ في الحفظ التلقائي!';
                autosaveDot.style.backgroundColor = '#dc2626';
            }
        })
        .catch(err => {
            autosaveStatus.textContent = 'خطأ في الاتصال بالشبكة!';
            autosaveDot.style.backgroundColor = '#dc2626';
        });
    }

    // Logo immediate file upload
    const logoInput = document.getElementById('logo-file-input');
    logoInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            showSaving();
            const formData = new FormData();
            formData.append('step', 4);
            formData.append('logo', this.files[0]);
            formData.append('csrf_token', '<?= csrf_token() ?>');

            fetch('<?= url("provider/wizard/save") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSaved();
                    // Reload panel page content/previews
                    window.location.reload();
                }
            });
        }
    });

    // Logo delete button
    const deleteLogoBtn = document.getElementById('btn-delete-logo');
    if (deleteLogoBtn) {
        deleteLogoBtn.addEventListener('click', function() {
            if (confirm('هل أنت متأكد من حذف شعار ملفك الشخصي؟')) {
                showSaving();
                const formData = new FormData();
                formData.append('step', 4);
                formData.append('delete_logo', '1');
                formData.append('csrf_token', '<?= csrf_token() ?>');

                fetch('<?= url("provider/wizard/save") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showSaved();
                        window.location.reload();
                    }
                });
            }
        });
    }

    // Gallery upload immediate action
    const galleryInput = document.getElementById('gallery-file-input');
    galleryInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            showSaving();
            const formData = new FormData();
            formData.append('step', 4);
            formData.append('csrf_token', '<?= csrf_token() ?>');
            for (let i = 0; i < this.files.length; i++) {
                formData.append('gallery_photos[]', this.files[i]);
            }

            fetch('<?= url("provider/wizard/save") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSaved();
                    window.location.reload();
                }
            });
        }
    });

    // Gallery delete photo buttons
    document.querySelectorAll('.btn-delete-photo').forEach(btn => {
        btn.addEventListener('click', function() {
            const path = this.getAttribute('data-path');
            if (confirm('هل أنت متأكد من إزالة هذه الصورة من المعرض؟')) {
                showSaving();
                const formData = new FormData();
                formData.append('step', 4);
                formData.append('delete_photo_path', path);
                formData.append('csrf_token', '<?= csrf_token() ?>');

                fetch('<?= url("provider/wizard/save") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showSaved();
                        this.closest('.gallery-photo-card').remove();
                    }
                });
            }
        });
    });
});
</script>
