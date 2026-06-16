<?php
/**
 * verification.php
 * Admin Verification Moderation View
 */

if (!defined('IN_APP')) {
    exit;
}
?>

<div class="card" style="padding: 25px; border-radius: 16px; background-color: #ffffff; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                🛡️ توثيق الحسابات والوثائق الثبوتية
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 5px; margin-bottom: 0;">
                قم بمراجعة السجلات التجارية والهويات الشخصية المرفوعة من مقدمي الخدمات لتفعيل شارة التوثيق.
            </p>
        </div>
        <div style="font-size: 0.85rem; background-color: var(--bg-hover); padding: 6px 12px; border-radius: 6px; font-weight: bold; color: var(--text-secondary);">
            إجمالي الطلبات: <?= count($providers) ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <div style="margin-bottom: 20px;">
        <?php include APP_DIR . '/views/components/flash.php'; ?>
    </div>

    <?php if (empty($providers)): ?>
        <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
            <span style="font-size: 3rem; display: block; margin-bottom: 15px;">📂</span>
            لا توجد طلبات توثيق نشطة حالياً.
        </div>
    <?php else: ?>
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-weight: 800;">
                        <th style="padding: 12px; font-weight: 800;">اسم مقدم الخدمة</th>
                        <th style="padding: 12px; font-weight: 800;">نوع الحساب</th>
                        <th style="padding: 12px; font-weight: 800;">نوع الوثيقة</th>
                        <th style="padding: 12px; font-weight: 800;">الوثيقة المرفوعة</th>
                        <th style="padding: 12px; font-weight: 800;">حالة التوثيق</th>
                        <th style="padding: 12px; font-weight: 800; width: 35%; text-align: left;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($providers as $prov): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#fafafa'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 12px; font-weight: bold;">
                                <a href="<?= url('provider/' . $prov['slug']) ?>" target="_blank" style="color: var(--primary); text-decoration: none;">
                                    <?= e($prov['display_name_ar']) ?>
                                </a>
                            </td>
                            <td style="padding: 12px; font-weight: 600; color: var(--text-primary);">
                                <?= $prov['business_type'] === 'company' ? '🏢 شركة/مؤسسة' : '👤 فرد/مستقل' ?>
                            </td>
                            <td style="padding: 12px; color: var(--text-secondary);">
                                <?= $prov['business_type'] === 'company' ? 'السجل التجاري' : 'الهوية الشخصية/جواز السفر' ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if (!empty($prov['verification_document_path'])): ?>
                                    <a href="<?= url('admin/verification/preview/' . $prov['verification_document_path']) ?>" target="_blank" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        👁️ استعراض الوثيقة بأمان
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">لا توجد وثيقة</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if ($prov['verification_status'] === 'verified'): ?>
                                    <span style="background-color: #dcfce7; color: #15803d; font-size: 0.8rem; font-weight: bold; padding: 4px 10px; border-radius: 9999px;">✓ موثق</span>
                                <?php elseif ($prov['verification_status'] === 'rejected'): ?>
                                    <span style="background-color: #fee2e2; color: #b91c1c; font-size: 0.8rem; font-weight: bold; padding: 4px 10px; border-radius: 9999px;">مرفوض</span>
                                <?php elseif ($prov['verification_status'] === 'resubmitted'): ?>
                                    <span style="background-color: #fef3c7; color: #d97706; font-size: 0.8rem; font-weight: bold; padding: 4px 10px; border-radius: 9999px;">أعيد إرساله</span>
                                <?php else: ?>
                                    <span style="background-color: #eff6ff; color: #1d4ed8; font-size: 0.8rem; font-weight: bold; padding: 4px 10px; border-radius: 9999px;">قيد الانتظار</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; text-align: left;">
                                <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end;">
                                    
                                    <!-- Approval Button & Form -->
                                    <?php if ($prov['verification_status'] !== 'verified'): ?>
                                        <form action="<?= url('admin/verification/' . $prov['id'] . '/approve') ?>" method="POST" style="margin: 0;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn" style="background-color: #10b981; color: white; border: none; padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                                ✓ قبول وتوثيق الحساب
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Rejection Button & Form -->
                                    <?php if ($prov['verification_status'] !== 'rejected'): ?>
                                        <form action="<?= url('admin/verification/' . $prov['id'] . '/reject') ?>" method="POST" style="width: 100%; max-width: 250px; display: flex; flex-direction: column; gap: 5px; margin: 0;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <div style="display: flex; gap: 5px;">
                                                <input type="text" name="rejection_reason" placeholder="سبب الرفض (مطلوب)..." required style="font-size: 0.8rem; padding: 6px; border: 1px solid var(--border-color); border-radius: 6px; flex: 1; min-width: 140px;">
                                                <button type="submit" class="btn" style="background-color: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                                    رفض
                                                </button>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Show Rejection Reason if currently rejected -->
                                    <?php if ($prov['verification_status'] === 'rejected' && !empty($prov['verification_rejection_reason'])): ?>
                                        <div style="font-size: 0.75rem; color: #b91c1c; background-color: #fef2f2; border: 1px solid #fee2e2; padding: 6px; border-radius: 4px; text-align: right; width: 100%;">
                                            <strong>سبب الرفض الحالي:</strong> <?= e($prov['verification_rejection_reason']) ?>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
