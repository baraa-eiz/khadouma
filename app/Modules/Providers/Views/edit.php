<?php
/**
 * edit.php
 * Providers Module Admin Edit View
 */
?>

<div class="section-header">
    <h1 class="section-title">تعديل بيانات مزود الخدمة</h1>
    <a href="<?= url('admin/providers') ?>" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2"/></svg>
        العودة للقائمة
    </a>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <form action="<?= url('admin/providers/' . $item['id']) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            
            <!-- Complete Full Name (Arabic) -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="display_name_ar">الاسم الكامل (بالعربية) <span style="color: var(--danger);">*</span></label>
                <input type="text" id="display_name_ar" name="display_name_ar" value="<?= e($old['display_name_ar'] ?? '') ?>" class="form-control" placeholder="مثال: أبو أحمد للسباكة المنزلية" required>
                <span class="form-hint">الاسم الكامل لمزود الخدمة (يجب أن يحتوي على حروف عربية).</span>
                <?php if (isset($errors['display_name_ar'])): ?>
                    <?php foreach ($errors['display_name_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Slug -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="slug">الرابط اللطيف (Slug)</label>
                <input type="text" id="slug" name="slug" value="<?= e($old['slug'] ?? '') ?>" class="form-control" style="font-family: monospace;" placeholder="مثال: abu-ahmad-plumbing">
                <span class="form-hint">يترك فارغاً لتوليده تلقائياً من الاسم (أحرف إنجليزية صغيرة وشرطات).</span>
                <?php if (isset($errors['slug'])): ?>
                    <?php foreach ($errors['slug'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Business Type -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="business_type">نوع العمل</label>
                <select id="business_type" name="business_type" class="form-control">
                    <option value="individual" <?= ($old['business_type'] ?? '') === 'individual' ? 'selected' : '' ?>>فرد / حرفي مستقل</option>
                    <option value="company" <?= ($old['business_type'] ?? '') === 'company' ? 'selected' : '' ?>>شركة / مؤسسة خدمية</option>
                </select>
            </div>

            <!-- Sort Weight -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="sort_weight">وزن الترتيب</label>
                <input type="number" id="sort_weight" name="sort_weight" value="<?= e($old['sort_weight'] ?? '0') ?>" class="form-control" min="0" placeholder="0">
                <span class="form-hint">قيمة فرز رقمية (الرقم الأكبر يظهر في المقدمة بالنتائج).</span>
                <?php if (isset($errors['sort_weight'])): ?>
                    <?php foreach ($errors['sort_weight'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Phone Number -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="phone">رقم الهاتف للاتصال <span style="color: var(--danger);">*</span></label>
                <input type="text" id="phone" name="phone" value="<?= e($old['phone'] ?? '') ?>" class="form-control" style="direction: ltr; text-align: left;" placeholder="09xxxxxxxx أو +963xxxxxxxx" required>
                <span class="form-hint">يجب أن يكون فريداً وغير مستخدم من قبل.</span>
                <?php if (isset($errors['phone'])): ?>
                    <?php foreach ($errors['phone'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- WhatsApp Number -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="whatsapp">رقم الواتساب (اختياري)</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= e($old['whatsapp'] ?? '') ?>" class="form-control" style="direction: ltr; text-align: left;" placeholder="09xxxxxxxx أو +963xxxxxxxx">
                <span class="form-hint">يترك فارغاً إذا كان يطابق رقم الهاتف الأساسي.</span>
                <?php if (isset($errors['whatsapp'])): ?>
                    <?php foreach ($errors['whatsapp'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Primary Service -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="primary_service_id">الخدمة الأساسية <span style="color: var(--danger);">*</span></label>
                <select id="primary_service_id" name="primary_service_id" class="form-control" required>
                    <option value="">-- اختر الخدمة الأساسية --</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?>" <?= (string)($old['primary_service_id'] ?? '') === (string)$service['id'] ? 'selected' : '' ?>><?= e($service['display_name_ar']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['primary_service_id'])): ?>
                    <?php foreach ($errors['primary_service_id'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Select City -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="city_id">المدينة الرئيسية <span style="color: var(--danger);">*</span></label>
                <select id="city_id" name="city_id" class="form-control" required>
                    <option value="">-- اختر المدينة --</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= $city['id'] ?>" <?= (string)($old['city_id'] ?? '') === (string)$city['id'] ? 'selected' : '' ?>><?= e($city['display_name_ar']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['city_id'])): ?>
                    <?php foreach ($errors['city_id'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Covered Areas Checkboxes (Renders dynamically based on selected city) -->
            <div class="form-group" style="grid-column: 1 / 3; display: none;" id="areas-group">
                <label class="form-label">مناطق التغطية والعمل بالمدينة</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); padding: 12px; border-radius: var(--radius-sm); background-color: var(--bg-hover);">
                    <?php foreach ($areas as $area): ?>
                        <label class="area-checkbox-item" data-city-id="<?= $area['city_id'] ?>" style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="areas[]" value="<?= $area['id'] ?>" <?= in_array($area['id'], $old['areas'] ?? []) ? 'checked' : '' ?>>
                            <span><?= e($area['display_name_ar']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <span class="form-hint">حدد كافة الأحياء أو البلدات والمناطق التي يقدم مزود الخدمة عمله فيها.</span>
            </div>

            <!-- Secondary Services Checkboxes -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label">خدمات إضافية ومتقاطعة يقدمها</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; border: 1px solid var(--border-color); padding: 12px; border-radius: var(--radius-sm); background-color: var(--bg-hover);">
                    <?php foreach ($services as $service): ?>
                        <label class="service-checkbox-item" style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;" id="sec-service-label-<?= $service['id'] ?>">
                            <input type="checkbox" name="services[]" value="<?= $service['id'] ?>" <?= in_array($service['id'], $old['services'] ?? []) ? 'checked' : '' ?>>
                            <span><?= e($service['display_name_ar']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Short Description (Arabic) -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="short_description_ar">الوصف القصير (المختصر)</label>
                <input type="text" id="short_description_ar" name="short_description_ar" value="<?= e($old['short_description_ar'] ?? '') ?>" class="form-control" placeholder="مثال: فني سباكة خبير بصيانة وتركيب الأدوات الصحية وشبكات الصرف الصحي">
                <span class="form-hint">يظهر في كروت نتائج البحث (يفضل ألا يتجاوز 250 حرفاً).</span>
                <?php if (isset($errors['short_description_ar'])): ?>
                    <?php foreach ($errors['short_description_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Detailed Description (Arabic) -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="description_ar">نبذة تفصيلية ومعلومات عن الخبرات</label>
                <textarea id="description_ar" name="description_ar" class="form-control" rows="4" placeholder="اكتب تفاصيل كاملة عن الخبرات، أوقات العمل، الخدمات الدقيقة وسنوات الضمان الممنوحة..."><?= e($old['description_ar'] ?? '') ?></textarea>
                <?php if (isset($errors['description_ar'])): ?>
                    <?php foreach ($errors['description_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Experience & Price -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="years_experience">سنوات الخبرة</label>
                <input type="number" id="years_experience" name="years_experience" value="<?= e($old['years_experience'] ?? '0') ?>" class="form-control" min="0" placeholder="0">
                <?php if (isset($errors['years_experience'])): ?>
                    <?php foreach ($errors['years_experience'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group" style="grid-column: 2 / 3; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <div>
                    <label class="form-label" for="starting_price">سعر البدء المبدئي</label>
                    <input type="number" id="starting_price" name="starting_price" value="<?= e($old['starting_price'] ?? '') ?>" class="form-control" min="0" step="any" placeholder="مثال: 25000">
                </div>
                <div>
                    <label class="form-label" for="price_unit">وحدة التسعير</label>
                    <select id="price_unit" name="price_unit" class="form-control">
                        <option value="hour" <?= ($old['price_unit'] ?? '') === 'hour' ? 'selected' : '' ?>>بالساعة</option>
                        <option value="job" <?= ($old['price_unit'] ?? '') === 'job' ? 'selected' : '' ?>>بالخدمة / بالزيارة</option>
                        <option value="day" <?= ($old['price_unit'] ?? '') === 'day' ? 'selected' : '' ?>>باليوم</option>
                    </select>
                </div>
            </div>

            <!-- Image Upload Section -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="logo">شعار مزود الخدمة (Logo / Avatar)</label>
                <?php if (!empty($item['logo'])): ?>
                    <div style="margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        <img src="<?= url($item['logo']) ?>" alt="الشعار الحالي" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                        <span style="font-size: 12px; color: var(--text-muted);">الشعار الحالي نشط. ارفع ملف جديد لاستبداله.</span>
                    </div>
                <?php endif; ?>
                <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                <?php if (isset($errors['logo'])): ?>
                    <?php foreach ($errors['logo'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="work_photos">صور معرض الأعمال (معرض الصور)</label>
                <?php if (!empty($item['work_photos'])): ?>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
                        <?php foreach ($item['work_photos'] as $photo): ?>
                            <img src="<?= url($photo) ?>" style="width: 38px; height: 38px; border-radius: 4px; object-fit: cover; border: 1px solid var(--border-color);" alt="معرض الأعمال">
                        <?php endforeach; ?>
                        <span style="font-size: 11px; color: var(--text-muted); align-self: center; margin-right: 6px;">سيتم استبدال معرض الصور الحالي في حال اختيار ملفات جديدة.</span>
                    </div>
                <?php endif; ?>
                <input type="file" id="work_photos" name="work_photos[]" class="form-control" accept="image/*" multiple>
                <?php if (isset($errors['work_photos'])): ?>
                    <?php foreach ($errors['work_photos'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Workflow & Badges Status -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="status">حالة القبول والتحقق</label>
                <select id="status" name="status" class="form-control">
                    <option value="pending" <?= ($old['status'] ?? '') === 'pending' ? 'selected' : '' ?>>قيد المراجعة</option>
                    <option value="approved" <?= ($old['status'] ?? '') === 'approved' ? 'selected' : '' ?>>مقبول ومعتمد (نشط)</option>
                    <option value="rejected" <?= ($old['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                    <option value="suspended" <?= ($old['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>معلق مؤقتاً</option>
                </select>
            </div>

            <!-- Checkboxes Row -->
            <div class="form-group" style="grid-column: 2 / 3; display: flex; flex-direction: column; gap: 12px; justify-content: center; padding-top: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="verified" value="1" <?= ($old['verified'] ?? false) ? 'checked' : '' ?>>
                    <span style="font-weight: bold;">مزود خدمة موثق بشارة زرقاء (Verified)</span>
                </label>
            </div>

            <!-- SEO Block Heading -->
            <div style="grid-column: 1 / 3; border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: 8px;">
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">إعدادات السيو وصفحة محركات البحث (SEO)</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">تعديل ميتا العناوين والأوصاف لرفع أرشفة الملف الشخصي بمحركات البحث.</p>
            </div>

            <!-- Meta Title -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="meta_title_ar">عنوان الميتا (Meta Title)</label>
                <input type="text" id="meta_title_ar" name="meta_title_ar" value="<?= e($old['meta_title_ar'] ?? '') ?>" class="form-control" placeholder="مثال: أفضل كهربائي منازل في حلب | أبو سامر لأعمال الكهرباء">
                <span class="form-hint">يفضل ألا يتجاوز 60 حرفاً. يترك فارغاً لاستخدام الاسم الكامل والمدينة.</span>
                <?php if (isset($errors['meta_title_ar'])): ?>
                    <?php foreach ($errors['meta_title_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Meta Description -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="meta_description_ar">وصف الميتا (Meta Description)</label>
                <textarea id="meta_description_ar" name="meta_description_ar" class="form-control" rows="3" placeholder="مثال: هل تبحث عن كهربائي منازل في حلب؟ تواصل مع أبو سامر مباشرة لأعمال التأسيس والصيانة والتمديدات المنزلية السريعة والآمنة..."><?= e($old['meta_description_ar'] ?? '') ?></textarea>
                <span class="form-hint">يفضل ألا يتجاوز 160 حرفاً لجلب نقرات بحث أفضل.</span>
                <?php if (isset($errors['meta_description_ar'])): ?>
                    <?php foreach ($errors['meta_description_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Is Active Toggle Switch -->
            <div class="form-group" style="grid-column: 1 / 3; display: flex; flex-direction: row; align-items: center; justify-content: space-between; background-color: var(--bg-hover); padding: 12px 16px; border-radius: var(--radius-sm); margin-top: 8px;">
                <div>
                    <label class="form-label" style="margin-bottom: 2px;">تفعيل ونشر الحساب</label>
                    <span style="font-size: 12px; color: var(--text-muted);">تحديد ما إذا كان الملف يظهر بمحركات البحث والظهور العام للزوار بالموقع.</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_active" value="1" class="toggle-input" <?= (isset($old['is_active']) && !$old['is_active']) ? '' : 'checked' ?>>
                    <div class="toggle-track">
                        <div class="toggle-thumb"></div>
                    </div>
                </label>
            </div>

        </div>

        <div class="card-footer">
            <a href="<?= url('admin/providers') ?>" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
        </div>
    </form>
</div>

<!-- CLIENT SIDE CITY-AREA INTERACTION JS -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const citySelect = document.getElementById('city_id');
        const primaryServiceSelect = document.getElementById('primary_service_id');
        const areasGroup = document.getElementById('areas-group');
        const areaCheckboxes = document.querySelectorAll('.area-checkbox-item');

        function filterAreasByCity() {
            const cityId = citySelect.value;
            if (!cityId) {
                areasGroup.style.display = 'none';
                return;
            }

            let matchesCount = 0;
            areaCheckboxes.forEach(item => {
                const itemCityId = item.getAttribute('data-city-id');
                if (itemCityId === cityId) {
                    item.style.display = 'flex';
                    matchesCount++;
                } else {
                    item.style.display = 'none';
                    // Do NOT uncheck on initial page load if it matches existing data,
                    // but we do want to prevent cross-city checks from posting.
                    // The backend handles saving anyway, but let's be safe.
                }
            });

            if (matchesCount > 0) {
                areasGroup.style.display = 'block';
            } else {
                areasGroup.style.display = 'none';
            }
        }

        function filterSecondaryServices() {
            const primaryId = primaryServiceSelect.value;
            document.querySelectorAll('.service-checkbox-item').forEach(item => {
                const checkbox = item.querySelector('input');
                const serviceId = checkbox.value;
                if (serviceId === primaryId) {
                    item.style.opacity = '0.4';
                    item.style.pointerEvents = 'none';
                    checkbox.checked = false;
                } else {
                    item.style.opacity = '1';
                    item.style.pointerEvents = 'auto';
                }
            });
        }

        // Bind events
        citySelect.addEventListener('change', () => {
            // Uncheck areas if changing city
            document.querySelectorAll('.area-checkbox-item input').forEach(cb => cb.checked = false);
            filterAreasByCity();
        });
        primaryServiceSelect.addEventListener('change', filterSecondaryServices);

        // Run on initial load without wiping initial checks
        const cityId = citySelect.value;
        if (cityId) {
            let matchesCount = 0;
            areaCheckboxes.forEach(item => {
                const itemCityId = item.getAttribute('data-city-id');
                if (itemCityId === cityId) {
                    item.style.display = 'flex';
                    matchesCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            if (matchesCount > 0) areasGroup.style.display = 'block';
        }
        filterSecondaryServices();
    });
</script>
