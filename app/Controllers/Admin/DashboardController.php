<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Config;
use App\Core\View;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard index page wrapped in the admin layout shell.
     */
    public function index(Request $request): Response
    {
        $data = [
            'admin_name' => Session::get('admin_user_name', 'مدير النظام'),
            'app_version' => '1.0.0',
            'app_env' => Config::get('app.env', 'production'),
        ];

        // Render child view
        $content = View::render('admin/dashboard', $data);

        // Render parent layout wrapping the content
        return $this->render('layouts/admin', [
            'title' => 'لوحة التحكم',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'لوحة التحكم']
            ]
        ]);
    }

    /**
     * Show review list for moderation.
     */
    public function reviews(Request $request): Response
    {
        $db = \App\Core\Database::getInstance();
        
        $reviews = $db->fetchAll(
            "SELECT r.*, p.display_name_ar as provider_name, p.slug as provider_slug 
             FROM `reviews` r 
             INNER JOIN `providers` p ON r.provider_id = p.id 
             WHERE r.deleted_at IS NULL 
             ORDER BY r.created_at DESC"
        );

        $content = View::render('admin/reviews', [
            'reviews' => $reviews
        ]);

        return $this->render('layouts/admin', [
            'title' => 'إدارة ومراجعة التقييمات',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة التقييمات']
            ]
        ]);
    }

    public function approveReview(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $id = (int)$id;

        $db = \App\Core\Database::getInstance();
        $review = $db->fetch("SELECT * FROM `reviews` WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$review) {
            \App\Core\Flash::error('التقييم المطلوب غير موجود.');
            return Response::redirect('/admin/reviews');
        }

        $db->execute("UPDATE `reviews` SET `status` = 'approved', `is_approved` = 1 WHERE id = :id", ['id' => $id]);

        $aggregationService = new \App\Services\ReviewAggregationService();
        $aggregationService->recalculateProviderStats($review['provider_id']);

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->execute(
            "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`) 
             VALUES (:admin_id, 'approve_review', 'reviews', :id, :ip)",
            [
                'admin_id' => $adminUserId,
                'id' => $id,
                'ip' => hash('sha256', $ipAddress)
            ]
        );

        \App\Core\Flash::success('تمت الموافقة على التقييم ونشره بنجاح.');
        return Response::redirect('/admin/reviews');
    }

    public function rejectReview(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $id = (int)$id;

        $db = \App\Core\Database::getInstance();
        $review = $db->fetch("SELECT * FROM `reviews` WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$review) {
            \App\Core\Flash::error('التقييم المطلوب غير موجود.');
            return Response::redirect('/admin/reviews');
        }

        $db->execute("UPDATE `reviews` SET `status` = 'rejected', `is_approved` = 0 WHERE id = :id", ['id' => $id]);

        $aggregationService = new \App\Services\ReviewAggregationService();
        $aggregationService->recalculateProviderStats($review['provider_id']);

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->execute(
            "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`) 
             VALUES (:admin_id, 'reject_review', 'reviews', :id, :ip)",
            [
                'admin_id' => $adminUserId,
                'id' => $id,
                'ip' => hash('sha256', $ipAddress)
            ]
        );

        \App\Core\Flash::success('تم رفض التقييم بنجاح.');
        return Response::redirect('/admin/reviews');
    }

    public function deleteReview(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $id = (int)$id;

        $db = \App\Core\Database::getInstance();
        $review = $db->fetch("SELECT * FROM `reviews` WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$review) {
            \App\Core\Flash::error('التقييم المطلوب غير موجود.');
            return Response::redirect('/admin/reviews');
        }

        $db->execute("UPDATE `reviews` SET `deleted_at` = CURRENT_TIMESTAMP WHERE id = :id", ['id' => $id]);

        $aggregationService = new \App\Services\ReviewAggregationService();
        $aggregationService->recalculateProviderStats($review['provider_id']);

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->execute(
            "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`) 
             VALUES (:admin_id, 'delete_review', 'reviews', :id, :ip)",
            [
                'admin_id' => $adminUserId,
                'id' => $id,
                'ip' => hash('sha256', $ipAddress)
            ]
        );

        \App\Core\Flash::success('تم حذف التقييم بنجاح.');
        return Response::redirect('/admin/reviews');
    }

    /**
     * Show verification requests list.
     */
    public function verificationRequests(Request $request): Response
    {
        $db = \App\Core\Database::getInstance();
        
        $providers = $db->fetchAll(
            "SELECT id, display_name_ar, slug, business_type, verification_status, verification_document_path, verification_rejection_reason 
             FROM `providers` 
             WHERE `verification_status` IN ('documents_uploaded', 'pending_review', 'resubmitted', 'verified', 'rejected') AND `deleted_at` IS NULL 
             ORDER BY CASE `verification_status` 
                WHEN 'documents_uploaded' THEN 1 
                WHEN 'resubmitted' THEN 2 
                WHEN 'pending_review' THEN 3 
                WHEN 'rejected' THEN 4 
                WHEN 'verified' THEN 5 
                ELSE 6 END ASC, `updated_at` DESC"
        );

        $content = View::render('admin/verification', [
            'providers' => $providers
        ]);

        return $this->render('layouts/admin', [
            'title' => 'إدارة توثيق الحسابات والوثائق الثبوتية',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة توثيق الحسابات']
            ]
        ]);
    }

    public function approveVerification(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $id = (int)$id;

        $db = \App\Core\Database::getInstance();
        $provider = $db->fetch("SELECT * FROM `providers` WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$provider) {
            \App\Core\Flash::error('مزود الخدمة المطلوب غير موجود.');
            return Response::redirect('/admin/verification');
        }

        $db->execute(
            "UPDATE `providers` SET 
                `verification_status` = 'verified', 
                `verified` = 1,
                `verification_rejection_reason` = NULL 
             WHERE id = :id",
            ['id' => $id]
        );

        $aggregationService = new \App\Services\ReviewAggregationService();
        $aggregationService->recalculateProviderStats($id);

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->execute(
            "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`) 
             VALUES (:admin_id, 'approve_verification', 'providers', :id, :ip)",
            [
                'admin_id' => $adminUserId,
                'id' => $id,
                'ip' => hash('sha256', $ipAddress)
            ]
        );

        \App\Core\Flash::success('تم توثيق حساب المزود بنجاح وتفعيل الشارة.');
        return Response::redirect('/admin/verification');
    }

    public function rejectVerification(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $id = (int)$id;
        $reason = trim($request->input('rejection_reason', ''));

        if (empty($reason)) {
            \App\Core\Flash::error('يرجى تحديد سبب الرفض لتوجيه الحرفي.');
            return Response::redirect('/admin/verification');
        }

        $db = \App\Core\Database::getInstance();
        $provider = $db->fetch("SELECT * FROM `providers` WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$provider) {
            \App\Core\Flash::error('مزود الخدمة المطلوب غير موجود.');
            return Response::redirect('/admin/verification');
        }

        $db->execute(
            "UPDATE `providers` SET 
                `verification_status` = 'rejected', 
                `verified` = 0,
                `verification_rejection_reason` = :reason 
             WHERE id = :id",
            [
                'id' => $id,
                'reason' => $reason
            ]
        );

        $aggregationService = new \App\Services\ReviewAggregationService();
        $aggregationService->recalculateProviderStats($id);

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->execute(
            "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`, `new_value_json`) 
             VALUES (:admin_id, 'reject_verification', 'providers', :id, :ip, :notes)",
            [
                'admin_id' => $adminUserId,
                'id' => $id,
                'ip' => hash('sha256', $ipAddress),
                'notes' => json_encode(['reason' => $reason], JSON_UNESCAPED_UNICODE)
            ]
        );

        \App\Core\Flash::success('تم رفض طلب التوثيق وإرسال السبب للمزود.');
        return Response::redirect('/admin/verification');
    }

    /**
     * Preview private verification documents securely.
     */
    public function previewDocument(Request $request, string $filename): Response
    {
        if (preg_match('/[^a-zA-Z0-9_\.-]/', $filename) || strpos($filename, '..') !== false) {
            \App\Core\Flash::error('اسم ملف غير صالح.');
            return Response::redirect('/admin/dashboard');
        }

        $filePath = dirname(dirname(dirname(__DIR__))) . '/storage/secure_uploads/verification/' . $filename;
        if (!file_exists($filePath)) {
            \App\Core\Flash::error('الملف المطلوب غير موجود.');
            return Response::redirect('/admin/dashboard');
        }

        $mimeType = mime_content_type($filePath);
        
        $response = new Response();
        $response->setHeader('Content-Type', $mimeType);
        $response->setHeader('Content-Disposition', 'inline; filename="' . basename($filename) . '"');
        $response->setContent(file_get_contents($filePath));
        return $response;
    }
}
