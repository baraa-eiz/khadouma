<?php
/**
 * compare.php
 * Admin side-by-side draft comparison and review dashboard.
 */

// Helper to determine row styling and highlight diffs
function render_diff_row(string $fieldName, $liveVal, $draftVal, bool $isImage = false) {
    $isDiff = false;
    
    if (is_array($liveVal) && is_array($draftVal)) {
        // Compare arrays
        sort($liveVal);
        sort($draftVal);
        $isDiff = ($liveVal !== $draftVal);
    } else {
        $isDiff = ((string)$liveVal !== (string)$draftVal);
    }

    $rowBg = $isDiff ? 'background-color: #fffbeb;' : '';
    $liveStyle = $isDiff ? 'background-color: #fee2e2; color: #991b1b; padding: 4px; border-radius: 4px;' : '';
    $draftStyle = $isDiff ? 'background-color: #d1fae5; color: #065f46; padding: 4px; border-radius: 4px; font-weight: bold;' : '';
    
    echo '<tr style="border-bottom: 1px solid var(--border-color); ' . $rowBg . '">';
    echo '<td style="padding: 12px; font-weight: 800; width: 20%; color: var(--text-primary);">' . $fieldName . '</td>';
    
    // Render Live Column
    echo '<td style="padding: 12px; width: 40%; font-size: 0.9rem;">';
    if ($isImage) {
        if ($liveVal) {
            echo '<img src="' . url($liveVal) . '" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">';
        } else {
            echo '<span style="color: #9ca3af; font-style: italic;">لا توجد صورة</span>';
        }
    } else {
        if (is_array($liveVal)) {
            echo '<span style="' . $liveStyle . '">' . (!empty($liveVal) ? implode('، ', array_map('e', $liveVal)) : 'لا يوجد') . '</span>';
        } else {
            echo '<span style="' . $liveStyle . '">' . ($liveVal !== null && $liveVal !== '' ? e($liveVal) : 'لا يوجد') . '</span>';
        }
    }
    echo '</td>';

    // Render Draft Column
    echo '<td style="padding: 12px; width: 40%; font-size: 0.9rem;">';
    if ($isImage) {
        if ($draftVal) {
            echo '<img src="' . url($draftVal) . '" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #10b981; box-shadow: 0 0 5px rgba(16,185,129,0.3);">';
        } else {
            echo '<span style="color: #9ca3af; font-style: italic;">لا توجد صورة</span>';
        }
    } else {
        if (is_array($draftVal)) {
            echo '<span style="' . $draftStyle . '">' . (!empty($draftVal) ? implode('، ', array_map('e', $draftVal)) : 'لا يوجد') . '</span>';
        } else {
            echo '<span style="' . $draftStyle . '">' . ($draftVal !== null && $draftVal !== '' ? e($draftVal) : 'لا يوجد') . '</span>';
        }
    }
    echo '</td>';
    echo '</tr>';
}
?>

