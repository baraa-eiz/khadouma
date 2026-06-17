<?php
/**
 * show.php
 * Users Module Admin Show/Details View
 */
?>

<div class="section-header">
    <div style="display: flex; align-items: center; gap: 12px;">
        <h1 class="section-title"><?= e($item->display_name) ?></h1>
        <div style="display: flex; gap: 6px;">
            <?php if ($item->deleted_at !== null): ?>
                <span class="badge badge-danger">محذوف مؤقتاً</span>
            <?php elseif ($item->status === 'active'): ?>
                <span class="badge badge-success">نشط</span>
            <?php else: ?>
                <span class="badge badge-warning">موقوف</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="display: flex; gap: 8px;">
        <a href="<?= url('admin/users') ?>" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2"/></svg>
            العودة للقائمة
        </a>
        <?php if ($item->deleted_at === null): ?>
            <!-- Status Toggle Form -->
            <form action="<?= url('admin/users/' . $item->id . '/status') ?>" method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <?php if ($item->status === 'active'): ?>
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" class="btn btn-secondary" style="color: var(--warning);">إيقاف الحساب</button>
                <?php else: ?>
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="btn btn-success">تفعيل الحساب</button>
                <?php endif; ?>
            </form>

            <a href="<?= url('admin/users/' . $item->id . '/edit') ?>" class="btn btn-primary">
                تعديل البيانات
            </a>

            <form action="<?= url('admin/users/' . $item->id . '/delete') ?>" method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" class="btn btn-danger" data-confirm="هل أنت متأكد من أرشفة وحذف حساب '<?= e($item->display_name) ?>'؟">
                    أرشفة الحساب
                </button>
            </form>
        <?php else: ?>
            <form action="<?= url('admin/users/' . $item->id . '/restore') ?>" method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" class="btn btn-success" data-confirm="هل أنت متأكد من استعادة حساب '<?= e($item->display_name) ?>'؟">
                    استعادة الحساب
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
    
    <!-- User Profile Details -->
    <div class="card" style="grid-column: 1 / 2;">
        <div class="card-header">
            <h3 class="card-title">بيانات الحساب الأساسية</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table" style="box-shadow: none; border: none;">
                <tbody>
                    <tr>
                        <th style="width: 150px; border-bottom: 1px solid var(--border-color); background: none;">المعرّف الداخلي (ID)</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-family: monospace; font-size: 13px; font-weight: 600;"><?= e($item->id) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">المعرّف العام (UUID)</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-family: monospace; font-size: 12px; color: var(--text-secondary);"><?= e($item->public_id) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">اسم المستخدم الكامل</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-weight: 700;"><?= e($item->display_name) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">البريد الإلكتروني</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-family: monospace;"><?= e($item->email ?? 'غير محدد') ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">رقم الهاتف</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-family: monospace;"><?= e($item->phone ?? 'غير محدد') ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">المدينة والمنطقة</th>
                        <td style="border-bottom: 1px solid var(--border-color);"><?= e($item->city_id ? 'معرّف المدينة: ' . $item->city_id : 'غير محدد') ?> / <?= e($item->area_id ? 'معرّف المنطقة: ' . $item->area_id : 'غير محدد') ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">نسبة اكتمال الملف</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-weight: 700; color: var(--primary);"><?= e($item->completion_score) ?>%</td>
                    </tr>
                    <tr>
                        <th style="border-bottom: 1px solid var(--border-color); background: none;">تاريخ التسجيل</th>
                        <td style="border-bottom: 1px solid var(--border-color); font-size: 13px; color: var(--text-secondary);"><?= e($item->created_at) ?></td>
                    </tr>
                    <tr>
                        <th style="border-bottom: none; background: none;">آخر تسجيل دخول</th>
                        <td style="border-bottom: none; font-size: 13px; color: var(--text-secondary);"><?= e($item->last_login_at ?? 'لم يسجل دخول بعد') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Extra Info & Preferences -->
    <div class="card" style="grid-column: 2 / 3;">
        <div class="card-header">
            <h3 class="card-title">العنوان والخيارات المفضلة</h3>
        </div>
        <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <h4 style="font-size: 13px; font-weight: bold; color: var(--text-secondary); margin-bottom: 4px;">العنوان بالتفصيل:</h4>
                <div style="background-color: var(--bg-hover); padding: 12px; border-radius: var(--radius-sm); font-size: 14px; line-height: 1.6; min-height: 80px; color: var(--text-primary);">
                    <?= $item->default_address ? nl2br(e($item->default_address)) : '<em style="color: var(--text-muted);">لا يوجد عنوان متوفر.</em>' ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <h4 style="font-size: 13px; font-weight: bold; color: var(--text-secondary); margin-bottom: 4px;">طريقة التواصل:</h4>
                    <div style="background-color: var(--bg-hover); padding: 10px 12px; border-radius: var(--radius-sm); font-size: 13px;">
                        <?= $item->preferred_contact_method === 'email' ? 'البريد الإلكتروني 📧' : 'الهاتف 📞' ?>
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 13px; font-weight: bold; color: var(--text-secondary); margin-bottom: 4px;">اللغة المفضلة:</h4>
                    <div style="background-color: var(--bg-hover); padding: 10px 12px; border-radius: var(--radius-sm); font-size: 13px;">
                        <?= $item->preferred_language === 'en' ? 'English 🇺🇸' : 'العربية 🇸🇾' ?>
                    </div>
                </div>
            </div>

            <div>
                <h4 style="font-size: 13px; font-weight: bold; color: var(--text-secondary); margin-bottom: 4px;">المنطقة الزمنية:</h4>
                <div style="background-color: var(--bg-hover); padding: 10px 12px; border-radius: var(--radius-sm); font-size: 13px; font-family: monospace;">
                    <?= e($item->timezone) ?>
                </div>
            </div>

            <div>
                <h4 style="font-size: 13px; font-weight: bold; color: var(--text-secondary); margin-bottom: 4px;">الرسائل التسويقية والعروض:</h4>
                <div style="background-color: var(--bg-hover); padding: 10px 12px; border-radius: var(--radius-sm); font-size: 13px;">
                    <?= $item->marketing_opt_in ? '✅ موافق ومقيد بالخدمة' : '❌ غير موافق' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AUDIT LOGS TABLE -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">سجل العمليات والتتبع للحساب (Audit Trail)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($auditLogs)): ?>
            <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 14px;">
                لا توجد عمليات مسجلة لهذا المستخدم بعد.
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
                                        'create_user' => '<span class="badge badge-success">إنشاء الحساب</span>',
                                        'update_user' => '<span class="badge badge-info">تعديل البيانات</span>',
                                        'delete_user' => '<span class="badge badge-danger">أرشفة الحساب</span>',
                                        'restore_user' => '<span class="badge badge-success">استعادة الحساب</span>',
                                        'toggle_user_status' => '<span class="badge badge-warning">تغيير حالة الحساب</span>',
                                    ];
                                    echo $actionMap[$log['action']] ?? '<span class="badge">' . e($log['action']) . '</span>';
                                ?>
                            </td>
                            <td style="line-height: 1.6;">
                                <?php if ($log['action'] === 'create_user'): ?>
                                    <span style="color: var(--success); font-weight: 600;">تم إنشاء الحساب لأول مرة.</span>
                                <?php elseif ($log['action'] === 'update_user' && $log['old_value_json'] && $log['new_value_json']):
                                    $oldArr = json_decode($log['old_value_json'], true) ?: [];
                                    $newArr = json_decode($log['new_value_json'], true) ?: [];
                                    $diffs = [];
                                    foreach ($newArr as $k => $val) {
                                        if (array_key_exists($k, $oldArr) && $oldArr[$k] !== $val) {
                                            $fieldLabel = [
                                                'display_name' => 'الاسم',
                                                'email' => 'البريد الإلكتروني',
                                                'phone' => 'الهاتف',
                                                'city_id' => 'المدينة',
                                                'area_id' => 'المنطقة',
                                                'default_address' => 'العنوان بالتفصيل',
                                                'preferred_contact_method' => 'طريقة التواصل',
                                                'preferred_language' => 'اللغة المفضلة',
                                                'timezone' => 'المنطقة الزمنية',
                                                'status' => 'الحالة',
                                                'marketing_opt_in' => 'الرسائل التسويقية'
                                            ][$k] ?? $k;
                                            
                                            $oldValStr = is_bool($oldArr[$k]) ? ($oldArr[$k] ? 'نعم' : 'لا') : (string)$oldArr[$k];
                                            $newValStr = is_bool($val) ? ($val ? 'نعم' : 'لا') : (string)$val;
                                            
                                            $diffs[] = "<strong>$fieldLabel:</strong> <del style='color: var(--danger);'>$oldValStr</del> &larr; <ins style='color: var(--success); text-decoration: none;'>$newValStr</ins>";
                                        }
                                    }
                                    echo implode('<br>', $diffs);
                                else: ?>
                                    <span style="color: var(--text-muted);">تم تعديل حالة أو إعدادات الحساب.</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: left; font-family: monospace; font-size: 11px; color: var(--text-muted);">
                                <?= substr(e($log['ip_hash']), 0, 16) ?>...
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
