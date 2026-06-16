<?php
/**
 * dashboard.php
 * Provider Portal Dashboard
 */

if (!defined('IN_APP')) {
    exit;
}

if (isset($isLayoutCalled) && $isLayoutCalled) {
    // Content rendering phase
} else {
    $isLayoutCalled = true;
    $viewPath = __FILE__;
    require APP_DIR . '/includes/layout.php';
    return;
}
?>

<div class="container provider-dashboard" style="margin-top: 30px; margin-bottom: 50px;">
    <!-- Welcome Header -->
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">لوحة تحكم مزود الخدمة</h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">مرحباً بك، <strong><?= e($account['display_name']) ?></strong> (<?= e($account['email']) ?>)</p>
        </div>
        <div>
            <!-- Logout Form with CSRF protection -->
            <form action="<?= url('provider/logout') ?>" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm" style="font-weight: 700;">
                    🚪 تسجيل الخروج
                </button>
            </form>
        </div>
    </div>

    <!-- Flash messages -->
    <div style="margin-bottom: 20px;">
        <?php include APP_DIR . '/views/components/flash.php'; ?>
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
        
        <?php if (!$account['provider_id']): ?>
            <!-- ================= UNPUBLISHED / ONBOARDING STATE ================= -->
            <div class="card" style="padding: 30px; border: 1px dashed #10b981; border-radius: 16px; background-color: #f0fdf4;">
                <div style="display: flex; align-items: flex-start; gap: 15px; flex-wrap: wrap;">
                    <div style="font-size: 2.5rem;">🛠️</div>
                    <div style="flex: 1; min-width: 280px;">
                        <h2 style="font-size: 1.4rem; font-weight: 800; color: #065f46; margin-bottom: 10px;">ملفك المهني غير منشور بعد!</h2>
                        <p style="color: #047857; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">
                            قم بإكمال خطوات المعالج لإعداد ملفك المهني وإرساله لمراجعة الإدارة. بعد الموافقة، سيظهر ملفك للجمهور في نتائج البحث وتتمكن من استقبال طلبات العمل.
                        </p>
                        
                        <!-- Status Alert Box -->
                        <?php if ($draft['status'] === 'draft'): ?>
                            <div style="background-color: #fffbeb; border: 1px solid #fef3c7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <strong style="color: #b45309; display: block; margin-bottom: 5px;">حالة الملف الحالية: مسودة قيد التحضير</strong>
                                <span style="font-size: 0.9rem; color: #78350f;">الملف لم يتم إرساله للإدارة للمراجعة بعد.</span>
                            </div>
                        <?php elseif ($draft['status'] === 'pending_review'): ?>
                            <div style="background-color: #eff6ff; border: 1px solid #dbeafe; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <strong style="color: #1d4ed8; display: block; margin-bottom: 5px;">حالة الملف الحالية: بانتظار مراجعة الإدارة</strong>
                                <span style="font-size: 0.9rem; color: #1e40af;">يتم تدقيق ملفك الآن. لا يمكنك إجراء أي تعديلات خلال هذه الفترة.</span>
                            </div>
                        <?php elseif ($draft['status'] === 'rejected'): ?>
                            <div style="background-color: #fef2f2; border: 1px solid #fee2e2; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <strong style="color: #b91c1c; display: block; margin-bottom: 5px;">تم طلب تعديلات على ملفك من المسؤول</strong>
                                <p style="font-size: 0.9rem; color: #991b1b; margin-top: 5px; font-weight: 600;">
                                    سبب الملاحظة: <?= e($draft['admin_notes']) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Completeness Score Progress -->
                        <div style="margin-bottom: 25px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 700; color: #047857; margin-bottom: 8px;">
                                <span>نسبة اكتمال الملف الشخصي</span>
                                <span><?= (int)$completenessScore ?>%</span>
                            </div>
                            <div style="background-color: #e5e7eb; height: 12px; border-radius: 6px; overflow: hidden; width: 100%;">
                                <div style="background: linear-gradient(90deg, #10b981, #3b82f6); height: 100%; width: <?= (int)$completenessScore ?>%; transition: width 0.3s ease;"></div>
                            </div>
                        </div>

                        <!-- Missing Fields Tips -->
                        <?php if (!empty($missingFields) && $draft['status'] !== 'pending_review'): ?>
                            <div style="background: #ffffff; border: 1px solid var(--border-color); padding: 20px; border-radius: 12px; margin-bottom: 25px;">
                                <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--text-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                    💡 نصائح لتحسين ترتيب ملفك واكتماله:
                                </h3>
                                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem;">
                                    <?php foreach ($missingFields as $title => $tip): ?>
                                        <li style="display: flex; align-items: flex-start; gap: 8px; color: var(--text-secondary);">
                                            <span style="color: #d97706;">⚠️</span>
                                            <span><strong><?= e($title) ?>:</strong> <?= e($tip) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Button Actions -->
                        <?php if ($draft['status'] !== 'pending_review'): ?>
                            <a href="<?= url('provider/wizard') ?>" class="btn btn-primary" style="font-weight: 700; padding: 10px 24px;">
                                🚀 <?= $draft['status'] === 'rejected' ? 'تعديل وإعادة إرسال الملف' : 'استكمال معالج إعداد الملف' ?>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" style="font-weight: 700; padding: 10px 24px;" disabled>
                                🔒 بانتظار الموافقة (ملف مقفل)
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- ================= PUBLISHED / OPERATIONAL STATE ================= -->
            
            <!-- Published Profile Status Banner -->
            <div class="card" style="padding: 20px 25px; border-radius: 12px; background: #f8fafc; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: #10b981; animation: pulse 2s infinite;"></div>
                    <div>
                        <strong style="color: var(--text-primary); font-size: 1.05rem;">ملفك منشور ونشط للجمهور</strong>
                        <span style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-top: 2px;">يظهر ملفك حالياً للعملاء في نتائج البحث.</span>
                    </div>
                </div>
                <div>
                    <a href="<?= url('provider/' . $provider['slug']) ?>" target="_blank" class="btn btn-outline-primary btn-sm" style="font-weight: 700;">
                        👁️ عرض ملفي العام المنشور
                    </a>
                </div>
            </div>

            <!-- Dashboard Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                <div class="card" style="padding: 20px; text-align: center; border: 1px solid var(--border-color); border-radius: 12px;">
                    <div style="font-size: 1.8rem; margin-bottom: 6px;">📞</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary);"><?= (int)$contactCount ?></div>
                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">نقرات الاتصال والطلب</div>
                </div>
                <div class="card" style="padding: 20px; text-align: center; border: 1px solid var(--border-color); border-radius: 12px;">
                    <div style="font-size: 1.8rem; margin-bottom: 6px;">⭐️</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary);"><?= number_format((float)$provider['rating'], 1) ?> / 5</div>
                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">تقييم العملاء</div>
                </div>
                <div class="card" style="padding: 20px; text-align: center; border: 1px solid var(--border-color); border-radius: 12px;">
                    <div style="font-size: 1.8rem; margin-bottom: 6px;">💬</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary);"><?= (int)$provider['reviews_count'] ?></div>
                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">التقييمات المعتمدة</div>
                </div>
            </div>

            <!-- Version Control & Draft Panel -->
            <div class="card" style="padding: 25px; border: 1px solid var(--border-color); border-radius: 12px; background: #ffffff;">
                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">🛡️ إدارة وإصدارات الملف المهني</h3>
                
                <?php if ($draft && $draft['status'] === 'pending_review'): ?>
                    <!-- Draft Pending Review Banner -->
                    <div style="background-color: #eff6ff; border: 1px solid #dbeafe; padding: 20px; border-radius: 8px; display: flex; align-items: flex-start; gap: 12px;">
                        <span style="font-size: 1.4rem;">⏳</span>
                        <div>
                            <strong style="color: #1d4ed8; display: block; margin-bottom: 4px;">لديك تعديلات معلقة بانتظار مراجعة الإدارة</strong>
                            <p style="font-size: 0.9rem; color: #1e40af; line-height: 1.5;">
                                لقد قمت بتحديث بيانات ملفك الشخصي وإرسالها للموافقة. ستبقى تفاصيلك القديمة معروضة للجمهور حتى يقوم المسؤول باعتماد وقبول التعديلات الجديدة.
                            </p>
                            <span style="display: inline-block; background: #dbeafe; color: #1e40af; font-size: 0.8rem; font-weight: 700; padding: 3px 8px; border-radius: 4px; margin-top: 10px;">
                                ملف التعديل مقفل حالياً للمراجعة
                            </span>
                        </div>
                    </div>
                <?php elseif ($draft && $draft['status'] === 'rejected'): ?>
                    <!-- Draft Rejected Alert -->
                    <div style="background-color: #fef2f2; border: 1px solid #fee2e2; padding: 20px; border-radius: 8px; display: flex; align-items: flex-start; gap: 12px;">
                        <span style="font-size: 1.4rem;">❌</span>
                        <div style="flex: 1;">
                            <strong style="color: #b91c1c; display: block; margin-bottom: 4px;">رفض المسؤول تعديلات الملف الأخيرة</strong>
                            <p style="font-size: 0.9rem; color: #991b1b; font-weight: 600; margin-bottom: 15px;">
                                السبب المحدد: <?= e($draft['admin_notes']) ?>
                            </p>
                            <a href="<?= url('provider/wizard') ?>" class="btn btn-danger btn-sm" style="font-weight: 700;">
                                🛠️ تصحيح التعديلات وإعادة التقديم
                            </a>
                        </div>
                    </div>
                <?php elseif ($draft && $draft['status'] === 'draft'): ?>
                    <!-- Draft in progress -->
                    <div style="background-color: #fffbeb; border: 1px solid #fef3c7; padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <span style="font-size: 1.4rem;">📝</span>
                            <div>
                                <strong style="color: #b45309; display: block; margin-bottom: 4px;">لديك مسودة تعديلات غير منشورة</strong>
                                <span style="font-size: 0.9rem; color: #78350f;">التعديلات التي قمت بحفظها مؤخراً لم يتم تقديمها بعد ولا تزال غير مرئية للجمهور.</span>
                            </div>
                        </div>
                        <div>
                            <a href="<?= url('provider/wizard') ?>" class="btn btn-warning btn-sm" style="font-weight: 700; color: #78350f;">
                                ✍️ استكمال ونشر التعديلات
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Standard state: in sync -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <p style="font-size: 0.95rem; color: var(--text-secondary);">الملف الشخصي متطابق مع آخر نسخة معتمدة ومنشورة.</p>
                        </div>
                        <div>
                            <a href="<?= url('provider/wizard') ?>" class="btn btn-primary btn-sm" style="font-weight: 700;">
                                ✍️ تعديل وتحديث الملف المهني
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Reviews -->
            <div class="card" style="padding: 25px; border: 1px solid var(--border-color); border-radius: 12px; background: #ffffff;">
                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">⭐ أحدث تقييمات وآراء العملاء</h3>
                
                <?php if (empty($reviews)): ?>
                    <p style="text-align: center; color: var(--text-secondary); padding: 20px 0;">لا توجد تقييمات مكتوبة معتمدة لملفك الشخصي حتى الآن.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($reviews as $rev): ?>
                            <div style="border-bottom: 1px solid #f3f4f6; padding-bottom: 15px; text-align: right;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <strong style="color: var(--text-primary); font-size: 0.95rem;"><?= e($rev['name']) ?></strong>
                                    <span style="color: #fbbf24; font-size: 0.9rem; font-weight: 700;">
                                        <?= str_repeat('★', (int)$rev['rating']) ?><span style="color: #e5e7eb;"><?= str_repeat('★', 5 - (int)$rev['rating']) ?></span>
                                    </span>
                                </div>
                                <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;"><?= e($rev['comment']) ?></p>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 5px;"><?= e(date('Y-m-d', strtotime($rev['created_at']))) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(0.9); opacity: 0.8; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0.8; }
}
</style>
