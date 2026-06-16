<?php
/**
 * quality.php
 * Admin Data Quality and Duplicate Detection View
 */
$descCoverage = $total > 0 ? round((($total - $missingDesc) / $total) * 100) : 100;
$logoCoverage = $total > 0 ? round((($total - $missingLogo) / $total) * 100) : 100;
$photosCoverage = $total > 0 ? round((($total - $missingPhotos) / $total) * 100) : 100;
$servicesCoverage = $total > 0 ? round((($total - $missingServices) / $total) * 100) : 100;
$areasCoverage = $total > 0 ? round((($total - $missingAreas) / $total) * 100) : 100;
$seoCoverage = $total > 0 ? round((($total - $missingSeo) / $total) * 100) : 100;

// Calculate overall score
$overallScore = round(($descCoverage + $logoCoverage + $photosCoverage + $servicesCoverage + $areasCoverage + $seoCoverage) / 6);
?>

<div class="tabs-navigation" style="display: flex; gap: 8px; border-bottom: 2px solid var(--border-color); padding-bottom: 0px; margin-bottom: 25px;">
    <a href="/admin/productivity/quality" class="tab-btn active" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--primary); border-bottom: 3px solid var(--primary); margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        صحة وجودة البيانات
    </a>
    <a href="/admin/productivity/seo" class="tab-btn" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--text-muted); border-bottom: 3px solid transparent; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        إدارة الـ SEO الجماعية
    </a>
    <a href="/admin/productivity/media" class="tab-btn" style="padding: 12px 24px; font-weight: bold; text-decoration: none; color: var(--text-muted); border-bottom: 3px solid transparent; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; font-size: 15px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        مدير الوسائط والصور
    </a>
</div>

<!-- DATA QUALITY OVERALL HEADER -->
<div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
    <div>
        <h2 style="margin: 0 0 8px 0; font-size: 24px; color: white;">مؤشر الصحة العام لقاعدة البيانات</h2>
        <p style="margin: 0; color: #c7d2fe; font-size: 14px; max-width: 600px;">
            توضح النسبة التالية مدى اكتمال وصحة البيانات المدخلة لمزودي الخدمات النشطين على المنصة. البيانات المكتملة تسهم في رفع مستويات التحويل وتحسين ظهور نتائج البحث في محركات البحث.
        </p>
    </div>
    <div style="text-align: center; background: rgba(255,255,255,0.08); padding: 15px 30px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.15);">
        <div style="font-size: 44px; font-weight: 900; line-height: 1; color: #818cf8;"><?= $overallScore ?>%</div>
        <div style="font-size: 12px; margin-top: 6px; color: #a5b4fc; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">درجة الصحة العامة</div>
    </div>
</div>

