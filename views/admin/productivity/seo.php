<?php
/**
 * seo.php
 * Admin SEO Metadata Bulk Management View
 */
?>

<div class="tabs-navigation" style="display: flex; gap: 8px; border-bottom: 2px solid var(--border-color); padding-bottom: 0px; margin-bottom: 25px;">
    <a href="/admin/productivity/quality" class="tab-btn" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--text-muted); border-bottom: 3px solid transparent; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        صحة وجودة البيانات
    </a>
    <a href="/admin/productivity/seo" class="tab-btn active" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--primary); border-bottom: 3px solid var(--primary); margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        إدارة الـ SEO الجماعية
    </a>
    <a href="/admin/productivity/media" class="tab-btn" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--text-muted); border-bottom: 3px solid transparent; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        مدير الوسائط والصور
    </a>
</div>

<div class="section-header" style="margin-bottom: 20px;">
    <div>
        <h2 class="section-title">إدارة تهيئة محركات البحث (SEO)</h2>
        <p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 13px;">
            تعديل كلمات الميتا الدليلية لجميع مزودي الخدمات. يمكنك النقر فوق الحقول وحفظها مباشرة دون مغادرة الصفحة.
        </p>
    </div>

    <!-- Bulk Action Auto-Generate -->
    <form method="POST" action="/admin/productivity/seo/auto" style="margin: 0;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; background-color: var(--primary);">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            توليد الكلمات الدليلية الناقصة تلقائياً
        </button>
    </form>
</div>

<!-- DATA GRID FOR SEO METADATA -->
<div class="table-container" style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="width: 250px;">مزود الخدمة</th>
                <th>عنوان الميتا (Meta Title)</th>
                <th>وصف الميتا (Meta Description)</th>
                <th style="width: 100px; text-align: center;">الحالة</th>
                <th style="width: 120px; text-align: center;">الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($providers as $p): 
                $hasSeo = !empty($p['meta_title_ar']) && !empty($p['meta_description_ar']);
            ?>
                <tr id="row-<?= $p['id'] ?>">
                    <td>
                        <div style="font-weight: bold; color: var(--text-color); font-size: 14px;"><?= e($p['display_name_ar']) ?></div>
                        <span style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 3px;"><?= e($p['service_name']) ?> / <?= e($p['city_name']) ?></span>
                        <a href="/provider/<?= e($p['slug']) ?>" target="_blank" style="font-size: 11px; color: var(--primary); text-decoration: none; display: inline-block; margin-top: 2px;">/provider/<?= e($p['slug']) ?> &nearr;</a>
                    </td>
                    <td>
                        <input type="text" 
                               id="title-<?= $p['id'] ?>" 
                               value="<?= e($p['meta_title_ar'] ?? '') ?>" 
                               class="form-control" 
                               placeholder="تلقائي: <?= e($p['display_name_ar'] . ' - ' . $p['service_name'] . ' في ' . $p['city_name']) ?>" 
                               style="width: 100%; font-size: 13px; padding: 6px 10px;">
                    </td>
                    <td>
                        <textarea id="desc-<?= $p['id'] ?>" 
                                  class="form-control" 
                                  placeholder="تلقائي: هل تبحث عن..." 
                                  rows="2" 
                                  style="width: 100%; font-size: 12px; padding: 6px 10px; resize: vertical; min-height: 50px;"><?= e($p['meta_description_ar'] ?? '') ?></textarea>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <span id="badge-<?= $p['id'] ?>" class="badge <?= $hasSeo ? 'badge-success' : 'badge-danger' ?>" style="font-size: 11px; padding: 3px 8px;">
                            <?= $hasSeo ? 'مكتمل' : 'ناقص' ?>
                        </span>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <button type="button" 
                                onclick="saveSeoRow(<?= $p['id'] ?>)" 
                                id="btn-<?= $p['id'] ?>" 
                                class="btn btn-secondary" 
                                style="padding: 6px 14px; font-size: 13px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            حفظ
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- AJAX SCRIPT TO SAVE SEO ROWS -->
<script>
function saveSeoRow(id) {
    const titleInput = document.getElementById('title-' + id);
    const descTextarea = document.getElementById('desc-' + id);
    const btn = document.getElementById('btn-' + id);
    const badge = document.getElementById('badge-' + id);

    const titleValue = titleInput.value.trim();
    const descValue = descTextarea.value.trim();

    // Disable button during request
    btn.disabled = true;
    btn.textContent = 'جاري...';

    // Build Form Data
    const formData = new FormData();
    formData.append('provider_id', id);
    formData.append('meta_title_ar', titleValue);
    formData.append('meta_description_ar', descValue);
    formData.append('csrf_token', '<?= csrf_token() ?>');

    fetch('/admin/productivity/seo/save', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg> حفظ';
        
        if (data.success) {
            // Flash color green on row
            const row = document.getElementById('row-' + id);
            row.style.backgroundColor = '#ecfdf5';
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 1000);

            // Update badge status
            if (titleValue !== '' && descValue !== '') {
                badge.className = 'badge badge-success';
                badge.textContent = 'مكتمل';
            } else {
                badge.className = 'badge badge-danger';
                badge.textContent = 'ناقص';
            }
        } else {
            alert('حدث خطأ أثناء الحفظ: ' + data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = 'حفظ';
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال بالخادم.');
    });
}
</script>
