<?php
/**
 * list.php
 * Users Module Admin List View
 */
use App\Core\Config;
?>

<div class="section-header">
    <h1 class="section-title">إدارة المستخدمين</h1>
    <a href="<?= url('admin/users/create') ?>" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        إضافة مستخدم جديد
    </a>
</div>

<!-- FILTERS TOOLBAR -->
<form method="GET" action="<?= url('admin/users') ?>" class="toolbar" style="gap: 12px; align-items: flex-end;">
    <div style="display: flex; flex-wrap: wrap; gap: 12px; flex-grow: 1;">
        <!-- Keyword Search -->
        <div class="form-group" style="margin-bottom: 0; min-width: 220px; flex-grow: 1;">
            <label class="form-label" for="keyword">بحث بالاسم، البريد أو رقم الهاتف</label>
            <div class="search-bar" style="max-width: 100%;">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="keyword" name="keyword" value="<?= e($keyword ?? '') ?>" placeholder="اكتب للبحث..." class="form-control" style="padding-right: 36px; padding-left: 12px;">
            </div>
        </div>

        <!-- Status Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 140px;">
            <label class="form-label" for="status">الحالة</label>
            <select name="status" id="status" class="form-control">
                <option value="">الكل</option>
                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>نشط</option>
                <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>موقوف</option>
            </select>
        </div>

        <!-- Deletion Status Filter -->
        <div class="form-group" style="margin-bottom: 0; min-width: 140px;">
            <label class="form-label" for="is_deleted">الأرشيف</label>
            <select name="is_deleted" id="is_deleted" class="form-control">
                <option value="0" <?= ($is_deleted === '0' || $is_deleted === null) ? 'selected' : '' ?>>المستخدمين الحاليين</option>
                <option value="1" <?= $is_deleted === '1' ? 'selected' : '' ?>>المحذوفين مؤقتاً</option>
            </select>
        </div>
    </div>

    <!-- Hidden Sort Fields -->
    <input type="hidden" name="sort_by" value="<?= e($sort_by) ?>">
    <input type="hidden" name="sort_dir" value="<?= e($sort_dir) ?>">

    <div style="display: flex; gap: 8px;">
        <button type="submit" class="btn btn-secondary" style="padding: 0.625rem 1.25rem;">
            تطبيق التصفية
        </button>
        <?php if (!empty($keyword) || !empty($status) || $is_deleted === '1'): ?>
            <a href="<?= url('admin/users') ?>" class="btn btn-secondary" style="padding: 0.625rem 1.25rem; color: var(--danger);">
                إلغاء التصفية
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- DATA TABLE -->
<div class="table-container">
    <?php if (empty($items)): ?>
        <?php 
        $empty_title = 'لا يوجد مستخدمين مطابقين';
        $empty_desc = 'لم يتم العثور على أي حسابات مستخدمين تطابق معايير البحث الحالية.';
        $empty_action_url = url('admin/users/create');
        $empty_action_label = 'إضافة مستخدم جديد';
        include Config::get('app.paths.root') . '/views/components/empty_state.php'; 
        ?>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">الصورة</th>
                    <th>
                        <a href="?sort_by=display_name&sort_dir=<?= $sort_by === 'display_name' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&status=<?= e($status ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                            الاسم
                            <?php if ($sort_by === 'display_name'): ?>
                                <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>البريد الإلكتروني</th>
                    <th>رقم الهاتف</th>
                    <th>
                        <a href="?sort_by=completion_score&sort_dir=<?= $sort_by === 'completion_score' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&status=<?= e($status ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                            اكتمال الملف
                            <?php if ($sort_by === 'completion_score'): ?>
                                <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>الحالة</th>
                    <th>
                        <a href="?sort_by=created_at&sort_dir=<?= $sort_by === 'created_at' && $sort_dir === 'ASC' ? 'DESC' : 'ASC' ?>&keyword=<?= e($keyword ?? '') ?>&status=<?= e($status ?? '') ?>&is_deleted=<?= e($is_deleted ?? '') ?>" style="display: inline-flex; align-items: center; gap: 4px; color: inherit; font-weight: bold;">
                            تاريخ التسجيل
                            <?php if ($sort_by === 'created_at'): ?>
                                <span><?= $sort_dir === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th style="width: 200px; text-align: left;">العمليات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="text-align: center;">
                            <?php if ($item->avatar): ?>
                                <img src="/<?= e($item->avatar) ?>" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 36px; height: 36px; border-radius: 50%; background-color: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; color: #475569;">
                                    <?= mb_substr($item->display_name, 0, 1) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= url('admin/users/' . $item->id) ?>" style="font-weight: 700; color: var(--text-primary);">
                                <?= e($item->display_name) ?>
                            </a>
                        </td>
                        <td style="font-family: monospace; font-size: 13px;"><?= e($item->email ?? '-') ?></td>
                        <td style="font-family: monospace; font-size: 13px;"><?= e($item->phone ?? '-') ?></td>
                        <td style="text-align: center; font-weight: 600;"><?= e($item->completion_score) ?>%</td>
                        <td style="text-align: center;">
                            <?php if ($item->deleted_at !== null): ?>
                                <span class="badge badge-danger">محذوف مؤقتاً</span>
                            <?php elseif ($item->status === 'active'): ?>
                                <span class="badge badge-success">نشط</span>
                            <?php else: ?>
                                <span class="badge badge-warning">موقوف</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);"><?= e($item->created_at) ?></td>
                        <td style="text-align: left;">
                            <div style="display: inline-flex; gap: 6px; justify-content: flex-end;">
                                <a href="<?= url('admin/users/' . $item->id) ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">
                                    التفاصيل
                                </a>
                                
                                <?php if ($item->deleted_at === null): ?>
                                    <a href="<?= url('admin/users/' . $item->id . '/edit') ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; color: var(--primary);">
                                        تعديل
                                    </a>
                                    
                                    <form action="<?= url('admin/users/' . $item->id . '/delete') ?>" method="POST" style="margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; color: var(--danger);" data-confirm="هل أنت متأكد من حذف حساب المستخدم '<?= e($item->display_name) ?>'؟">
                                            حذف
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('admin/users/' . $item->id . '/restore') ?>" method="POST" style="margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-success" style="padding: 4px 8px; font-size: 12px;">
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
<?php 
$current_page = $currentPage;
$total_pages = $totalPages;
$total_records = $totalItems;
$per_page = $limit;

$queryParams = $_GET;
unset($queryParams['page']);
$base_url = '?' . http_build_query($queryParams);

include Config::get('app.paths.root') . '/views/components/pagination.php';
?>
