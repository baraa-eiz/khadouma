<?php
/**
 * dashboard.php
 * Child view content for the Admin Dashboard.
 * Expects variables:
 *  - string $admin_name
 *  - string $app_version
 *  - string $app_env
 */
?>
<div class="section-header">
    <h1 class="section-title">لوحة التحكم</h1>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--spacing-lg);">
    <!-- Left Column: Welcome & Info -->
    <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        <div class="card">
            <div class="card-body" style="padding: var(--spacing-xl);">
                <h2 style="font-size: 22px; font-weight: 700; color: var(--primary); margin-bottom: 8px;">
                    مرحباً بك، <?= e($admin_name) ?>!
                </h2>
                <p style="color: var(--text-secondary); font-size: 15px; line-height: 1.7; max-width: 600px;">
                    تم إعداد وتشغيل منصة الإدارة الأساسية لـ <strong>خدومة (خيار الدليل الخدمي السوري)</strong>. 
                    من هنا، يمكنك مستقبلاً إدارة مدخلات النظام من مزودي خدمات، تصنيفات، مناطق جغرافية، مراجعات العملاء، وحل الشكاوى المقدمة.
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">روابط سريعة للإجراءات القادمة</h3>
            </div>
            <div class="card-body" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-md);">
                <a href="#" class="btn btn-secondary" style="justify-content: flex-start; padding: var(--spacing-md);" onclick="event.preventDefault(); alert('هذا القسم سيتم تفعيله في المرحلة القادمة.');">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left: 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    إدارة مزودي الخدمات
                </a>
                <a href="#" class="btn btn-secondary" style="justify-content: flex-start; padding: var(--spacing-md);" onclick="event.preventDefault(); alert('هذا القسم سيتم تفعيله في المرحلة القادمة.');">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left: 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    إدارة التصنيفات والخدمات
                </a>
                <a href="#" class="btn btn-secondary" style="justify-content: flex-start; padding: var(--spacing-md);" onclick="event.preventDefault(); alert('هذا القسم سيتم تفعيله في المرحلة القادمة.');">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left: 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5A2.5 2.5 0 0119 14.5v.5a2.5 2.5 0 01-2.5 2.5H14M9 21h3m-3 0a9 9 0 119-9m-9 9a9 9 0 01-9-9"/></svg>
                    إدارة المدن والمناطق
                </a>
                <a href="#" class="btn btn-secondary" style="justify-content: flex-start; padding: var(--spacing-md);" onclick="event.preventDefault(); alert('هذا القسم سيتم تفعيله في المرحلة القادمة.');">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left: 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    تعديل إعدادات الموقع
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: System Details & Health status -->
    <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">معلومات النظام الأساسي</h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">إصدار التطبيق:</span>
                    <span class="badge badge-info">v<?= e($app_version) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">بيئة التشغيل:</span>
                    <span class="badge <?= ($app_env === 'development') ? 'badge-warning' : 'badge-success' ?>">
                        <?= e($app_env) ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">لغة العرض:</span>
                    <span>العربية (RTL)</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 4px;">
                    <span style="color: var(--text-secondary);">حالة الخادم الفنية:</span>
                    <span class="badge badge-success" style="display: flex; align-items: center; gap: 4px;">
                        <span style="width: 8px; height: 8px; background-color: var(--success); border-radius: 50%;"></span>
                        مستقرة
                    </span>
                </div>
                
                <?php if ($app_env === 'development'): ?>
                    <a href="/health" class="btn btn-secondary" style="width: 100%; margin-top: 8px; font-size: 13px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left: 6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        زيارة صفحة تشخيص النظام /health
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
