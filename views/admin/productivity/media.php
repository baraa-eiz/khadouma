<?php
/**
 * media.php
 * Admin Centralized Media & Image Manager View
 */
?>

<div class="tabs-navigation" style="display: flex; gap: 8px; border-bottom: 2px solid var(--border-color); padding-bottom: 0px; margin-bottom: 25px;">
    <a href="/admin/productivity/quality" class="tab-btn" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--text-muted); border-bottom: 3px solid transparent; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        صحة وجودة البيانات
    </a>
    <a href="/admin/productivity/seo" class="tab-btn" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--text-muted); border-bottom: 3px solid transparent; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        إدارة الـ SEO الجماعية
    </a>
    <a href="/admin/productivity/media" class="tab-btn active" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--primary); border-bottom: 3px solid var(--primary); margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        مدير الوسائط والصور
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 25px; align-items: start;">
    
    <!-- LEFT COLUMN: IMAGES GALLERY GRID -->
    <div>
        <h3 style="margin: 0 0 15px 0; font-size: 18px;">معرض الصور والوسائط الفعالة</h3>
        
        <?php if (empty($images)): ?>
            <div style="text-align: center; padding: 40px; background: white; border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-muted);">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" style="margin-bottom: 12px; opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                <div style="font-weight: bold; font-size: 15px;">لا توجد صور مرفوعة في النظام حالياً!</div>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px;">
                <?php foreach ($images as $img): ?>
                    <div style="background: white; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                        <div style="position: relative; width: 100%; padding-top: 75%; background-color: #f1f5f9;">
                            <img src="<?= url($img['image_path']) ?>" 
                                 alt="<?= e($img['alt_text_ar'] ?? 'صورة مزود') ?>" 
                                 style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                            
                            <!-- Image type badge -->
                            <span class="badge" style="position: absolute; bottom: 8px; right: 8px; font-size: 9px; padding: 2px 6px; background: rgba(0,0,0,0.6); color: white; border: none;">
                                <?= $img['image_type'] === 'profile' ? 'شعار' : ($img['image_type'] === 'work_photo' ? 'معرض أعمال' : 'مستند توثيق') ?>
                            </span>
                        </div>
                        <div style="padding: 10px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 8px;">
                            <div>
                                <span style="font-weight: bold; font-size: 13px; color: var(--text-color); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= e($img['display_name_ar']) ?>">
                                    <?= e($img['display_name_ar']) ?>
                                </span>
                                <span style="font-size: 10px; color: var(--text-muted); font-family: monospace; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 2px;">
                                    <?= basename($img['image_path']) ?>
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 6px;">
                                <a href="<?= url($img['image_path']) ?>" target="_blank" style="font-size: 11px; color: var(--primary); text-decoration: none; font-weight: bold;">معاينة كاشفة</a>
                                <span style="font-size: 10px; color: var(--text-muted);"><?= date('Y/m/d', strtotime($img['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT COLUMN: ORPHANED FILES & CLEANUP PANEL -->
    <div>
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); position: sticky; top: 20px;">
            <h3 style="margin: 0 0 10px 0; font-size: 16px; color: var(--danger); display: flex; align-items: center; gap: 6px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                تنظيف المساحة التخزينية
            </h3>
            <p style="margin: 0 0 15px 0; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                الملفات اليتيمة هي صور موجودة فعلياً في مجلد الرفع (`uploads/`) ولكن لا تملك أي إشارات أو ارتباطات نشطة في قاعدة البيانات (ناتجة عن حذف مزودين، استبدال صور قديمة، أو إلغاء عمليات رفع).
            </p>

            <?php if (empty($orphanedFiles)): ?>
                <div style="text-align: center; padding: 20px; color: #10b981; background-color: #ecfdf5; border-radius: 8px; border: 1px solid #a7f3d0; font-size: 13px;">
                    <div style="font-weight: bold; margin-bottom: 4px;">القرص نظيف بالكامل!</div>
                    لا توجد ملفات يتيمة أو مهملة على الخادم.
                </div>
            <?php else: ?>
                <div style="background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px; margin-bottom: 15px;">
                    <div style="font-weight: bold; color: #b91c1c; font-size: 14px;">تم كشف: <?= count($orphanedFiles) ?> ملف يتيم</div>
                    <div style="font-size: 11px; color: #7f1d1d; margin-top: 3px;">حجم المساحة المهدورة سيتم استرجاعها فور الحذف.</div>
                </div>

                <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; margin-bottom: 15px; background-color: #fafafa; font-family: monospace; font-size: 11px; direction: ltr; text-align: left;">
                    <?php foreach ($orphanedFiles as $orph): ?>
                        <div style="color: #6b7280; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            &bull; public/<?= e($orph) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" action="/admin/productivity/media/clean" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف كافة الملفات غير المستخدمة نهائياً من القرص؟ لا يمكن التراجع عن هذا الإجراء.');" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background-color: #ef4444; border-color: #ef4444; padding: 10px; font-weight: bold;">
                        حذف كافة الملفات اليتيمة فوراً
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
</div>
