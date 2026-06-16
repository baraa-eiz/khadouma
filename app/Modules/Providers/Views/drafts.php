<?php
/**
 * drafts.php
 * Admin List of Pending Provider Drafts
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline" style="border-radius: 12px; border: 1px solid var(--border-color); background: #fff; padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 1.35rem; font-weight: 800; color: var(--text-primary);">📝 طلبات التسجيل والتحديث المعلقة للمراجعة</h3>
                <span class="badge" style="background-color: #dbeafe; color: #1e40af; padding: 6px 12px; font-size: 0.9rem; font-weight: 700; border-radius: 6px;">
                    إجمالي الطلبات: <?= count($items) ?>
                </span>
            </div>

            <!-- Flash messages -->
            <div style="margin-bottom: 15px;">
                <?php include dirname(dirname(dirname(dirname(__DIR__)))) . '/views/components/flash.php'; ?>
            </div>

            <?php if (empty($items)): ?>
                <div style="text-align: center; padding: 50px 20px; color: var(--text-secondary);">
                    <div style="font-size: 3rem; margin-bottom: 15px;">🎉</div>
                    <h4 style="font-weight: 800; margin-bottom: 8px;">لا توجد أي طلبات معلقة بانتظار التدقيق!</h4>
                    <p style="font-size: 0.9rem;">جميع تسجيلات الحرفيين وتحديثاتهم معتمدة ومحدثة بالكامل.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 2px solid var(--border-color); text-align: right;">
                                <th style="padding: 12px; font-weight: 800;">الاسم التجاري بالمسودة</th>
                                <th style="padding: 12px; font-weight: 800;">حساب تسجيل الدخول</th>
                                <th style="padding: 12px; font-weight: 800;">المدينة</th>
                                <th style="padding: 12px; font-weight: 800;">نوع الطلب</th>
                                <th style="padding: 12px; font-weight: 800;">تاريخ التقديم</th>
                                <th style="padding: 12px; font-weight: 800; text-align: center;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr style="border-bottom: 1px solid var(--border-color); vertical-align: middle;">
                                    <td style="padding: 12px; font-weight: 700; color: var(--text-primary);">
                                        <?= e($item['display_name_ar'] ?: 'بلا اسم تجاري') ?>
                                    </td>
                                    <td style="padding: 12px; font-size: 0.9rem;">
                                        <strong><?= e($item['account_name']) ?></strong>
                                        <span style="display: block; color: var(--text-secondary); font-size: 0.8rem;"><?= e($item['account_email']) ?></span>
                                    </td>
                                    <td style="padding: 12px; font-size: 0.9rem;">
                                        <!-- Note: City names are translated in controller or display ID as fallback -->
                                        <?= $item['city_id'] ? 'مدينة #' . (int)$item['city_id'] : 'غير محدد' ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php if ($item['provider_id']): ?>
                                            <span class="badge" style="background-color: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 700;">
                                                ⚙️ تحديث ملف موجود
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 700;">
                                                🆕 تسجيل جديد بالكامل
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px; font-size: 0.85rem; color: var(--text-secondary);">
                                        <?= e($item['updated_at']) ?>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <a href="<?= url("admin/providers/drafts/{$item['id']}/compare") ?>" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 6px 12px;">
                                            🔍 مراجعة ومقارنة
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