<!-- QUALITY METRICS GRID -->
<h3 style="margin-bottom: 15px; font-size: 18px;">مؤشرات صحة البيانات الفرعية</h3>
<div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 35px;">
    <!-- Metric 1: Description -->
    <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: bold; color: var(--text-color);">تغطية الوصف العربي</span>
                <span style="font-size: 13px; color: var(--text-muted);"><?= $total - $missingDesc ?> من <?= $total ?></span>
            </div>
            <div style="font-size: 28px; font-weight: bold; color: var(--text-color); margin-bottom: 12px;"><?= $descCoverage ?>%</div>
        </div>
        <div>
            <div style="height: 6px; background-color: #f1f5f9; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                <div style="width: <?= $descCoverage ?>%; height: 100%; background-color: #10b981;"></div>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);"><?= $missingDesc ?> مزودين يفتقرون للوصف التفصيلي</span>
        </div>
    </div>

    <!-- Metric 2: Logo -->
    <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: bold; color: var(--text-color);">تغطية شعار الملف</span>
                <span style="font-size: 13px; color: var(--text-muted);"><?= $total - $missingLogo ?> من <?= $total ?></span>
            </div>
            <div style="font-size: 28px; font-weight: bold; color: var(--text-color); margin-bottom: 12px;"><?= $logoCoverage ?>%</div>
        </div>
        <div>
            <div style="height: 6px; background-color: #f1f5f9; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                <div style="width: <?= $logoCoverage ?>%; height: 100%; background-color: #3b82f6;"></div>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);"><?= $missingLogo ?> ملف شخصي بدون شعار أو صورة</span>
        </div>
    </div>

    <!-- Metric 3: Photos -->
    <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: bold; color: var(--text-color);">معرض صور الأعمال</span>
                <span style="font-size: 13px; color: var(--text-muted);"><?= $total - $missingPhotos ?> من <?= $total ?></span>
            </div>
            <div style="font-size: 28px; font-weight: bold; color: var(--text-color); margin-bottom: 12px;"><?= $photosCoverage ?>%</div>
        </div>
        <div>
            <div style="height: 6px; background-color: #f1f5f9; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                <div style="width: <?= $photosCoverage ?>%; height: 100%; background-color: #8b5cf6;"></div>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);"><?= $missingPhotos ?> مزود بدون صور توضيحية لخدماتهم</span>
        </div>
    </div>

    <!-- Metric 4: Secondary Services -->
    <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: bold; color: var(--text-color);">الخدمات الثانوية والممتدة</span>
                <span style="font-size: 13px; color: var(--text-muted);"><?= $total - $missingServices ?> من <?= $total ?></span>
            </div>
            <div style="font-size: 28px; font-weight: bold; color: var(--text-color); margin-bottom: 12px;"><?= $servicesCoverage ?>%</div>
        </div>
        <div>
            <div style="height: 6px; background-color: #f1f5f9; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                <div style="width: <?= $servicesCoverage ?>%; height: 100%; background-color: #f59e0b;"></div>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);"><?= $missingServices ?> مزود لم يتم تعيين خدمات إضافية لهم</span>
        </div>
    </div>

    <!-- Metric 5: Areas Coverage -->
    <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: bold; color: var(--text-color);">تغطية مناطق الخدمة الفرعية</span>
                <span style="font-size: 13px; color: var(--text-muted);"><?= $total - $missingAreas ?> من <?= $total ?></span>
            </div>
            <div style="font-size: 28px; font-weight: bold; color: var(--text-color); margin-bottom: 12px;"><?= $areasCoverage ?>%</div>
        </div>
        <div>
            <div style="height: 6px; background-color: #f1f5f9; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                <div style="width: <?= $areasCoverage ?>%; height: 100%; background-color: #06b6d4;"></div>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);"><?= $missingAreas ?> مزود لم يحددوا مناطق تقديم الخدمة</span>
        </div>
    </div>

    <!-- Metric 6: SEO Tags -->
    <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: bold; color: var(--text-color);">تغطية الكلمات الدليلية (SEO)</span>
                <span style="font-size: 13px; color: var(--text-muted);"><?= $total - $missingSeo ?> من <?= $total ?></span>
            </div>
            <div style="font-size: 28px; font-weight: bold; color: var(--text-color); margin-bottom: 12px;"><?= $seoCoverage ?>%</div>
        </div>
        <div>
            <div style="height: 6px; background-color: #f1f5f9; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                <div style="width: <?= $seoCoverage ?>%; height: 100%; background-color: #ec4899;"></div>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);"><?= $missingSeo ?> مزود بدون بيانات ميتا مهيأة للبحث</span>
        </div>
    </div>
</div>

