<?php
/**
 * edit.php
 * Services Module Admin Edit View
 */
?>

<div class="section-header">
    <h1 class="section-title">تعديل الخدمة: <?= e($item['display_name_ar']) ?></h1>
    <a href="<?= url('admin/services') ?>" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2"/></svg>
        العودة للقائمة
    </a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form action="<?= url('admin/services/' . $item['id']) ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            
            <!-- Key Identifier (uniqueness check will exclude current ID) -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="key">الرمز التعريفي (Key) <span style="color: var(--danger);">*</span></label>
                <input type="text" id="key" name="key" value="<?= e($old['key'] ?? '') ?>" class="form-control" style="font-family: monospace;" placeholder="e.g. cleaning" required>
                <span class="form-hint">رمز تعريفي فريد بالأحرف الإنجليزية الصغيرة والأرقام والشرطة.</span>
                <?php if (isset($errors['key'])): ?>
                    <?php foreach ($errors['key'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Slug -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="slug">الرابط اللطيف (Slug)</label>
                <input type="text" id="slug" name="slug" value="<?= e($old['slug'] ?? '') ?>" class="form-control" style="font-family: monospace;" placeholder="e.g. house-cleaning">
                <span class="form-hint">اتركه فارغاً للتوليد التلقائي من الاسم المختصر. أحرف صغيرة وشرطات فقط.</span>
                <?php if (isset($errors['slug'])): ?>
                    <?php foreach ($errors['slug'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Full Display Name (Arabic) -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="display_name_ar">الاسم الكامل (بالعربية) <span style="color: var(--danger);">*</span></label>
                <input type="text" id="display_name_ar" name="display_name_ar" value="<?= e($old['display_name_ar'] ?? '') ?>" class="form-control" placeholder="مثال: خدمات التنظيف والتعقيم للمنازل" required>
                <span class="form-hint">يظهر كعنوان رئيسي لصفحة الخدمة ويجب أن يحتوي على حروف عربية.</span>
                <?php if (isset($errors['display_name_ar'])): ?>
                    <?php foreach ($errors['display_name_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Short Name (Arabic) -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="short_name_ar">الاسم المختصر (بالعربية) <span style="color: var(--danger);">*</span></label>
                <input type="text" id="short_name_ar" name="short_name_ar" value="<?= e($old['short_name_ar'] ?? '') ?>" class="form-control" placeholder="مثال: تنظيف" required>
                <span class="form-hint">يستخدم في القوائم المختصرة والروابط السريعة (حروف عربية).</span>
                <?php if (isset($errors['short_name_ar'])): ?>
                    <?php foreach ($errors['short_name_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Description (Arabic) -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="description_ar">الوصف التفصيلي (بالعربية)</label>
                <textarea id="description_ar" name="description_ar" class="form-control" rows="4" placeholder="اكتب وصفاً مفصلاً للخدمة والمزايا المقدمة..."><?= e($old['description_ar'] ?? '') ?></textarea>
                <?php if (isset($errors['description_ar'])): ?>
                    <?php foreach ($errors['description_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Icon Dropdown -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="icon">أيقونة الخدمة</label>
                <select id="icon" name="icon" class="form-control">
                    <option value="">-- اختر أيقونة --</option>
                    <option value="icon-cleaning" <?= ($old['icon'] ?? '') === 'icon-cleaning' ? 'selected' : '' ?>>🧹 تنظيف (icon-cleaning)</option>
                    <option value="icon-plumbing" <?= ($old['icon'] ?? '') === 'icon-plumbing' ? 'selected' : '' ?>>🚰 سباكة (icon-plumbing)</option>
                    <option value="icon-electricity" <?= ($old['icon'] ?? '') === 'icon-electricity' ? 'selected' : '' ?>>⚡ كهرباء (icon-electricity)</option>
                    <option value="icon-painting" <?= ($old['icon'] ?? '') === 'icon-painting' ? 'selected' : '' ?>>🎨 دهان (icon-painting)</option>
                    <option value="icon-moving" <?= ($old['icon'] ?? '') === 'icon-moving' ? 'selected' : '' ?>>📦 نقل أثاث (icon-moving)</option>
                </select>
                <?php if (isset($errors['icon'])): ?>
                    <?php foreach ($errors['icon'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Sort Order -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="sort_order">ترتيب الفرز</label>
                <input type="number" id="sort_order" name="sort_order" value="<?= e($old['sort_order'] ?? '0') ?>" class="form-control" min="0" placeholder="0">
                <span class="form-hint">يحدد أولوية ظهور الخدمة في القوائم الرئيسية (الرقم الأصغر يظهر أولاً).</span>
                <?php if (isset($errors['sort_order'])): ?>
                    <?php foreach ($errors['sort_order'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- SEO Block Heading -->
            <div style="grid-column: 1 / 3; border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: 8px;">
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">إعدادات السيو والبحث (SEO)</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">تحسين ظهور صفحة الخدمة في محركات البحث مثل جوجل.</p>
            </div>

            <!-- Meta Title -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="meta_title_ar">عنوان الميتا (Meta Title)</label>
                <input type="text" id="meta_title_ar" name="meta_title_ar" value="<?= e($old['meta_title_ar'] ?? '') ?>" class="form-control" placeholder="مثال: أفضل خدمات تنظيف المنازل في دمشق | منصة خدومة">
                <span class="form-hint">يفضل ألا يتجاوز 60 حرفاً. يترك فارغاً لاستخدام العنوان الافتراضي للموقع.</span>
                <?php if (isset($errors['meta_title_ar'])): ?>
                    <?php foreach ($errors['meta_title_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Meta Description -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="meta_description_ar">وصف الميتا (Meta Description)</label>
                <textarea id="meta_description_ar" name="meta_description_ar" class="form-control" rows="3" placeholder="مثال: تواصل مع أفضل مزودي خدمات تنظيف المنازل والتعقيم بالمواد الأصلية في دمشق مباشرة بدون عمولة..."><?= e($old['meta_description_ar'] ?? '') ?></textarea>
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
                    <label class="form-label" style="margin-bottom: 2px;">تفعيل الخدمة</label>
                    <span style="font-size: 12px; color: var(--text-muted);">تحديد ما إذا كانت الخدمة تظهر في الموقع العام للزوار.</span>
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
            <a href="<?= url('admin/services') ?>" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
        </div>
    </form>
</div>
