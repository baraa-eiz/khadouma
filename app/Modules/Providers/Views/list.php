<?php
/**
 * list.php
 * Providers Module Admin List View
 */
use App\Core\Config;
?>

<div class="section-header">
    <h1 class="section-title">إدارة مزودي الخدمات</h1>
    
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <!-- Export Button with current filters -->
        <a href="<?= url('admin/providers/export') . '?' . http_build_query($_GET) ?>" class="btn btn-secondary" title="تصدير النتائج الحالية كملف CSV" style="padding: 0.625rem 1rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            تصدير CSV
        </a>
        
        <!-- Import Form -->
        <form method="POST" action="<?= url('admin/providers/import') ?>" enctype="multipart/form-data" style="display: inline-flex; align-items: center; gap: 6px; margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <label class="btn btn-secondary" style="cursor: pointer; margin: 0; padding: 0.625rem 1rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                استيراد CSV
                <input type="file" name="csv_file" accept=".csv" style="display: none;" onchange="this.form.submit()">
            </label>
        </form>

        <a href="<?= url('admin/providers/create') ?>" class="btn btn-primary" style="padding: 0.625rem 1.25rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            إضافة مزود خدمة جديد
        </a>
    </div>
</div>

<!-- FILTERS TOOLBAR -->
<form method="GET" action="<?= url('admin/providers') ?>" class="toolbar" style="gap: 12px; align-items: flex-end; flex-wrap: wrap;">
    <div style="display: flex; flex-wrap: wrap; gap: 12px; flex-grow: 1; width: 100%;">
        <!-- Keyword Search -->
        <div class="form-group" style="margin-bottom: 0; min-width: 200px; flex-grow: 1;">
            <label class="form-label" for="keyword">بحث ذكي بالاسم، الهاتف، أو الوصف</label>
            <div class="search-bar" style="max-width: 100%;">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="keyword" name="keyword" value="<?= e($keyword ?? '') ?>" placeholder="اكتب للبحث..." class="form-control" style="padding-right: 36px; padding-left: 12px;">
            </div>
        </div>

        <!-- City Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 130px;">
            <label class="form-label" for="city_id">المدينة</label>
            <select name="city_id" id="city_id" class="form-control">
                <option value="">كل المدن</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?= $city['id'] ?>" <?= (string)$city_id === (string)$city['id'] ? 'selected' : '' ?>><?= e($city['display_name_ar']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Service Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 130px;">
            <label class="form-label" for="service_id">الخدمة الأساسية</label>
            <select name="service_id" id="service_id" class="form-control">
                <option value="">كل الخدمات</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id'] ?>" <?= (string)$service_id === (string)$service['id'] ? 'selected' : '' ?>><?= e($service['display_name_ar']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Workflow Status Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 110px;">
            <label class="form-label" for="status">حالة القبول</label>
            <select name="status" id="status" class="form-control">
                <option value="">الكل</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>قيد المراجعة</option>
                <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>مقبول</option>
                <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>معلق</option>
            </select>
        </div>

        <!-- Active Status Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 100px;">
            <label class="form-label" for="is_active">الظهور العام</label>
            <select name="is_active" id="is_active" class="form-control">
                <option value="">الكل</option>
                <option value="1" <?= $is_active === '1' ? 'selected' : '' ?>>منشور</option>
                <option value="0" <?= $is_active === '0' ? 'selected' : '' ?>>مخفي</option>
            </select>
        </div>

        <!-- Deletion Status Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 100px;">
            <label class="form-label" for="is_deleted">الأرشيف</label>
            <select name="is_deleted" id="is_deleted" class="form-control">
                <option value="0" <?= $is_deleted === '0' || $is_deleted === null ? 'selected' : '' ?>>الحاليين</option>
                <option value="1" <?= $is_deleted === '1' ? 'selected' : '' ?>>المحذوفين مؤقتاً</option>
            </select>
        </div>
    </div>

    <!-- Collapsible Advanced Filters -->
    <div id="advanced-filters" style="display: <?= (!empty($rating_min) || !empty($rating_max) || !empty($experience_min) || !empty($experience_max) || !empty($business_type) || ($verified !== null && $verified !== '') || ($phone_verified !== null && $phone_verified !== '') || ($identity_verified !== null && $identity_verified !== '') || ($is_featured !== null && $is_featured !== '') || !empty($completion_min) || !empty($completion_max)) ? 'flex' : 'none' ?>; flex-wrap: wrap; gap: 12px; margin-top: 12px; border-top: 1px dashed var(--border-color); padding-top: 12px; width: 100%;">
        <!-- Rating Min/Max -->
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
            <label class="form-label" for="rating_min">الحد الأدنى للتقييم</label>
            <input type="number" step="0.1" min="0" max="5" id="rating_min" name="rating_min" value="<?= e($rating_min ?? '') ?>" class="form-control" placeholder="0.0">
        </div>
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
            <label class="form-label" for="rating_max">الحد الأقصى للتقييم</label>
            <input type="number" step="0.1" min="0" max="5" id="rating_max" name="rating_max" value="<?= e($rating_max ?? '') ?>" class="form-control" placeholder="5.0">
        </div>

        <!-- Experience Min/Max -->
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
            <label class="form-label" for="experience_min">الخبرة الأدنى (سنوات)</label>
            <input type="number" min="0" id="experience_min" name="experience_min" value="<?= e($experience_min ?? '') ?>" class="form-control" placeholder="0">
        </div>
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
            <label class="form-label" for="experience_max">الخبرة الأقصى (سنوات)</label>
            <input type="number" min="0" id="experience_max" name="experience_max" value="<?= e($experience_max ?? '') ?>" class="form-control" placeholder="30">
        </div>

        <!-- Business Type -->
        <div class="form-group" style="margin-bottom: 0; min-width: 130px;">
            <label class="form-label" for="business_type">نوع العمل</label>
            <select name="business_type" id="business_type" class="form-control">
                <option value="">الكل</option>
                <option value="individual" <?= $business_type === 'individual' ? 'selected' : '' ?>>فردي</option>
                <option value="company" <?= $business_type === 'company' ? 'selected' : '' ?>>شركة</option>
            </select>
        </div>

        <!-- Verified Flags -->
        <div class="form-group" style="margin-bottom: 0; min-width: 110px;">
            <label class="form-label" for="verified">حالة التوثيق</label>
            <select name="verified" id="verified" class="form-control">
                <option value="">الكل</option>
                <option value="1" <?= $verified === '1' ? 'selected' : '' ?>>موثق</option>
                <option value="0" <?= $verified === '0' ? 'selected' : '' ?>>غير موثق</option>
            </select>
        </div>

        <!-- Phone Verified -->
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
            <label class="form-label" for="phone_verified">توثيق الهاتف</label>
            <select name="phone_verified" id="phone_verified" class="form-control">
                <option value="">الكل</option>
                <option value="1" <?= $phone_verified === '1' ? 'selected' : '' ?>>مؤكد</option>
                <option value="0" <?= $phone_verified === '0' ? 'selected' : '' ?>>غير مؤكد</option>
            </select>
        </div>

        <!-- Identity Verified -->
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
            <label class="form-label" for="identity_verified">توثيق الهوية</label>
            <select name="identity_verified" id="identity_verified" class="form-control">
                <option value="">الكل</option>
                <option value="1" <?= $identity_verified === '1' ? 'selected' : '' ?>>موثقة</option>
                <option value="0" <?= $identity_verified === '0' ? 'selected' : '' ?>>غير موثقة</option>
            </select>
        </div>

        <!-- Is Featured -->
        <div class="form-group" style="margin-bottom: 0; min-width: 110px;">
            <label class="form-label" for="is_featured">التميز</label>
            <select name="is_featured" id="is_featured" class="form-control">
                <option value="">الكل</option>
                <option value="1" <?= $is_featured === '1' ? 'selected' : '' ?>>مميز</option>
                <option value="0" <?= $is_featured === '0' ? 'selected' : '' ?>>عادي</option>
            </select>
        </div>

        <!-- Profile Completion Score Min/Max -->
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
            <label class="form-label" for="completion_min">صحة الملف أدنى %</label>
            <input type="number" min="0" max="100" id="completion_min" name="completion_min" value="<?= e($completion_min ?? '') ?>" class="form-control" placeholder="0%">
        </div>
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
            <label class="form-label" for="completion_max">صحة الملف أقصى %</label>
            <input type="number" min="0" max="100" id="completion_max" name="completion_max" value="<?= e($completion_max ?? '') ?>" class="form-control" placeholder="100%">
        </div>
    </div>

    <!-- Hidden Sort Fields to Preserve Sorting on Search -->
    <input type="hidden" name="sort_by" value="<?= e($sort_by) ?>">
    <input type="hidden" name="sort_dir" value="<?= e($sort_dir) ?>">

    <div style="display: flex; gap: 8px; margin-top: 12px; width: 100%; justify-content: flex-end;">
        <button type="button" class="btn btn-secondary" onclick="toggleAdvancedFilters()" style="padding: 0.625rem 1.25rem;">
            تصفية متقدمة
        </button>
        <button type="submit" class="btn btn-secondary" style="padding: 0.625rem 1.25rem; background-color: var(--primary); color: white;">
            تطبيق التصفية
        </button>
        <?php if (!empty($keyword) || !empty($city_id) || !empty($service_id) || !empty($status) || $is_active !== null || $is_deleted === '1' || !empty($rating_min) || !empty($experience_min) || $verified !== null || $is_featured !== null || !empty($completion_min)): ?>
            <a href="<?= url('admin/providers') ?>" class="btn btn-secondary" style="padding: 0.625rem 1.25rem; color: var(--danger);">
                إلغاء التصفية
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- BULK ACTIONS FORM WRAPPER -->
<form id="bulk-action-form" method="POST" action="<?= url('admin/providers/bulk') ?>" style="margin-top: 15px;">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <!-- Bulk Actions Header (visible when items are selected) -->
    <div id="bulk-actions-toolbar" class="toolbar" style="display: none; background-color: #f5f6ff; border: 1px solid #c2c8f8; justify-content: space-between; align-items: center; padding: 12px 20px; margin-bottom: 15px; border-radius: 8px;">
        <div style="font-weight: bold; color: #3b82f6; display: flex; align-items: center; gap: 8px;">
            <span id="selected-count">0</span> مزودين محددين
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <select name="action" class="form-control" style="width: auto; display: inline-block; padding: 0.5rem 1rem;">
                <option value="">اختر الإجراء الجماعي...</option>
                <option value="approve">موافقة وقبول</option>
                <option value="pending">تعليق قيد المراجعة</option>
                <option value="reject">رفض</option>
                <option value="suspend">إيقاف مؤقت</option>
                <option value="publish">نشر (تفعيل)</option>
                <option value="hide">إخفاء (تعطيل)</option>
                <option value="delete">حذف مؤقت</option>
                <option value="restore">استعادة</option>
            </select>
            <button type="button" onclick="submitBulkAction()" class="btn btn-primary" style="padding: 0.5rem 1.25rem;">تنفيذ</button>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="table-container">
        <?php if (empty($items)): ?>
            <?php 
            $empty_title = 'لا توجد نتائج مطابقة لعملية البحث';
            $empty_desc = 'لم نتمكن من العثور على أي مزودي خدمات يطابقون المعايير المدخلة.';
            $empty_action_url = url('admin/providers/create');
            $empty_action_label = 'إضافة مزود خدمة جديد';
            include Config::get('app.paths.root') . '/views/components/empty_state.php'; 
            ?>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select-all" style="cursor: pointer; transform: scale(1.15);">
                        </th>
                        <th style="width: 50px; text-align: center;">الشعار</th>
                        <th>
                            <a href="?sort_by=display_name_ar&sort_dir=<?= $sort_by === 'display_name_ar' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&rating_min=<?= e($rating_min ?? '') ?>&rating_max=<?= e($rating_max ?? '') ?>&experience_min=<?= e($experience_min ?? '') ?>&experience_max=<?= e($experience_max ?? '') ?>&business_type=<?= e($business_type ?? '') ?>&verified=<?= e($verified ?? '') ?>&phone_verified=<?= e($phone_verified ?? '') ?>&identity_verified=<?= e($identity_verified ?? '') ?>&is_featured=<?= e($is_featured ?? '') ?>&completion_min=<?= e($completion_min ?? '') ?>&completion_max=<?= e($completion_max ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                                الاسم والملف
                                <?php if ($sort_by === 'display_name_ar'): ?>
                                    <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>المدينة والخدمة</th>
                        <th>الاتصال</th>
                        <th style="text-align: center;">التوثيق والتميز</th>
                        <th style="text-align: center;">الظهور العام</th>
                        <th style="text-align: center;">حالة القبول</th>
                        <th style="width: 80px; text-align: center;">
                            <a href="?sort_by=sort_weight&sort_dir=<?= $sort_by === 'sort_weight' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&rating_min=<?= e($rating_min ?? '') ?>&rating_max=<?= e($rating_max ?? '') ?>&experience_min=<?= e($experience_min ?? '') ?>&experience_max=<?= e($experience_max ?? '') ?>&business_type=<?= e($business_type ?? '') ?>&verified=<?= e($verified ?? '') ?>&phone_verified=<?= e($phone_verified ?? '') ?>&identity_verified=<?= e($identity_verified ?? '') ?>&is_featured=<?= e($is_featured ?? '') ?>&completion_min=<?= e($completion_min ?? '') ?>&completion_max=<?= e($completion_max ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                                الترتيب
                                <?php if ($sort_by === 'sort_weight'): ?>
                                    <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th style="width: 140px; text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): 
                        $logo = $this->repo->getProviderProfileImage($item['id']);
                    ?>
                        <tr class="<?= $item['deleted_at'] ? 'deleted-row' : '' ?>">
                            <td style="text-align: center; vertical-align: middle;">
                                <input type="checkbox" name="ids[]" value="<?= $item['id'] ?>" class="select-item" style="cursor: pointer; transform: scale(1.15);">
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <?php if ($logo): ?>
                                    <img src="<?= url($logo) ?>" alt="<?= e($item['display_name_ar']) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; color: var(--text-muted);">
                                        <?= mb_substr($item['display_name_ar'], 0, 1) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: bold; font-size: 15px; color: var(--text-color);">
                                    <?= e($item['display_name_ar']) ?>
                                    <?php if ($item['business_type'] === 'company'): ?>
                                        <span class="badge badge-secondary" style="font-size: 10px; padding: 1px 4px; vertical-align: middle;">شركة</span>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size: 11px; color: var(--text-muted); display: block; font-family: monospace; margin: 2px 0;">
                                    /provider/<?= e($item['slug']) ?>
                                </span>
                                
                                <!-- Profile Completion Score -->
                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                    <div class="progress-bar-container" style="flex-grow: 1; max-width: 100px; height: 6px; background-color: #e2e8f0; border-radius: 3px; overflow: hidden;" title="صحة وملء الملف: <?= (int)($item['completion_score'] ?? 0) ?>%">
                                        <?php 
                                        $score = (int)($item['completion_score'] ?? 0);
                                        $color = '#ef4444'; // red
                                        if ($score >= 80) $color = '#10b981'; // green
                                        elseif ($score >= 50) $color = '#f59e0b'; // amber
                                        ?>
                                        <div style="width: <?= $score ?>%; height: 100%; background-color: <?= $color ?>;"></div>
                                    </div>
                                    <span style="font-size: 11px; font-weight: bold; color: var(--text-muted);"><?= $score ?>% صحة الملف</span>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: bold; font-size: 14px;"><?= e($item['city_name']) ?></div>
                                <div style="margin-top: 4px;">
                                    <span class="badge badge-secondary" style="font-size: 11px;"><?= e($item['service_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="direction: ltr; text-align: right; font-weight: bold; font-size: 13px;"><?= e($item['phone']) ?></div>
                                <?php if ($item['whatsapp']): ?>
                                    <div style="direction: ltr; text-align: right; font-size: 11px; color: #10b981; margin-top: 2px;">
                                        WhatsApp: <?= e($item['whatsapp']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                                    <?php if ($item['verified']): ?>
                                        <span class="badge badge-success" style="padding: 2px 6px; font-size: 11px; width: 80px;">موثق بالكامل</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="padding: 2px 6px; font-size: 11px; opacity: 0.6; width: 80px;">غير موثق</span>
                                    <?php endif; ?>

                                    <?php if ($item['is_featured']): ?>
                                        <span class="badge badge-warning" style="padding: 2px 6px; font-size: 11px; width: 80px; background-color: #fbbf24; color: #1e293b;">مميز ★</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <?php if ($item['is_active']): ?>
                                    <span class="badge badge-success">منشور</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">مخفي</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <?php if ($item['status'] === 'approved'): ?>
                                    <span class="badge badge-success">مقبول</span>
                                <?php elseif ($item['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">قيد المراجعة</span>
                                <?php elseif ($item['status'] === 'rejected'): ?>
                                    <span class="badge badge-danger">مرفوض</span>
                                <?php else: ?>
                                    <span class="badge badge-danger" style="opacity: 0.7;">معلق</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; vertical-align: middle; font-weight: bold;"><?= $item['sort_weight'] ?></td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div class="actions-group" style="display: flex; gap: 4px; justify-content: center;">
                                    <a href="<?= url('admin/providers/' . $item['id']) ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" title="عرض التفاصيل والسجلات">
                                        التفاصيل
                                    </a>
                                    <?php if (!$item['deleted_at']): ?>
                                        <a href="<?= url('admin/providers/' . $item['id'] . '/edit') ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; color: var(--primary);" title="تعديل">
                                            تعديل
                                        </a>
                                        <button type="button" class="btn btn-secondary delete-btn" data-id="<?= $item['id'] ?>" data-name="<?= e($item['display_name_ar']) ?>" style="padding: 4px 8px; font-size: 12px; color: var(--danger);" title="حذف مؤقت">
                                            حذف
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary restore-btn" data-id="<?= $item['id'] ?>" data-name="<?= e($item['display_name_ar']) ?>" style="padding: 4px 8px; font-size: 12px; color: var(--success);" title="استعادة">
                                            استعادة
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</form>

<!-- PAGINATION -->
<?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 6px;">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&rating_min=<?= e($rating_min ?? '') ?>&rating_max=<?= e($rating_max ?? '') ?>&experience_min=<?= e($experience_min ?? '') ?>&experience_max=<?= e($experience_max ?? '') ?>&business_type=<?= e($business_type ?? '') ?>&verified=<?= e($verified ?? '') ?>&phone_verified=<?= e($phone_verified ?? '') ?>&identity_verified=<?= e($identity_verified ?? '') ?>&is_featured=<?= e($is_featured ?? '') ?>&completion_min=<?= e($completion_min ?? '') ?>&completion_max=<?= e($completion_max ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="page-link">&laquo; السابق</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&rating_min=<?= e($rating_min ?? '') ?>&rating_max=<?= e($rating_max ?? '') ?>&experience_min=<?= e($experience_min ?? '') ?>&experience_max=<?= e($experience_max ?? '') ?>&business_type=<?= e($business_type ?? '') ?>&verified=<?= e($verified ?? '') ?>&phone_verified=<?= e($phone_verified ?? '') ?>&identity_verified=<?= e($identity_verified ?? '') ?>&is_featured=<?= e($is_featured ?? '') ?>&completion_min=<?= e($completion_min ?? '') ?>&completion_max=<?= e($completion_max ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&rating_min=<?= e($rating_min ?? '') ?>&rating_max=<?= e($rating_max ?? '') ?>&experience_min=<?= e($experience_min ?? '') ?>&experience_max=<?= e($experience_max ?? '') ?>&business_type=<?= e($business_type ?? '') ?>&verified=<?= e($verified ?? '') ?>&phone_verified=<?= e($phone_verified ?? '') ?>&identity_verified=<?= e($identity_verified ?? '') ?>&is_featured=<?= e($is_featured ?? '') ?>&completion_min=<?= e($completion_min ?? '') ?>&completion_max=<?= e($completion_max ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="page-link">التالي &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- CONFIRM DELETE DIALOG HANDLERS -->
<script>
    function toggleAdvancedFilters() {
        const adv = document.getElementById('advanced-filters');
        if (adv.style.display === 'none') {
            adv.style.display = 'flex';
        } else {
            adv.style.display = 'none';
        }
    }

    function submitBulkAction() {
        const form = document.getElementById('bulk-action-form');
        const action = form.querySelector('select[name="action"]').value;
        if (!action) {
            alert('الرجاء اختيار إجراء أولاً');
            return;
        }
        confirmDialog({
            title: 'تأكيد الإجراء الجماعي',
            message: `هل أنت متأكد من رغبتك في تطبيق هذا الإجراء الجماعي على كافة مزودي الخدمة المحددين؟`,
            confirmLabel: 'نعم، نفذ الإجراء',
            confirmClass: 'btn-primary',
            onConfirm: () => {
                form.submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Bulk action check-all mechanism
        const selectAll = document.getElementById('select-all');
        const selectItems = document.querySelectorAll('.select-item');
        const bulkToolbar = document.getElementById('bulk-actions-toolbar');
        const selectedCountLabel = document.getElementById('selected-count');

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                selectItems.forEach(item => item.checked = selectAll.checked);
                updateBulkToolbar();
            });
        }

        selectItems.forEach(item => {
            item.addEventListener('change', () => {
                updateBulkToolbar();
            });
        });

        function updateBulkToolbar() {
            const checkedCount = document.querySelectorAll('.select-item:checked').length;
            if (checkedCount > 0) {
                bulkToolbar.style.display = 'flex';
                selectedCountLabel.textContent = checkedCount;
            } else {
                bulkToolbar.style.display = 'none';
            }
        }

        // Handle delete clicks
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                confirmDialog({
                    title: 'تأكيد الحذف المؤقت',
                    message: `هل أنت متأكد من رغبتك في حذف مزود الخدمة "${name}" مؤقتاً؟ سيتم إخفاؤه من قوائم البحث والصفحات العامة.`,
                    confirmLabel: 'نعم، احذف مؤقتاً',
                    confirmClass: 'btn-danger',
                    onConfirm: () => {
                        submitActionForm('<?= url('admin/providers/') ?>' + id + '/delete');
                    }
                });
            });
        });

        // Handle restore clicks
        document.querySelectorAll('.restore-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                confirmDialog({
                    title: 'تأكيد استعادة مزود الخدمة',
                    message: `هل تريد استعادة مزود الخدمة "${name}" وإعادته للقوائم النشطة؟`,
                    confirmLabel: 'نعم، استعد',
                    confirmClass: 'btn-success',
                    onConfirm: () => {
                        submitActionForm('<?= url('admin/providers/') ?>' + id + '/restore');
                    }
                });
            });
        });

        function submitActionForm(actionUrl) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= csrf_token() ?>';
            
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
