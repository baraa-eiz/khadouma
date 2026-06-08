<?php
/**
 * show.php
 * Areas Module Admin Show/Details View
 */
?>

<div class="section-header">
    <div style="display: flex; align-items: center; gap: 12px;">
        <h1 class="section-title"><?= e($item['display_name_ar']) ?></h1>
        <div style="display: flex; gap: 6px;">
            <?php if ($item['is_deleted']): ?>
                <span class="badge badge-danger">محذوف مؤقتاً</span>
            <?php elseif ($item['is_active']): ?>
                <span class="badge badge-success">نشط</span>
            <?php else: ?>
                <span class="badge badge-warning">معطل</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="display: flex; gap: 8px;">
        <a href="<?= url('admin/areas') ?>" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2"/></svg>
            العودة للقائمة
        </a>
        <?php if (!$item['is_deleted']): ?>
            <a href="<?= url('admin/areas/' . $item['id'] . '/edit') ?>" class="btn btn-primary">
                تعديل البيانات
            </a>
            <form action="<?= url('admin/areas/' . $item['id'] . '/delete') ?>" method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" class="btn btn-danger" data-confirm="هل أنت متأكد من أرشفة منطقة '<?= e($item['display_name_ar']) ?>'؟">
                    أرشفة المنطقة
                </button>
            </form>
        <?php else: ?>
            <form action="<?= url('admin/areas/' . $item['id'] . '/restore') ?>" method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" class="btn btn-success" data-confirm="هل أنت متأكد من استعادة منطقة '<?= e($item['display_name_ar']) ?>'؟">
                    استعادة المنطقة
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
    
    <!-- Area Details -->
    <div class="card" style="grid-column: 1 / 2;">
        <div class="card-header">
            <h3 class="card-title">بيانات المنطقة الأساسية</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table" style="box-shadow: none; border: none;">
                <tbody>
                    <tr>
                        <th style="width: 150px; border-bottom: 1px solid var(--border-color); background: none;">المدينة التابعة لها</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-weight: 700; color: var(--primary);"><?= e($item['city_name_ar']) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">المعرّف الداخلي (ID)</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-family: monospace; font-size: 13px; font-weight: 600;"><?= e($item['id']) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">المعرّف العام (UUID)</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-family: monospace; font-size: 12px; color: var(--text-secondary);"><?= e($item['public_id']) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">الرمز التعريفي (Key)</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-family: monospace; font-size: 13px; font-weight: 600; color: var(--text-secondary);"><?= e($item['key']) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">الاسم الكامل</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-weight: 700;"><?= e($item['display_name_ar']) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">الاسم بالإنجليزية</th>
                        <td style="border-bottom: 1px solid var(--border-color);"><?= e($item['display_name_en'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">الرابط اللطيف (Slug)</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-family: monospace; font-size: 13px; color: var(--text-muted);"><?= e($item['slug']) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">ترتيب الفرز</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-weight: 600;"><?= e($item['sort_order']) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">تاريخ الإنشاء</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-size: 13px; color: var(--text-secondary);"><?= e($item['created_at']) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: none; background: none;">آخر تحديث</th>
                        <td style="border-bottom: none; font-size: 13px; color: var(--text-secondary);"><?= e($item['updated_at']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SEO & Metadata Details -->
    <div class="card" style="grid-column: 2 / 3;">
        <div class="card-header">
            <h3 class="card-title">إعدادات محركات البحث (SEO)</h3>
        </div>
        <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <h4 style="font-size: 13px; font-weight: bold; color: var(--text-secondary); margin-bottom: 4px;">عنوان الميتا (Meta Title):</h4>
                <div style="background-color: var(--bg-hover); padding: 10px 12px; border-radius: var(--radius-sm); font-size: 13px; font-family: monospace; color: var(--text-primary);">
                    <?= $item['meta_title_ar'] ? e($item['meta_title_ar']) : '<em style="color: var(--text-muted);">مستند إلى العنوان الافتراضي للموقع</em>' ?>
                </div>
            </div>

            <div>
                <h4 style="font-size: 13px; font-weight: bold; color: var(--text-secondary); margin-bottom: 4px;">وصف الميتا (Meta Description):</h4>
                <div style="background-color: var(--bg-hover); padding: 12px; border-radius: var(--radius-sm); font-size: 13px; line-height: 1.5; color: var(--text-primary);">
                    <?= $item['meta_description_ar'] ? nl2br(e($item['meta_description_ar'])) : '<em style="color: var(--text-muted);">مستند إلى الوصف الافتراضي للموقع</em>' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AUDIT LOGS TABLE -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">سجل العمليات والتتبع للمنطقة (Audit Trail)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($auditLogs)): ?>
            <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 14px;">
                لا توجد عمليات مسجلة لهذه المنطقة بعد.
            </div>
        <?php else: ?>
            <table class="table" style="box-shadow: none; border: none; font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 160px;">تاريخ العملية</th>
                        <th style="width: 140px;">اسم المسؤول</th>
                        <th style="width: 140px;">نوع الإجراء</th>
                        <th>تفاصيل التغيير</th>
                        <th style="width: 140px; text-align: left;">بصمة جهاز IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditLogs as $log): ?>
                        <tr>
                            <td style="color: var(--text-secondary); font-family: monospace;"><?= e($log['created_at']) ?></td>
                            <td>
                                <span style="font-weight: 600; color: var(--text-primary);">
                                    <?= e($log['admin_name'] ?: 'نظام تلقائي') ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $actionMap = [
                                        'create_area' => '<span class="badge badge-success">إنشاء المنطقة</span>',
                                        'update_area' => '<span class="badge badge-info">تعديل البيانات</span>',
                                        'delete_area' => '<span class="badge badge-danger">أرشفة (حذف مؤقت)</span>',
                                        'restore_area' => '<span class="badge badge-success">استعادة المنطقة</span>',
                                    ];
                                    echo $actionMap[$log['action']] ?? '<span class="badge">' . e($log['action']) . '</span>';
                                ?>
                            </td>
                            <td style="line-height: 1.6;">
                                <?php if ($log['action'] === 'create_area'): ?>
                                    <span style="color: var(--success); font-weight: 600;">تم إنشاء السجل لأول مرة بالبيانات المدخلة.</span>
                                <?php elseif ($log['action'] === 'update_area' && $log['old_value_json'] && $log['new_value_json']):
                                    $oldArr = json_decode($log['old_value_json'], true) ?: [];
                                    $newArr = json_decode($log['new_value_json'], true) ?: [];
                                    $diffs = [];
                                    foreach ($newArr as $k => $val) {
                                        if (array_key_exists($k, $oldArr) && $oldArr[$k] !== $val) {
                                            $fieldLabel = [
                                                'city_id' => 'المدينة التابعة لها', 'key' => 'الرمز', 'slug' => 'الرابط اللطيف',
                                                'display_name_ar' => 'الاسم الكامل', 'display_name_en' => 'الاسم بالإنجليزية',
                                                'sort_order' => 'الترتيب', 'meta_title_ar' => 'عنوان الميتا',
                                                'meta_description_ar' => 'وصف الميتا', 'is_active' => 'حالة النشاط'
                                            ][$k] ?? $k;
                                            
                                            $oldValStr = is_bool($oldArr[$k]) ? ($oldArr[$k] ? 'نعم' : 'لا') : (string)$oldArr[$k];
                                            $newValStr = is_bool($val) ? ($val ? 'نعم' : 'لا') : (string)$val;
                                            
                                            $diffs[] = "<strong>$fieldLabel:</strong> <del style='color: var(--danger);'>$oldValStr</del> &larr; <ins style='color: var(--success); text-decoration: none;'>$newValStr</ins>";
                                        }
                                    }
                                    echo implode('<br>', $diffs);
                                else: ?>
                                    <span style="color: var(--text-muted);">لا توجد تفاصيل إضافية.</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: left; font-family: monospace; font-size: 11px; color: var(--text-muted);" title="SHA256 IP Hash">
                                <?= substr(e($log['ip_hash']), 0, 16) ?>...
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
