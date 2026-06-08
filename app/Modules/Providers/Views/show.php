<?php
/**
 * show.php
 * Providers Module Admin Details View
 */
?>

<div class="section-header">
    <h1 class="section-title">عرض تفاصيل مزود الخدمة</h1>
    <div style="display: flex; gap: 8px;">
        <a href="<?= url('admin/providers/' . $item['id'] . '/edit') ?>" class="btn btn-primary">
            تعديل البيانات
        </a>
        <a href="<?= url('admin/providers') ?>" class="btn btn-secondary">
            العودة للقائمة
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    
    <!-- Left Column: Details -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 16px; font-weight: 700; color: var(--text-color);">معلومات الحساب الأساسية</h2>
            </div>
            <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">الاسم الكامل (عربي)</strong>
                    <div style="font-size: 15px; font-weight: 700; color: var(--text-color);"><?= e($item['display_name_ar']) ?></div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">الرابط اللطيف (Slug)</strong>
                    <div style="font-size: 14px; font-family: monospace;"><?= e($item['slug']) ?></div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">الخدمة الأساسية</strong>
                    <div><span class="badge badge-primary"><?= e($item['service_name']) ?></span></div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">المدينة الرئيسية</strong>
                    <div style="font-size: 14px; font-weight: bold;"><?= e($item['city_name']) ?></div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">رقم الهاتف</strong>
                    <div style="font-size: 14px; font-weight: bold; direction: ltr; text-align: right;"><?= e($item['phone']) ?></div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">رقم الواتساب</strong>
                    <div style="font-size: 14px; font-weight: bold; direction: ltr; text-align: right;"><?= e($item['whatsapp'] ?: 'مطابق للهاتف الأساسي') ?></div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">نوع العمل</strong>
                    <div style="font-size: 14px;"><?= $item['business_type'] === 'company' ? 'شركة / مؤسسة' : 'فرد مستقل' ?></div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">سنوات الخبرة</strong>
                    <div style="font-size: 14px; font-weight: bold;"><?= $item['years_experience'] ?> سنة</div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">السعر المبدئي</strong>
                    <div style="font-size: 14px; font-weight: bold;">
                        <?= $item['starting_price'] ? number_format($item['starting_price']) . ' ل.س' : 'غير محدد' ?>
                        <?php if ($item['starting_price']): ?>
                            <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">/ <?= $item['price_unit'] === 'hour' ? 'ساعة' : ($item['price_unit'] === 'day' ? 'يوم' : 'خدمة') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 4px;">وزن الترتيب</strong>
                    <div style="font-size: 14px; font-weight: bold;"><?= $item['sort_weight'] ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 16px; font-weight: 700; color: var(--text-color);">التغطية والخدمات الثانوية</h2>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 8px;">المناطق المغطاة</strong>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <?php if (empty($item['areas_covered'])): ?>
                            <span style="font-size: 13px; color: var(--text-muted);">كامل مناطق المدينة.</span>
                        <?php else: ?>
                            <?php foreach ($item['areas_covered'] as $area): ?>
                                <span class="badge badge-secondary"><?= e($area) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 8px;">الخدمات الإضافية / المتقاطعة</strong>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <?php if (empty($item['secondary_services'])): ?>
                            <span style="font-size: 13px; color: var(--text-muted);">لا توجد خدمات إضافية.</span>
                        <?php else: ?>
                            <?php foreach ($item['secondary_services'] as $secSrv): ?>
                                <span class="badge badge-secondary" style="border: 1px solid var(--primary);"><?= e($secSrv) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- AUDIT LOGS -->
        <div class="card">
            <div class="card-header">
                <h2 style="font-size: 16px; font-weight: 700; color: var(--text-color);">سجل العمليات الإدارية (Audit Trail)</h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($auditLogs)): ?>
                    <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                        لا يوجد سجل عمليات إدارية مسجل لهذا الحساب بعد.
                    </div>
                <?php else: ?>
                    <table class="table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>تاريخ العملية</th>
                                <th>المسؤول</th>
                                <th>نوع الإجراء</th>
                                <th>عنوان IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditLogs as $log): ?>
                                <tr>
                                    <td style="font-size: 12px; font-family: monospace;"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                                    <td><strong><?= e($log['admin_name'] ?: 'مدير النظام') ?></strong></td>
                                    <td>
                                        <?php if ($log['action'] === 'create_provider'): ?>
                                            <span class="badge badge-success" style="font-size: 11px;">إنشاء الحساب</span>
                                        <?php elseif ($log['action'] === 'update_provider'): ?>
                                            <span class="badge badge-primary" style="font-size: 11px;">تعديل البيانات</span>
                                        <?php elseif ($log['action'] === 'delete_provider'): ?>
                                            <span class="badge badge-danger" style="font-size: 11px;">حذف مؤقت</span>
                                        <?php elseif ($log['action'] === 'restore_provider'): ?>
                                            <span class="badge badge-success" style="font-size: 11px;">استعادة الحساب</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary" style="font-size: 11px;"><?= e($log['action']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 12px; font-family: monospace; direction: ltr; text-align: right;"><?= e($log['ip_address']) ?></td>
                                </tr>
                            <?php endphp ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Status & Images -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Status Card -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 14px; font-weight: 700; color: var(--text-color);">حالة الحساب والظهور</h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: var(--text-muted);">حالة التفعيل العام:</span>
                    <?php if ($item['is_active']): ?>
                        <span class="badge badge-success">منشور وعام</span>
                    <?php else: ?>
                        <span class="badge badge-danger">مخفي وغير نشط</span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: var(--text-muted);">حالة القبول الإداري:</span>
                    <?php if ($item['status'] === 'approved'): ?>
                        <span class="badge badge-success">مقبول</span>
                    <?php elseif ($item['status'] === 'pending'): ?>
                        <span class="badge badge-warning">قيد المراجعة</span>
                    <?php elseif ($item['status'] === 'rejected'): ?>
                        <span class="badge badge-danger">مرفوض</span>
                    <?php else: ?>
                        <span class="badge badge-danger" style="opacity: 0.7;">معلق</span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: var(--text-muted);">شارة التوثيق:</span>
                    <?php if ($item['verified']): ?>
                        <span class="badge badge-success">حساب موثق</span>
                    <?php else: ?>
                        <span class="badge badge-secondary" style="opacity: 0.6;">غير موثق</span>
                    <?php endif; ?>
                </div>
                <?php if ($item['deleted_at']): ?>
                    <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 4px; color: var(--danger); font-size: 12px; font-weight: bold; text-align: center;">
                        هذا الحساب محذوف مؤقتاً منذ: <?= date('Y-m-d H:i', strtotime($item['deleted_at'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Logo Card -->
        <div class="card" style="text-align: center; padding: 24px;">
            <strong style="color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 12px; text-align: right;">صورة الشعار (الملف الشخصي)</strong>
            <?php if (!empty($item['logo'])): ?>
                <img src="<?= url($item['logo']) ?>" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-light); margin: 0 auto;" alt="<?= e($item['display_name_ar']) ?>">
            <?php else: ?>
                <div style="width: 120px; height: 120px; border-radius: 50%; background-color: var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; color: var(--text-muted); margin: 0 auto;">
                    <?= mb_substr($item['display_name_ar'], 0, 1) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Gallery Photos Card -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 14px; font-weight: 700; color: var(--text-color);">معرض الصور ونماذج الأعمال</h3>
            </div>
            <div class="card-body">
                <?php if (empty($item['work_photos'])): ?>
                    <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 12px 0;">
                        لا توجد صور مضافة لمعرض الأعمال بعد.
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(68px, 1fr)); gap: 8px;">
                        <?php foreach ($item['work_photos'] as $photo): ?>
                            <a href="<?= url($photo) ?>" target="_blank">
                                <img src="<?= url($photo) ?>" style="width: 100%; height: 68px; border-radius: 4px; object-fit: cover; border: 1px solid var(--border-color); cursor: zoom-in;" title="اضغط لفتح الصورة الأصلية">
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SEO Metadata Card -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 14px; font-weight: 700; color: var(--text-color);">إعدادات SEO للبحث</h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <strong style="color: var(--text-muted); font-size: 11px; display: block; margin-bottom: 2px;">Meta Title (عنوان البحث)</strong>
                    <div style="font-size: 12px; font-weight: bold;"><?= e($item['meta_title_ar'] ?: 'توليد تلقائي افتراضي') ?></div>
                </div>
                <div>
                    <strong style="color: var(--text-muted); font-size: 11px; display: block; margin-bottom: 2px;">Meta Description (وصف البحث)</strong>
                    <div style="font-size: 12px;"><?= e($item['meta_description_ar'] ?: 'توليد تلقائي افتراضي') ?></div>
                </div>
            </div>
        </div>

    </div>
</div>