<!-- DUPLICATE DETECTION SECTIONS -->
<h3 style="margin-bottom: 15px; font-size: 18px;">كشف الازدواجية وتكرار البيانات</h3>
<div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 35px;">
    <!-- Duplicate Group by Phone -->
    <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
        <h4 style="margin: 0 0 10px 0; color: var(--text-color); display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--danger);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            تكرار أرقام الهواتف (<?= count($phoneDuplicates) ?> مكررات)
        </h4>
        <p style="margin: 0 20px 20px 0; color: var(--text-muted); font-size: 13px;">
            أرقام الهواتف المتشابهة غالباً ما تدل على تكرار تسجيل نفس مزود الخدمة. يوصى بدمج المكررات للحفاظ على جودة النتائج.
        </p>

        <?php if (empty($phoneDuplicates)): ?>
            <div style="text-align: center; padding: 30px; color: #10b981; background-color: #ecfdf5; border-radius: 8px;">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-bottom: 8px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div style="font-weight: bold;">لا توجد أي أرقام هواتف مكررة في النظام حالياً!</div>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php foreach ($phoneDuplicates as $group): 
                    $idsArr = explode(',', $group['ids']);
                    $namesArr = explode(' | ', $group['names']);
                ?>
                    <form method="POST" action="/admin/productivity/merge" style="border: 1px solid #fed7d7; background-color: #fffaf0; border-radius: 8px; padding: 15px; display: flex; flex-direction: column; gap: 12px; margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #fee2e2; padding-bottom: 8px;">
                            <span style="font-weight: bold; color: #b91c1c; font-family: monospace; font-size: 15px;">الهاتف: <?= e($group['phone']) ?></span>
                            <span class="badge badge-danger" style="background-color: #f87171; color: white;"><?= $group['cnt'] ?> مزودين مكررين</span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-color);">
                            <div style="margin-bottom: 6px; font-weight: bold;">اختر الملف الأساسي (الذي سيتم نقل كافة البيانات والتقييمات إليه):</div>
                            <div style="display: flex; flex-direction: column; gap: 6px; padding-right: 10px;">
                                <?php foreach ($idsArr as $index => $id): ?>
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="primary_id" value="<?= $id ?>" <?= $index === 0 ? 'checked' : '' ?> style="transform: scale(1.15);">
                                        <span style="font-weight: bold;"><?= e($namesArr[$index] ?? '') ?></span>
                                        <span style="font-size: 11px; color: var(--text-muted);">(معرّف النظام ID: <?= $id ?>)</span>
                                    </label>
                                    <input type="hidden" name="duplicate_ids[]" value="<?= $id ?>">
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: flex-end; margin-top: 5px;">
                            <button type="submit" class="btn btn-primary" style="padding: 6px 16px; font-size: 13px; background-color: #e11d48; border-color: #e11d48;">
                                دمج المكررات وحذف الإضافية
                            </button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- DATA QUALITY ISSUES / LOW ACCURACY PROVIDERS -->
<div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
    <h3 style="margin: 0 0 10px 0; color: var(--text-color); font-size: 18px; display: flex; align-items: center; gap: 8px;">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: var(--warning);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        ملفات شخصية ضعيفة الاكتمال (صحة الملف &lt; 60%)
    </h3>
    <p style="margin: 0 0 20px 0; color: var(--text-muted); font-size: 13px;">
        تفتقد هذه الملفات إلى حقول هامة مثل الوصف التفصيلي، شعار الملف، معرض الصور أو الكلمات الدلالية.
    </p>

    <div class="table-container">
        <?php if (empty($lowCompletionProviders)): ?>
            <div style="text-align: center; padding: 30px; color: #10b981; background-color: #ecfdf5; border-radius: 8px;">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="margin-bottom: 8px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div style="font-weight: bold;">جميع الملفات الشخصية تتمتع بنسبة اكتمال جيدة وتتجاوز 60%!</div>
            </div>
        <?php else: ?>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">معرّف ID</th>
                        <th>مزود الخدمة</th>
                        <th>الهاتف</th>
                        <th style="text-align: center;">صحة الملف</th>
                        <th style="width: 150px; text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowCompletionProviders as $p): ?>
                        <tr>
                            <td style="text-align: center; font-weight: bold;"><?= $p['id'] ?></td>
                            <td style="font-weight: bold; color: var(--text-color);"><?= e($p['display_name_ar']) ?></td>
                            <td style="direction: ltr; text-align: right;"><?= e($p['phone']) ?></td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <div class="progress-bar-container" style="width: 80px; height: 6px; background-color: #f1f5f9; border-radius: 3px; overflow: hidden;">
                                        <div style="width: <?= (int)$p['completion_score'] ?>%; height: 100%; background-color: #f59e0b;"></div>
                                    </div>
                                    <span style="font-size: 12px; font-weight: bold; color: #d97706;"><?= (int)$p['completion_score'] ?>%</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <a href="/admin/providers/<?= $p['id'] ?>/edit" class="btn btn-secondary" style="padding: 4px 12px; font-size: 12px; color: var(--primary);">
                                    إكمال الملف
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