<div class="row">
    <!-- Header Summary Alert -->
    <div class="col-12" style="margin-bottom: 20px;">
        <!-- Flash messages -->
        <?php include dirname(dirname(dirname(dirname(__DIR__)))) . '/views/components/flash.php'; ?>
        
        <div class="card" style="padding: 20px; border-radius: 12px; background-color: #f8fafc; border: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h4 style="margin: 0; font-weight: 800; color: var(--text-primary);">
                        <?php if ($liveProvider): ?>
                            ⚙️ مراجعة طلب تحديث تفاصيل الملف: <?= e($liveProvider['display_name_ar']) ?>
                        <?php else: ?>
                            🆕 مراجعة طلب تسجيل حساب حرفي جديد: <?= e($draft['display_name_ar']) ?>
                        <?php endif; ?>
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px; margin-bottom: 0;">
                        مقدم الطلب: <strong><?= e($draft['account_name'] ?? 'غير معروف') ?></strong> (<?= e($draft['account_email'] ?? '') ?>)
                    </p>
                </div>
                <div>
                    <a href="<?= url('admin/providers/drafts') ?>" class="btn btn-secondary btn-sm" style="font-weight: 700;">
                        ← العودة لطلبات المراجعة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Table Card -->
    <div class="col-12" style="margin-bottom: 30px;">
        <div class="card" style="padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); background: #fff;">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; font-weight: 800; color: var(--text-primary);">🔎 مقارنة التحديثات جنباً إلى جنب مع البيانات المنشورة</h3>
            
            <div class="table-responsive">
                <table class="table table-bordered" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f1f5f9; border-bottom: 2px solid var(--border-color);">
                            <th style="padding: 12px; font-weight: 800; text-align: right; width: 20%;">الحقل</th>
                            <th style="padding: 12px; font-weight: 800; text-align: right; width: 40%;">النسخة المنشورة حالياً</th>
                            <th style="padding: 12px; font-weight: 800; text-align: right; width: 40%; background-color: #ecfdf5; color: #065f46;">المسودة المقترحة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Format complex values for presentation
                        $liveCity = $liveProvider && isset($cities[$liveProvider['city_id']]) ? $cities[$liveProvider['city_id']] : '';
                        $draftCity = isset($cities[$draft['city_id']]) ? $cities[$draft['city_id']] : '';

                        $liveService = $liveProvider && isset($services[$liveProvider['primary_service_id']]) ? $services[$liveProvider['primary_service_id']] : '';
                        $draftService = isset($services[$draft['primary_service_id']]) ? $services[$draft['primary_service_id']] : '';

                        // secondary services lists
                        $liveSecServices = [];
                        if ($liveProvider && !empty($liveProvider['services'])) {
                            foreach ($liveProvider['services'] as $s) {
                                $liveSecServices[] = $s['display_name_ar'];
                            }
                        }
                        $draftSecServices = [];
                        if (!empty($draft['secondary_services_json'])) {
                            foreach ($draft['secondary_services_json'] as $sid) {
                                if (isset($services[$sid])) {
                                    $draftSecServices[] = $services[$sid];
                                }
                            }
                        }

                        // coverage areas lists
                        $liveAreas = [];
                        if ($liveProvider && !empty($liveProvider['areas'])) {
                            foreach ($liveProvider['areas'] as $a) {
                                $liveAreas[] = $a['display_name_ar'];
                            }
                        }
                        $draftAreas = [];
                        if (!empty($draft['coverage_areas_json'])) {
                            foreach ($draft['coverage_areas_json'] as $aid) {
                                if (isset($areas[$aid])) {
                                    $draftAreas[] = $areas[$aid];
                                }
                            }
                        }

                        // social links formatter
                        $liveSocial = [];
                        if ($liveProvider && !empty($liveProvider['social_links'])) {
                            foreach ($liveProvider['social_links'] as $k => $v) {
                                if ($v) $liveSocial[] = "$k: $v";
                            }
                        }
                        $draftSocial = [];
                        if (!empty($draft['social_links'])) {
                            foreach ($draft['social_links'] as $k => $v) {
                                if ($v) $draftSocial[] = "$k: $v";
                            }
                        }

                        // Call helper for all fields
                        render_diff_row('الاسم التجاري (عربي)', $liveProvider['display_name_ar'] ?? '', $draft['display_name_ar']);
                        render_diff_row('رابط مسار الملف (Slug)', $liveProvider['slug'] ?? '', $draft['slug']);
                        render_diff_row('نوع العمل', $liveProvider ? ($liveProvider['business_type'] === 'company' ? 'شركة / ورشة' : 'حرفي مستقل') : '', $draft['business_type'] === 'company' ? 'شركة / ورشة' : 'حرفي مستقل');
                        render_diff_row('رقم الهاتف', $liveProvider['phone'] ?? '', $draft['phone']);
                        render_diff_row('رقم الواتساب', $liveProvider['whatsapp'] ?? '', $draft['whatsapp']);
                        render_diff_row('البريد الإلكتروني', $liveProvider['email'] ?? '', $draft['email']);
                        render_diff_row('المدينة الرئيسية', $liveCity, $draftCity);
                        render_diff_row('الخدمة/المهنة الرئيسية', $liveService, $draftService);
                        render_diff_row('الخدمات الثانوية', $liveSecServices, $draftSecServices);
                        render_diff_row('مناطق التغطية بالتفصيل', $liveAreas, $draftAreas);
                        render_diff_row('الوصف المختصر', $liveProvider['short_description_ar'] ?? '', $draft['short_description_ar']);
                        render_diff_row('الوصف التفصيلي للخبرات', $liveProvider['description_ar'] ?? '', $draft['description_ar']);
                        render_diff_row('سنوات الخبرة', $liveProvider['years_experience'] ?? '', $draft['years_experience']);
                        render_diff_row('بداية السعر المتوقع', $liveProvider['starting_price'] ?? '', $draft['starting_price']);
                        render_diff_row('وحدة التسعير', $liveProvider ? ($liveProvider['price_unit'] === 'hour' ? 'ساعة' : ($liveProvider['price_unit'] === 'job' ? 'خدمة' : 'يوم')) : '', $draft['price_unit'] === 'hour' ? 'ساعة' : ($draft['price_unit'] === 'job' ? 'خدمة' : 'يوم'));
                        render_diff_row('الموقع الإلكتروني', $liveProvider['website'] ?? '', $draft['website']);
                        render_diff_row('ساعات العمل', $liveProvider['working_hours'] ?? '', $draft['working_hours']);
                        render_diff_row('روابط التواصل الاجتماعي', $liveSocial, $draftSocial);
                        
                        // Media Logo/Avatar
                        $liveLogo = $liveProvider && isset($liveProvider['logo']) ? $liveProvider['logo'] : null;
                        render_diff_row('صورة الشعار / الملف', $liveLogo, $draft['logo_path'], true);

                        // Work Photos Gallery Diff
                        $liveGallery = [];
                        if ($liveProvider && !empty($liveProvider['work_photos'])) {
                            foreach ($liveProvider['work_photos'] as $wp) {
                                $liveGallery[] = $wp['image_path'];
                            }
                        }
                        $draftGallery = $draft['work_photos_json'] ?? [];
                        ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px; font-weight: 800; color: var(--text-primary);">معرض صور الأعمال</td>
                            <td style="padding: 12px;">
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php if (empty($liveGallery)): ?>
                                        <span style="color: #9ca3af; font-style: italic;">لا توجد صور معرض</span>
                                    <?php else: ?>
                                        <?php foreach ($liveGallery as $path): ?>
                                            <img src="<?= url($path) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding: 12px; background-color: #ecfdf5;">
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php if (empty($draftGallery)): ?>
                                        <span style="color: #9ca3af; font-style: italic;">لا توجد صور معرض بالمسودة</span>
                                    <?php else: ?>
                                        <?php foreach ($draftGallery as $path): ?>
                                            <img src="<?= url($path) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #10b981;">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <?php
                        render_diff_row('عنوان SEO (Title)', $liveProvider['meta_title_ar'] ?? '', $draft['meta_title_ar']);
                        render_diff_row('وصف SEO (Description)', $liveProvider['meta_description_ar'] ?? '', $draft['meta_description_ar']);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Review Decision Form Box -->
    <div class="col-12" style="margin-bottom: 50px;">
        <div class="card" style="padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); background: #f8fafc;">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; font-weight: 800; color: var(--text-primary);">⚖️ اتخاذ القرار والموافقة على النشر</h3>
            
            <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start;">
                
                <!-- Accept Section -->
                <div style="flex: 1; min-width: 280px; background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #d1fae5; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <h4 style="color: #065f46; font-weight: 800; margin-top: 0; margin-bottom: 10px;">🟢 قبول ونشر التغييرات</h4>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 20px;">
                        بالموافقة على هذا الطلب، سيتم نسخ كافة محتويات المسودة وأعمال المعرض والشعار إلى ملف مزود الخدمة المنشور، ويتم تحديثها وعرضها للعملاء على الفور.
                    </p>
                    
                    <form action="<?= url("admin/providers/drafts/{$draft['id']}/approve") ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="btn btn-success" style="width: 100%; font-weight: 800; padding: 10px; font-size: 0.95rem;">
                            ✅ اعتماد وموافقة على النشر الفوري
                        </button>
                    </form>
                </div>

                <!-- Reject Section -->
                <div style="flex: 1; min-width: 280px; background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #fee2e2; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <h4 style="color: #991b1b; font-weight: 800; margin-top: 0; margin-bottom: 10px;">🔴 رفض الطلب والمطالبة بتعديلات</h4>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 15px;">
                        برفض التعديلات، سيتم إرجاع حالة الملف للحرفي ليتمكن من التعديل مجدداً. يجب كتابة سبب الرفض لتوجيه الحرفي وتوضيح المطلوب.
                    </p>
                    
                    <form action="<?= url("admin/providers/drafts/{$draft['id']}/reject") ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label class="form-label" style="font-weight: 700;">سبب الرفض والتوجيهات المهنية <span style="color:red;">*</span></label>
                            <textarea name="rejection_reason" rows="3" class="form-control" placeholder="مثال: يرجى استبدال صورة الشعار بصورة مهنية وتحديد السعر بصورة حقيقية..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger" style="width: 100%; font-weight: 800; padding: 10px; font-size: 0.95rem;">
                            ❌ رفض التعديلات وإعادتها للحرفي
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
