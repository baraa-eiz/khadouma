<?php
/**
 * reviews.php
 * Admin Review Moderation View
 */

if (!defined('IN_APP')) {
    exit;
}
?>

<div class="card" style="padding: 25px; border-radius: 16px; background-color: #ffffff; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                💬 مراجعة وتقييمات العملاء
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 5px; margin-bottom: 0;">
                قم بإقرار أو رفض أو حذف تقييمات العملاء المكتوبة لمزودي الخدمة.
            </p>
        </div>
        <div style="font-size: 0.85rem; background-color: var(--bg-hover); padding: 6px 12px; border-radius: 6px; font-weight: bold; color: var(--text-secondary);">
            إجمالي التقييمات: <?= count($reviews) ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <div style="margin-bottom: 20px;">
        <?php include APP_DIR . '/views/components/flash.php'; ?>
    </div>

    <?php if (empty($reviews)): ?>
        <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
            <span style="font-size: 3rem; display: block; margin-bottom: 15px;">📭</span>
            لا توجد أي تقييمات مسجلة في قاعدة البيانات حالياً.
        </div>
    <?php else: ?>
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-weight: 800;">
                        <th style="padding: 12px; font-weight: 800;">المزود</th>
                        <th style="padding: 12px; font-weight: 800;">العميل</th>
                        <th style="padding: 12px; font-weight: 800;">التقييم</th>
                        <th style="padding: 12px; font-weight: 800; width: 30%;">التعليق</th>
                        <th style="padding: 12px; font-weight: 800;">بصمة الأمان (IP/UA)</th>
                        <th style="padding: 12px; font-weight: 800;">الحالة</th>
                        <th style="padding: 12px; font-weight: 800; text-align: left;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $rev): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#fafafa'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 12px; font-weight: bold;">
                                <a href="<?= url('provider/' . $rev['provider_slug']) ?>" target="_blank" style="color: var(--primary); text-decoration: none;">
                                    <?= e($rev['provider_name']) ?>
                                </a>
                            </td>
                            <td style="padding: 12px; font-weight: 600; color: var(--text-primary);">
                                <?= e($rev['reviewer_name']) ?>
                            </td>
                            <td style="padding: 12px;">
                                <span style="color: #fbbf24; font-size: 0.95rem; font-weight: 800;">
                                    <?= str_repeat('★', (int)$rev['rating']) ?><span style="color: #e5e7eb;"><?= str_repeat('★', 5 - (int)$rev['rating']) ?></span>
                                </span>
                            </td>
                            <td style="padding: 12px; color: var(--text-secondary); line-height: 1.5;">
                                <?= e($rev['comment']) ?>
                                <span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">التاريخ: <?= e($rev['created_at']) ?></span>
                            </td>
                            <td style="padding: 12px; font-family: monospace; font-size: 0.8rem; color: var(--text-secondary);">
                                <div>IP: <span title="<?= e($rev['ip_hash']) ?>"><?= substr($rev['ip_hash'] ?? 'N/A', 0, 8) ?>...</span></div>
                                <div>UA: <span title="<?= e($rev['user_agent_hash']) ?>"><?= substr($rev['user_agent_hash'] ?? 'N/A', 0, 8) ?>...</span></div>
                            </td>
                            <td style="padding: 12px;">
                                <?php if ($rev['status'] === 'approved'): ?>
                                    <span style="background-color: #dcfce7; color: #15803d; font-size: 0.8rem; font-weight: bold; padding: 4px 10px; border-radius: 9999px;">منشور</span>
                                <?php elseif ($rev['status'] === 'rejected'): ?>
                                    <span style="background-color: #fee2e2; color: #b91c1c; font-size: 0.8rem; font-weight: bold; padding: 4px 10px; border-radius: 9999px;">مرفوض</span>
                                <?php else: ?>
                                    <span style="background-color: #fef3c7; color: #d97706; font-size: 0.8rem; font-weight: bold; padding: 4px 10px; border-radius: 9999px; animation: pulse 2s infinite;">معلق</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; text-align: left; white-space: nowrap;">
                                <?php if ($rev['status'] !== 'approved'): ?>
                                    <form action="<?= url('admin/reviews/' . $rev['id'] . '/approve') ?>" method="POST" style="display: inline-block; margin-left: 5px;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm" style="background-color: #10b981; color: white; border: none; padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                            ✓ موافقة
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($rev['status'] !== 'rejected'): ?>
                                    <form action="<?= url('admin/reviews/' . $rev['id'] . '/reject') ?>" method="POST" style="display: inline-block; margin-left: 5px;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm" style="background-color: #f59e0b; color: white; border: none; padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                            ⚠ رفض
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form action="<?= url('admin/reviews/' . $rev['id'] . '/delete') ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم نهائياً؟');">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm" style="background-color: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                        🗑️ حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
@keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}
</style>
