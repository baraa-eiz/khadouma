<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Core\Database;
use App\Core\View;
use App\Core\Config;

class ProductivityController extends Controller
{
    /**
     * Quality Report & Duplicate Detection Dashboard.
     */
    public function qualityReport(Request $request): Response
    {
        $db = Database::getInstance();

        // 1. Data Quality Metrics
        // Total providers
        $total = (int)$db->fetchColumn("SELECT COUNT(*) FROM `providers` WHERE `deleted_at` IS NULL");
        
        // Missing descriptions
        $missingDesc = (int)$db->fetchColumn("SELECT COUNT(*) FROM `providers` WHERE `deleted_at` IS NULL AND (`description_ar` IS NULL OR TRIM(`description_ar`) = '')");
        
        // Missing profile picture (logo)
        $missingLogo = (int)$db->fetchColumn("
            SELECT COUNT(*) FROM `providers` p 
            WHERE p.deleted_at IS NULL AND NOT EXISTS (
                SELECT 1 FROM `provider_images` pi 
                WHERE pi.provider_id = p.id AND pi.image_type = 'profile' AND pi.deleted_at IS NULL
            )
        ");

        // Missing work photos (gallery)
        $missingPhotos = (int)$db->fetchColumn("
            SELECT COUNT(*) FROM `providers` p 
            WHERE p.deleted_at IS NULL AND NOT EXISTS (
                SELECT 1 FROM `provider_images` pi 
                WHERE pi.provider_id = p.id AND pi.image_type = 'work_photo' AND pi.deleted_at IS NULL
            )
        ");

        // Missing secondary services map
        $missingServices = (int)$db->fetchColumn("
            SELECT COUNT(*) FROM `providers` p 
            WHERE p.deleted_at IS NULL AND NOT EXISTS (
                SELECT 1 FROM `provider_service_map` psm WHERE psm.provider_id = p.id
            )
        ");

        // Missing areas map
        $missingAreas = (int)$db->fetchColumn("
            SELECT COUNT(*) FROM `providers` p 
            WHERE p.deleted_at IS NULL AND NOT EXISTS (
                SELECT 1 FROM `provider_area_map` pam WHERE pam.provider_id = p.id
            )
        ");

        // Missing SEO metadata
        $missingSeo = (int)$db->fetchColumn("
            SELECT COUNT(*) FROM `providers` p 
            WHERE p.deleted_at IS NULL AND (
                NOT EXISTS (SELECT 1 FROM `seo_metadata` sm WHERE sm.entity_type = 'provider' AND sm.entity_id = p.id)
                OR EXISTS (SELECT 1 FROM `seo_metadata` sm WHERE sm.entity_type = 'provider' AND sm.entity_id = p.id AND (sm.meta_title_ar IS NULL OR sm.meta_title_ar = ''))
            )
        ");

        // 2. Duplicate Detection
        // Group by phone
        $phoneDuplicates = $db->fetchAll("
            SELECT `phone`, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(display_name_ar SEPARATOR ' | ') as names
            FROM `providers`
            WHERE `deleted_at` IS NULL
            GROUP BY `phone`
            HAVING cnt > 1
        ");

        // Group by exact Arabic display name
        $nameDuplicates = $db->fetchAll("
            SELECT `display_name_ar`, COUNT(*) as cnt, GROUP_CONCAT(id) as ids, GROUP_CONCAT(phone SEPARATOR ' | ') as phones
            FROM `providers`
            WHERE `deleted_at` IS NULL
            GROUP BY `display_name_ar`
            HAVING cnt > 1
        ");

        // 3. Let's fetch list of providers with low completion score (< 60)
        // Virtual SQL completion score calculation
        $lowCompletionProviders = $db->fetchAll("
            SELECT id, display_name_ar, phone,
            (
                (CASE WHEN description_ar IS NOT NULL AND TRIM(description_ar) != '' THEN 25 ELSE 0 END) +
                (CASE WHEN EXISTS (SELECT 1 FROM provider_images pi WHERE pi.provider_id = p.id AND pi.image_type = 'profile' AND pi.deleted_at IS NULL) THEN 25 ELSE 0 END) +
                (CASE WHEN EXISTS (SELECT 1 FROM provider_images pi WHERE pi.provider_id = p.id AND pi.image_type = 'work_photo' AND pi.deleted_at IS NULL) THEN 20 ELSE 0 END) +
                (CASE WHEN EXISTS (SELECT 1 FROM provider_area_map pam WHERE pam.provider_id = p.id) THEN 15 ELSE 0 END) +
                (CASE WHEN EXISTS (SELECT 1 FROM seo_metadata sm WHERE sm.entity_type = 'provider' AND sm.entity_id = p.id AND sm.meta_title_ar IS NOT NULL AND sm.meta_title_ar != '') THEN 15 ELSE 0 END)
            ) as completion_score
            FROM `providers` p
            WHERE p.deleted_at IS NULL
            HAVING completion_score < 60
            ORDER BY completion_score ASC
            LIMIT 10
        ");

        $viewData = [
            'total' => $total,
            'missingDesc' => $missingDesc,
            'missingLogo' => $missingLogo,
            'missingPhotos' => $missingPhotos,
            'missingServices' => $missingServices,
            'missingAreas' => $missingAreas,
            'missingSeo' => $missingSeo,
            'phoneDuplicates' => $phoneDuplicates,
            'nameDuplicates' => $nameDuplicates,
            'lowCompletionProviders' => $lowCompletionProviders,
            'active_tab' => 'quality'
        ];

        $content = View::render('admin/productivity/quality', $viewData);

        return $this->render('layouts/admin', [
            'title' => 'جودة البيانات وكشف التكرار',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'أدوات الإنتاجية', 'url' => '/admin/productivity/quality'],
                ['label' => 'صحة وجودة البيانات']
            ]
        ]);
    }

    /**
     * Merge multiple duplicate providers into a single primary provider.
     */
    public function mergeProviders(Request $request): Response
    {
        $this->validateCsrf($request);

        $primaryId = (int)$request->input('primary_id');
        $duplicateIds = $request->input('duplicate_ids');

        if (empty($primaryId) || empty($duplicateIds) || !is_array($duplicateIds)) {
            Flash::error('الرجاء تحديد المزود الأساسي والمزودات المكررة للدمج.');
            $this->redirect('/admin/productivity/quality');
            return new Response();
        }

        $duplicateIds = array_map('intval', array_diff($duplicateIds, [$primaryId]));

        if (empty($duplicateIds)) {
            Flash::error('الرجاء تحديد مزود مكرر واحد على الأقل يختلف عن المزود الأساسي.');
            $this->redirect('/admin/productivity/quality');
            return new Response();
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Fetch primary provider info
            $primary = $db->fetch("SELECT * FROM `providers` WHERE `id` = ? AND `deleted_at` IS NULL", [$primaryId]);
            if (!$primary) {
                $db->rollBack();
                Flash::error('المزود الأساسي غير موجود.');
                $this->redirect('/admin/productivity/quality');
                return new Response();
            }

            // Move reviews to primary
            $db->execute("UPDATE `reviews` SET `provider_id` = ? WHERE `provider_id` IN (" . implode(',', $duplicateIds) . ")", [$primaryId]);

            // Move reports to primary
            $db->execute("UPDATE `reports` SET `provider_id` = ? WHERE `provider_id` IN (" . implode(',', $duplicateIds) . ")", [$primaryId]);

            // Move contact events to primary
            $db->execute("UPDATE `contact_events` SET `provider_id` = ? WHERE `provider_id` IN (" . implode(',', $duplicateIds) . ")", [$primaryId]);

            // Move images
            $db->execute("UPDATE `provider_images` SET `provider_id` = ? WHERE `provider_id` IN (" . implode(',', $duplicateIds) . ")", [$primaryId]);

            // Move service map
            foreach ($duplicateIds as $dupId) {
                $dupServices = $db->fetchAll("SELECT service_id FROM `provider_service_map` WHERE `provider_id` = ?", [$dupId]);
                foreach ($dupServices as $ds) {
                    $exists = $db->fetch("SELECT 1 FROM `provider_service_map` WHERE `provider_id` = ? AND `service_id` = ?", [$primaryId, $ds['service_id']]);
                    if (!$exists) {
                        $db->execute("INSERT INTO `provider_service_map` (`provider_id`, `service_id`) VALUES (?, ?)", [$primaryId, $ds['service_id']]);
                    }
                }
            }

            // Move areas map
            foreach ($duplicateIds as $dupId) {
                $dupAreas = $db->fetchAll("SELECT area_id FROM `provider_area_map` WHERE `provider_id` = ?", [$dupId]);
                foreach ($dupAreas as $da) {
                    $exists = $db->fetch("SELECT 1 FROM `provider_area_map` WHERE `provider_id` = ? AND `area_id` = ?", [$primaryId, $da['area_id']]);
                    if (!$exists) {
                        $db->execute("INSERT INTO `provider_area_map` (`provider_id`, `area_id`) VALUES (?, ?)", [$primaryId, $da['area_id']]);
                    }
                }
            }

            // Copy missing details (like description or logo/photos flag) to primary if empty
            foreach ($duplicateIds as $dupId) {
                $dupData = $db->fetch("SELECT * FROM `providers` WHERE `id` = ?", [$dupId]);
                if ($dupData) {
                    $updates = [];
                    $params = [];
                    
                    if (empty($primary['description_ar']) && !empty($dupData['description_ar'])) {
                        $updates[] = "`description_ar` = :desc";
                        $params['desc'] = $dupData['description_ar'];
                        $primary['description_ar'] = $dupData['description_ar']; // update local cache
                    }
                    if (empty($primary['short_description_ar']) && !empty($dupData['short_description_ar'])) {
                        $updates[] = "`short_description_ar` = :short";
                        $params['short'] = $dupData['short_description_ar'];
                        $primary['short_description_ar'] = $dupData['short_description_ar'];
                    }
                    if (empty($primary['whatsapp']) && !empty($dupData['whatsapp'])) {
                        $updates[] = "`whatsapp` = :wa";
                        $params['wa'] = $dupData['whatsapp'];
                        $primary['whatsapp'] = $dupData['whatsapp'];
                    }

                    if (!empty($updates)) {
                        $params['id'] = $primaryId;
                        $db->execute("UPDATE `providers` SET " . implode(', ', $updates) . " WHERE `id` = :id", $params);
                    }
                }
            }

            // Recalculate average rating and reviews count for primary
            $ratingData = $db->fetch("
                SELECT COUNT(*) as cnt, COALESCE(AVG(rating), 0) as avg_rating 
                FROM `reviews` 
                WHERE `provider_id` = ? AND `is_approved` = 1 AND `deleted_at` IS NULL
            ", [$primaryId]);
            
            $db->execute("
                UPDATE `providers` 
                SET `reviews_count` = ?, `rating` = ? 
                WHERE `id` = ?
            ", [$ratingData['cnt'], $ratingData['avg_rating'], $primaryId]);

            // Soft delete the duplicates
            $db->execute("
                UPDATE `providers` 
                SET `deleted_at` = CURRENT_TIMESTAMP, `is_active` = 0, `status` = 'rejected' 
                WHERE `id` IN (" . implode(',', $duplicateIds) . ")
            ");

            // Audit log
            $adminUserId = (int)Session::get('admin_user_id');
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $db->execute(
                "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `new_value_json`, `ip_hash`) 
                 VALUES (:admin_id, 'merge_providers', 'providers', :primary, :val, :ip)",
                [
                    'admin_id' => $adminUserId,
                    'primary' => $primaryId,
                    'val' => json_encode(['merged_duplicate_ids' => $duplicateIds]),
                    'ip' => hash('sha256', $ipAddress)
                ]
            );

            $db->commit();
            Flash::success("تمت عملية دمج " . count($duplicateIds) . " مكررات بنجاح مع المزود الأساسي: " . $primary['display_name_ar']);
        } catch (\Throwable $e) {
            $db->rollBack();
            Flash::error("حدث خطأ أثناء الدمج: " . $e->getMessage());
        }

        $this->redirect('/admin/productivity/quality');
        return new Response();
    }

    /**
     * SEO Metadata Manager.
     */
    public function seoManager(Request $request): Response
    {
        $db = Database::getInstance();

        // Fetch all providers and their SEO metadata
        $providers = $db->fetchAll("
            SELECT p.id, p.display_name_ar, p.slug, c.display_name_ar as city_name, s.display_name_ar as service_name,
                   sm.meta_title_ar, sm.meta_description_ar
            FROM `providers` p
            JOIN `cities` c ON p.city_id = c.id
            JOIN `services` s ON p.primary_service_id = s.id
            LEFT JOIN `seo_metadata` sm ON sm.entity_type = 'provider' AND sm.entity_id = p.id
            WHERE p.deleted_at IS NULL
            ORDER BY p.id DESC
        ");

        $viewData = [
            'providers' => $providers,
            'active_tab' => 'seo'
        ];

        $content = View::render('admin/productivity/seo', $viewData);

        return $this->render('layouts/admin', [
            'title' => 'مدير الـ SEO الجماعي',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'أدوات الإنتاجية', 'url' => '/admin/productivity/quality'],
                ['label' => 'إدارة الـ SEO الجماعية']
            ]
        ]);
    }

    /**
     * Save/Update SEO meta tags via Ajax or POST.
     */
    public function saveSeo(Request $request): Response
    {
        $this->validateCsrf($request);

        $providerId = (int)$request->input('provider_id');
        $metaTitle = trim((string)$request->input('meta_title_ar'));
        $metaDesc = trim((string)$request->input('meta_description_ar'));

        if (!$providerId) {
            return Response::json(['success' => false, 'message' => 'الرمز غير صحيح'], 400);
        }

        $db = Database::getInstance();

        $exists = $db->fetch("SELECT id FROM `seo_metadata` WHERE `entity_type` = 'provider' AND `entity_id` = ?", [$providerId]);

        if ($exists) {
            $db->execute("
                UPDATE `seo_metadata` 
                SET `meta_title_ar` = ?, `meta_description_ar` = ?, `updated_at` = CURRENT_TIMESTAMP 
                WHERE `entity_type` = 'provider' AND `entity_id` = ?
            ", [$metaTitle, $metaDesc, $providerId]);
        } else {
            $db->execute("
                INSERT INTO `seo_metadata` (`entity_type`, `entity_id`, `meta_title_ar`, `meta_description_ar`) 
                VALUES ('provider', ?, ?, ?)
            ", [$providerId, $metaTitle, $metaDesc]);
        }

        return Response::json(['success' => true, 'message' => 'تم حفظ الكلمات الدليلية بنجاح.']);
    }

    /**
     * Auto Generate missing SEO Meta Tags for all or selected providers.
     */
    public function autoGenerateSeo(Request $request): Response
    {
        $this->validateCsrf($request);

        $db = Database::getInstance();

        $providers = $db->fetchAll("
            SELECT p.id, p.display_name_ar, c.display_name_ar as city_name, s.display_name_ar as service_name
            FROM `providers` p
            JOIN `cities` c ON p.city_id = c.id
            JOIN `services` s ON p.primary_service_id = s.id
            LEFT JOIN `seo_metadata` sm ON sm.entity_type = 'provider' AND sm.entity_id = p.id
            WHERE p.deleted_at IS NULL AND (sm.id IS NULL OR sm.meta_title_ar IS NULL OR sm.meta_title_ar = '')
        ");

        $count = 0;
        foreach ($providers as $p) {
            $title = $p['display_name_ar'] . ' - ' . $p['service_name'] . ' في ' . $p['city_name'];
            $desc = 'هل تبحث عن ' . $p['display_name_ar'] . '؟ نوفر لك أفضل خدمات ' . $p['service_name'] . ' في مدينة ' . $p['city_name'] . ' مع أسعار ممتازة وتقييمات حقيقية.';

            $exists = $db->fetch("SELECT id FROM `seo_metadata` WHERE `entity_type` = 'provider' AND `entity_id` = ?", [$p['id']]);
            if ($exists) {
                $db->execute("
                    UPDATE `seo_metadata` 
                    SET `meta_title_ar` = ?, `meta_description_ar` = ? 
                    WHERE `entity_type` = 'provider' AND `entity_id` = ?
                ", [$title, $desc, $p['id']]);
            } else {
                $db->execute("
                    INSERT INTO `seo_metadata` (`entity_type`, `entity_id`, `meta_title_ar`, `meta_description_ar`) 
                    VALUES ('provider', ?, ?, ?)
                ", [$p['id'], $title, $desc]);
            }
            $count++;
        }

        Flash::success("تم توليد الكلمات الدليلية تلقائياً لـ {$count} مزودي خدمات بنجاح.");
        $this->redirect('/admin/productivity/seo');
        return new Response();
    }

    /**
     * Media Manager Dashboard.
     */
    public function mediaManager(Request $request): Response
    {
        $db = Database::getInstance();

        $images = $db->fetchAll("
            SELECT pi.*, p.display_name_ar
            FROM `provider_images` pi
            JOIN `providers` p ON pi.provider_id = p.id
            WHERE pi.deleted_at IS NULL
            ORDER BY pi.id DESC
        ");

        $uploadDir = Config::get('app.paths.root') . '/public/uploads/';
        $physicalFiles = [];
        if (is_dir($uploadDir)) {
            $files = scandir($uploadDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($uploadDir . $file)) {
                    $physicalFiles[] = 'uploads/' . $file;
                }
            }
        }

        $dbFiles = $db->fetchAllColumn("SELECT image_path FROM `provider_images` WHERE deleted_at IS NULL");
        $orphanedFiles = array_diff($physicalFiles, $dbFiles);

        $viewData = [
            'images' => $images,
            'orphanedFiles' => $orphanedFiles,
            'active_tab' => 'media'
        ];

        $content = View::render('admin/productivity/media', $viewData);

        return $this->render('layouts/admin', [
            'title' => 'مدير الوسائط والصور',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'أدوات الإنتاجية', 'url' => '/admin/productivity/quality'],
                ['label' => 'مدير الوسائط والصور']
            ]
        ]);
    }

    /**
     * Clean/Delete orphaned upload files.
     */
    public function cleanMedia(Request $request): Response
    {
        $this->validateCsrf($request);

        $uploadDir = Config::get('app.paths.root') . '/public/uploads/';

        $physicalFiles = [];
        if (is_dir($uploadDir)) {
            $files = scandir($uploadDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($uploadDir . $file)) {
                    $physicalFiles[] = 'uploads/' . $file;
                }
            }
        }

        $db = Database::getInstance();
        $dbFiles = $db->fetchAllColumn("SELECT image_path FROM `provider_images` WHERE deleted_at IS NULL");
        $orphanedFiles = array_diff($physicalFiles, $dbFiles);

        $count = 0;
        foreach ($orphanedFiles as $orphaned) {
            $fullPath = Config::get('app.paths.root') . '/public/' . $orphaned;
            if (file_exists($fullPath)) {
                unlink($fullPath);
                $count++;
            }
        }

        Flash::success("تم تنظيف المستودع وحذف {$count} ملفات غير مستخدمة بنجاح.");
        $this->redirect('/admin/productivity/media');
        return new Response();
    }
}
