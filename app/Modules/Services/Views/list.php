<?php
/**
 * list.php
 * Services Module Admin List View
 */
?>

<div class="section-header">
    <h1 class="section-title">إدارة الخدمات</h1>
    <a href="<?= url('admin/services/create') ?>" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        إضافة خدمة جديدة
    </a>
</div>

<!-- FILTERS TOOLBAR -->
<form method="GET" action="<?= url('admin/services') ?>" class="toolbar" style="gap: 12px; align-items: flex-end;">
    <div style="display: flex; flex-wrap: wrap; gap: 12px; flex-grow: 1;">
        <!-- Keyword Search -->
        <div class="form-group" style="margin-bottom: 0; min-width: 220px; flex-grow: 1;">
            <label class="form-label" for="keyword">بحث بالاسم أو الرمز</label>
            <div class="search-bar" style="max-width: 100%;">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="keyword" name="keyword" value="<?= e($keyword ?? '') ?>" placeholder="اكتب للبحث..." class="form-control" style="padding-right: 36px; padding-left: 12px;">
            </div>
        </div>

        <!-- Active Status Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 140px;">
            <label class="form-label" for="is_active">حالة النشاط</label>
            <select name="is_active" id="is_active" class="form-control">
                <option value="">الكل</option>
                <option value="1" <?= $is_active === '1' ? 'selected' : '' ?>>نشط فقط</option>
                <option value="0" <?= $is_active === '0' ? 'selected' : '' ?>>معطل فقط</option>
            </select>
        </div>

        <!-- Deletion Status Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 140px;">
            <label class="form-label" for="is_deleted">حالة الأرشيف</label>
            <select name="is_deleted" id="is_deleted" class="form-control">
                <option value="0" <?= $is_deleted === '0' || $is_deleted === null ? 'selected' : '' ?>>الخدمات الحالية</option>
                <option value="1" <?= $is_deleted === '1' ? 'selected' : '' ?>>المحذوفة مؤقتاً</option>
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
        <?php if (!empty($keyword) || $is_active !== null || $is_deleted === '1'): ?>
            <a href="<?= url('admin/services') ?>" class="btn btn-secondary" style="padding: 0.625rem 1.25rem; color: var(--danger);">
                إلغاء التصفية
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- DATA TABLE -->
<div class="table-container">
    <?php if (empty($items)): ?>
        <div class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
            <h3 class="empty-title">لا توجد خدمات مطابقة</h3>
            <p class="empty-desc">لم نعثر على أي خدمات تطابق معايير البحث الحالية. جرب تغيير كلمات البحث أو المرشحات.</p>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">الأيقونة</th>
                    <th>
                        <a href="?sort_by=key&sort_dir=<?= $sort_by === 'key' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                            الرمز التعريفي
                            <?php if ($sort_by === 'key'): ?>
                                <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=display_name_ar&sort_dir=<?= $sort_by === 'display_name_ar' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                            الاسم الكامل
                            <?php if ($sort_by === 'display_name_ar'): ?>
                                <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>الاسم المختصر</th>
                    <th>الرابط اللطيف</th>
                    <th style="width: 100px; text-align: center;">
                        <a href="?sort_by=sort_order&sort_dir=<?= $sort_by === 'sort_order' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                            الترتيب
                            <?php if ($sort_by === 'sort_order'): ?>
                                <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th style="width: 120px; text-align: center;">الحالة</th>
                    <th style="width: 200px; text-align: left;">العمليات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="text-align: center; font-size: 20px;">
                            <?php
                                // Inline custom emoji matching for icons, or render custom class
                                $iconMap = [
                                    'icon-cleaning' => '🧹',
                                    'icon-plumbing' => '🚰',
                                    'icon-electricity' => '⚡',
                                    'icon-painting' => '🎨',
                                    'icon-moving' => '📦'
                                ];
                                echo e($iconMap[$item['icon']] ?? ($item['icon'] ?: '🛠️'));
                            ?>
                        </td>
                        <td style="font-family: monospace; font-size: 13px; font-weight: 600; color: var(--text-secondary);">
                            <?= e($item['key']) ?>
                        </td>
                        <td>
                            <a href="<?= url('admin/services/' . $item['id']) ?>" style="font-weight: 700; color: var(--text-primary);">
                                <?= e($item['display_name_ar']) ?>
                            </a>
                        </td>
                        <td><?= e($item['short_name_ar']) ?></td>
                        <td style="font-family: monospace; font-size: 13px; color: var(--text-muted);"><?= e($item['slug']) ?></td>
                        <td style="text-align: center; font-weight: 600;"><?= e($item['sort_order']) ?></td>
                        <td style="text-align: center;">
                            <?php if ($item['is_deleted']): ?>
                                <span class="badge badge-danger">محذوف مؤقتاً</span>
                            <?php elseif ($item['is_active']): ?>
                                <span class="badge badge-success">نشط</span>
                            <?php else: ?>
                                <span class="badge badge-warning">معطل</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: left;">
                            <div style="display: inline-flex; gap: 6px; justify-content: flex-end;">
                                <a href="<?= url('admin/services/' . $item['id']) ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" title="عرض التفاصيل وسجل العمليات">
                                    التفاصيل
                                </a>
                                
                                <?php if (!$item['is_deleted']): ?>
                                    <a href="<?= url('admin/services/' . $item['id'] . '/edit') ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; color: var(--primary);" title="تعديل الخدمة">
                                        تعديل
                                    </a>
                                    
                                    <form action="<?= url('admin/services/' . $item['id'] . '/delete') ?>" method="POST" style="margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; color: var(--danger);" data-confirm="هل أنت متأكد من أرشفة (حذف مؤقت) لخدمة '<?= e($item['display_name_ar']) ?>'؟">
                                            حذف
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('admin/services/' . $item['id'] . '/restore') ?>" method="POST" style="margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-success" style="padding: 4px 8px; font-size: 12px;" data-confirm="هل أنت متأكد من استعادة خدمة '<?= e($item['display_name_ar']) ?>'؟">
                                            استعادة
                                        </button>
                                    </form>
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
    <div class="pagination-container">
        <div class="pagination-info">
            عرض عناصر من <strong><?= (($currentPage - 1) * $limit) + 1 ?></strong> إلى <strong><?= min($totalItems, $currentPage * $limit) ?></strong> من أصل <strong><?= $totalItems ?></strong> خدمة.
        </div>
        <div class="pagination-buttons">
            <!-- Previous Button -->
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>&keyword=<?= e($keyword ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;">
                    السابق
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px; opacity: 0.5; cursor: not-allowed;" disabled>
                    السابق
                </button>
            <?php endif; ?>

            <!-- Page indicators -->
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?page=<?= $p ?>&keyword=<?= e($keyword ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="btn <?= $currentPage === $p ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 6px 12px; font-size: 13px;">
                    <?= $p ?>
                </a>
            <?php endfor; ?>

            <!-- Next Button -->
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&keyword=<?= e($keyword ?? '') ?>&is_active=<?= e($is_active ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>&sort_by=<?= e($sort_by) ?>&sort_dir=<?= e($sort_dir) ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;">
                    التالي
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px; opacity: 0.5; cursor: not-allowed;" disabled>
                    التالي
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
