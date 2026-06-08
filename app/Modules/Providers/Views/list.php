<?php
/**
 * list.php
 * Providers Module Admin List View
 */
use App\Core\Config;
?>

<div class="section-header">
    <h1 class="section-title">إدارة مزودي الخدمات</h1>
    <a href="<?= url('admin/providers/create') ?>" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        إضافة مزود خدمة جديد
    </a>
</div>

<!-- FILTERS TOOLBAR -->
<form method="GET" action="<?= url('admin/providers') ?>" class="toolbar" style="gap: 12px; align-items: flex-end;">
    <div style="display: flex; flex-wrap: wrap; gap: 12px; flex-grow: 1;">
        <!-- Keyword Search -->
        <div class="form-group" style="margin-bottom: 0; min-width: 200px; flex-grow: 1;">
            <label class="form-label" for="keyword">بحث بالاسم أو الهاتف</label>
            <div class="search-bar" style="max-width: 100%;">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="keyword" name="keyword" value="<?= e($keyword ?? '') ?>" placeholder="اكتب للابحث..." class="form-control" style="padding-right: 36px; padding-left: 12px;">
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

    <!-- Hidden Sort Fields to Preserve Sorting on Search -->
    <input type="hidden" name="sort_by" value="<?= e($sort_by) ?>">
    <input type="hidden" name="sort_dir" value="<?= e($sort_dir) ?>">

    <div style="display: flex; gap: 8px;">
        <button type="submit" class="btn btn-secondary" style="padding: 0.625rem 1.25rem;">
            تطبيق التصفية
        </button>
        <?php if (!empty($keyword) || !empty($city_id) || !empty($service_id) || !empty($status) || $is_active !== null || $is_deleted === '1'): ?>
            <a href="<?= url('admin/providers') ?>" class="btn btn-secondary" style="padding: 0.625rem 1.25rem; color: var(--danger);">
                إلغاء التصفية
            </a>
        <?php endif; ?>
    </div>
</form>

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
                    <th style="width: 50px; text-align: center;">الشعار</th>
                    <th>
                        <a href="?sort_by=display_name_ar&sort_dir=<?= $sort_by === 'display_name_ar' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                            الاسم الكامل
                            <?php if ($sort_by === 'display_name_ar'): ?>
                                <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>المدينة</th>
                    <th>الخدمة الأساسية</th>
                    <th>رقم الهاتف</th>
                    <th style="text-align: center;">التوثيق</th>
                    <th style="text-align: center;">الظهور العام</th>
                    <th style="text-align: center;">حالة القبول</th>
                    <th style="width: 80px; text-align: center;">
                        <a href="?sort_by=sort_weight&sort_dir=<?= $sort_by === 'sort_weight' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
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
                        <td style="text-align: center;">
                            <?php if ($logo): ?>
                                <img src="<?= url($logo) ?>" alt="<?= e($item['display_name_ar']) ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-color);">
                            <?php else: ?>
                                <div style="width: 32px; height: 32px; border-radius: 50%; background-color: var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; color: var(--text-muted);">
                                    <?= mb_substr($item['display_name_ar'], 0, 1) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: bold; font-size: 14px; color: var(--text-color);">
                                <?= e($item['display_name_ar']) ?>
                            </div>
                            <span style="font-size: 11px; color: var(--text-muted); display: block; font-family: monospace;">
                                /provider/<?= e($item['slug']) ?>
                            </span>
                        </td>
                        <td><?= e($item['city_name']) ?></td>
                        <td>
                            <span class="badge badge-secondary"><?= e($item['service_name']) ?></span>
                        </td>
                        <td style="direction: ltr; text-align: right;"><?= e($item['phone']) ?></td>
                        <td style="text-align: center;">
                            <?php if ($item['verified']): ?>
                                <span class="badge badge-success" style="padding: 2px 6px; font-size: 11px;">موثق</span>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="padding: 2px 6px; font-size: 11px; opacity: 0.6;">غير موثق</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($item['is_active']): ?>
                                <span class="badge badge-success">منشور</span>
                            <?php else: ?>
                                <span class="badge badge-danger">مخفي</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
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
                        <td style="text-align: center; font-weight: bold;"><?= $item['sort_weight'] ?></td>
                        <td style="text-align: center;">
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

<!-- PAGINATION -->
<?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 6px;">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="page-link">&laquo; السابق</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>&keyword=<?= e($keyword ?? '') ?>&city_id=<?= e($city_id ?? '') ?>&service_id=<?= e($service_id ?? '') ?>&status=<?= e($status ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="page-link">التالي &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- CONFIRM DELETE DIALOG HANDLERS -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
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
