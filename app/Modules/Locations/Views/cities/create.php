<?php
/**
 * create.php
 * Cities Module Admin Create View
 */
?>

<div class="section-header">
    <h1 class="section-title">إضافة مدينة جديدة</h1>
    <a href="<?= url('admin/cities') ?>" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2"/></svg>
        العودة للقائمة
    </a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form action="<?= url('admin/cities') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            
            <!-- Key -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="key">الرمز التعريفي (Key) <span style="color: var(--danger);">*</span></label>
                <input type="text" id="key" name="key" value="<?= e($old['key'] ?? '') ?>" class="form-control" style="font-family: monospace;" placeholder="e.g. damascus" required>
                <span class="form-hint">رمز تعريفي فريد بالأحرف الصغيرة والأرقام والشرطة.</span>
                <?php if (isset($errors['key'])): ?>
                    <?php foreach ($errors['key'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Slug -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="slug">الرابط اللطيف (Slug)</label>
                <input type="text" id="slug" name="slug" value="<?= e($old['slug'] ?? '') ?>" class="form-control" style="font-family: monospace;" placeholder="e.g. damascus">
                <span class="form-hint">اترك الحقل فارغاً ليتم توليده تلقائياً من الرمز التعريفي.</span>
                <?php if (isset($errors['slug'])): ?>
                    <?php foreach ($errors['slug'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Display Name Arabic -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="display_name_ar">اسم المدينة الكامل (بالعربية) <span style="color: var(--danger);">*</span></label>
                <input type="text" id="display_name_ar" name="display_name_ar" value="<?= e($old['display_name_ar'] ?? '') ?>" class="form-control" placeholder="مثال: دمشق" required>
                <span class="form-hint">يجب أن يحتوي على حروف عربية.</span>
                <?php if (isset($errors['display_name_ar'])): ?>
                    <?php foreach ($errors['display_name_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Display Name English -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="display_name_en">الاسم بالإنجليزية</label>
                <input type="text" id="display_name_en" name="display_name_en" value="<?= e($old['display_name_en'] ?? '') ?>" class="form-control" placeholder="مثال: Damascus">
                <?php if (isset($errors['display_name_en'])): ?>
                    <?php foreach ($errors['display_name_en'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Sort Order -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="sort_order">ترتيب الفرز</label>
                <input type="number" id="sort_order" name="sort_order" value="<?= e($old['sort_order'] ?? '0') ?>" class="form-control" min="0" placeholder="0">
                <span class="form-hint">الرقم الأصغر يظهر أولاً.</span>
                <?php if (isset($errors['sort_order'])): ?>
                    <?php foreach ($errors['sort_order'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- SEO Settings -->
            <div style="grid-column: 1 / 3; border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: 8px;">
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">إعدادات السيو والبحث (SEO)</h3>
            </div>

            <!-- Meta Title -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="meta_title_ar">عنوان الميتا (Meta Title)</label>
                <input type="text" id="meta_title_ar" name="meta_title_ar" value="<?= e($old['meta_title_ar'] ?? '') ?>" class="form-control" placeholder="مثال: أفضل مقدمي الخدمات المنزلية في دمشق | منصة خدومة">
                <?php if (isset($errors['meta_title_ar'])): ?>
                    <?php foreach ($errors['meta_title_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Meta Description -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="meta_description_ar">وصف الميتا (Meta Description)</label>
                <textarea id="meta_description_ar" name="meta_description_ar" class="form-control" rows="3" placeholder="مثال: تصفح مقدمي الخدمات والمهنيين الأقرب إليك في دمشق..."><?= e($old['meta_description_ar'] ?? '') ?></textarea>
                <?php if (isset($errors['meta_description_ar'])): ?>
                    <?php foreach ($errors['meta_description_ar'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Is Active Toggle Switch -->
            <div class="form-group" style="grid-column: 1 / 3; display: flex; flex-direction: row; align-items: center; justify-content: space-between; background-color: var(--bg-hover); padding: 12px 16px; border-radius: var(--radius-sm); margin-top: 8px;">
                <div>
                    <label class="form-label" style="margin-bottom: 2px;">تفعيل المدينة</label>
                    <span style="font-size: 12px; color: var(--text-muted);">تحديد ما إذا كانت المدينة تظهر في الواجهة العامة للموقع.</span>
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
            <a href="<?= url('admin/cities') ?>" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ وإضافة المدينة</button>
        </div>
    </form>
</div>
