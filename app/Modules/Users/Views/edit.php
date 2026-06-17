<?php
/**
 * edit.php
 * Users Module Admin Edit View
 */
?>

<div class="section-header">
    <h1 class="section-title">تعديل حساب المستخدم: <?= e($item->display_name) ?></h1>
    <a href="<?= url('admin/users') ?>" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2"/></svg>
        العودة للقائمة
    </a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form action="<?= url('admin/users/' . $item->id) ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            
            <!-- Display Name -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="display_name">اسم المستخدم الكامل <span style="color: var(--danger);">*</span></label>
                <input type="text" id="display_name" name="display_name" value="<?= e($old['display_name'] ?? $item->display_name) ?>" class="form-control" placeholder="مثال: أحمد المحمد" required>
                <?php if (isset($errors['display_name'])): ?>
                    <?php foreach ($errors['display_name'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?= e($old['email'] ?? $item->email) ?>" class="form-control" placeholder="example@domain.com">
                <?php if (isset($errors['email'])): ?>
                    <?php foreach ($errors['email'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Phone -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="phone">رقم الهاتف</label>
                <input type="text" id="phone" name="phone" value="<?= e($old['phone'] ?? $item->phone) ?>" class="form-control" placeholder="09xxxxxxxx">
                <?php if (isset($errors['phone'])): ?>
                    <?php foreach ($errors['phone'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- City -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="city_id">المدينة</label>
                <select id="city_id" name="city_id" class="form-control">
                    <option value="">-- اختر المدينة --</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= $city['id'] ?>" <?= ($old['city_id'] ?? $item->city_id) == $city['id'] ? 'selected' : '' ?>><?= e($city['display_name_ar']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['city_id'])): ?>
                    <?php foreach ($errors['city_id'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Area -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="area_id">المنطقة</label>
                <select id="area_id" name="area_id" class="form-control">
                    <option value="">-- اختر المنطقة --</option>
                    <?php foreach ($areas as $area): ?>
                        <option value="<?= $area['id'] ?>" data-city="<?= $area['city_id'] ?>" <?= ($old['area_id'] ?? $item->area_id) == $area['id'] ? 'selected' : '' ?>><?= e($area['display_name_ar']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['area_id'])): ?>
                    <?php foreach ($errors['area_id'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Default Address -->
            <div class="form-group" style="grid-column: 1 / 3;">
                <label class="form-label" for="default_address">العنوان بالتفصيل</label>
                <textarea id="default_address" name="default_address" rows="3" class="form-control" placeholder="الحي، الشارع، البناء..."><?= e($old['default_address'] ?? $item->default_address) ?></textarea>
                <?php if (isset($errors['default_address'])): ?>
                    <?php foreach ($errors['default_address'] as $err): ?>
                        <span class="form-error"><?= e($err) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Preferred Contact Method -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="preferred_contact_method">طريقة التواصل المفضلة</label>
                <select id="preferred_contact_method" name="preferred_contact_method" class="form-control">
                    <option value="phone" <?= ($old['preferred_contact_method'] ?? $item->preferred_contact_method) === 'phone' ? 'selected' : '' ?>>الهاتف</option>
                    <option value="email" <?= ($old['preferred_contact_method'] ?? $item->preferred_contact_method) === 'email' ? 'selected' : '' ?>>البريد الإلكتروني</option>
                </select>
            </div>

            <!-- Preferred Language -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="preferred_language">اللغة المفضلة</label>
                <select id="preferred_language" name="preferred_language" class="form-control">
                    <option value="ar" <?= ($old['preferred_language'] ?? $item->preferred_language) === 'ar' ? 'selected' : '' ?>>العربية</option>
                    <option value="en" <?= ($old['preferred_language'] ?? $item->preferred_language) === 'en' ? 'selected' : '' ?>>English</option>
                </select>
            </div>

            <!-- Timezone -->
            <div class="form-group" style="grid-column: 1 / 2;">
                <label class="form-label" for="timezone">التوقيت المحلي</label>
                <select id="timezone" name="timezone" class="form-control">
                    <option value="Asia/Damascus" <?= ($old['timezone'] ?? $item->timezone) === 'Asia/Damascus' ? 'selected' : '' ?>>دمشق (UTC+3)</option>
                    <option value="Asia/Riyadh" <?= ($old['timezone'] ?? $item->timezone) === 'Asia/Riyadh' ? 'selected' : '' ?>>الرياض (UTC+3)</option>
                    <option value="UTC" <?= ($old['timezone'] ?? $item->timezone) === 'UTC' ? 'selected' : '' ?>>التوقيت العالمي (UTC)</option>
                </select>
            </div>

            <!-- Account Status -->
            <div class="form-group" style="grid-column: 2 / 3;">
                <label class="form-label" for="status">حالة الحساب</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" <?= ($old['status'] ?? $item->status) === 'active' ? 'selected' : '' ?>>نشط</option>
                    <option value="suspended" <?= ($old['status'] ?? $item->status) === 'suspended' ? 'selected' : '' ?>>موقوف</option>
                </select>
            </div>

            <!-- Marketing Opt In -->
            <div class="form-group" style="grid-column: 1 / 3; display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                <input type="checkbox" id="marketing_opt_in" name="marketing_opt_in" value="1" <?= ($old['marketing_opt_in'] ?? $item->marketing_opt_in) ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                <label for="marketing_opt_in" style="margin: 0; font-weight: 500; cursor: pointer;">الموافقة على تلقي الرسائل الترويجية والعروض</label>
            </div>

        </div>

        <div class="card-footer">
            <a href="<?= url('admin/users') ?>" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const citySelect = document.getElementById('city_id');
    const areaSelect = document.getElementById('area_id');
    const areaOptions = Array.from(areaSelect.options).slice(1);

    function filterAreas() {
        const selectedCityId = citySelect.value;
        areaSelect.innerHTML = '<option value="">-- اختر المنطقة --</option>';
        const filtered = areaOptions.filter(opt => opt.getAttribute('data-city') === selectedCityId);
        filtered.forEach(opt => areaSelect.appendChild(opt));
        
        const prevValue = "<?= $item->area_id ?>";
        if (filtered.some(opt => opt.value === prevValue)) {
            areaSelect.value = prevValue;
        }
    }

    citySelect.addEventListener('change', filterAreas);
    if (citySelect.value) {
        filterAreas();
    }
});
</script>
