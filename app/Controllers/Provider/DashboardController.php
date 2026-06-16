<?php

namespace App\Controllers\Provider;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\ProviderAccountRepository;
use App\Repositories\ProviderDraftRepository;
use App\Repositories\ProviderRepository;

class DashboardController extends Controller
{
    private ProviderAccountRepository $accountRepo;
    private ProviderDraftRepository $draftRepo;
    private ProviderRepository $providerRepo;

    public function __construct()
    {
        $this->accountRepo = new ProviderAccountRepository();
        $this->draftRepo = new ProviderDraftRepository();
        $this->providerRepo = new ProviderRepository();
    }

    /**
     * Display the provider dashboard.
     */
    public function index(Request $request): Response
    {
        $accountId = Session::get('provider_account_id');
        $account = $this->accountRepo->find($accountId);

        if (!$account) {
            Session::destroy();
            return Response::redirect('/provider/login');
        }

        $provider = null;
        if ($account['provider_id']) {
            $provider = $this->providerRepo->find($account['provider_id']);
        }

        $draft = $this->draftRepo->getLatestDraftForAccount($accountId);
        
        // Calculate completeness score of the draft (or provider if no draft)
        $scoreSource = $draft ?: $provider;
        $completenessScore = 0;
        $missingFields = [];

        if ($scoreSource) {
            // Profile image
            $hasLogo = !empty($scoreSource['logo_path']) || !empty($scoreSource['logo']);
            if ($hasLogo) {
                $completenessScore += 15;
            } else {
                $missingFields['الصورة الشخصية'] = 'إضافة صورة الملف الشخصي تزيد الموثوقية بنسبة 15%.';
            }

            // Coverage areas
            $hasAreas = false;
            if (isset($scoreSource['coverage_areas_json'])) {
                $hasAreas = !empty($scoreSource['coverage_areas_json']);
            } elseif (isset($scoreSource['areas'])) {
                $hasAreas = !empty($scoreSource['areas']);
            }
            if ($hasAreas) {
                $completenessScore += 15;
            } else {
                $missingFields['مناطق التغطية'] = 'تحديد مناطق تغطية الخدمة يزيد نسبة الاكتمال بمقدار 15%.';
            }

            // Description
            $hasDesc = !empty($scoreSource['description_ar']);
            if ($hasDesc) {
                $completenessScore += 15;
            } else {
                $missingFields['الوصف التفصيلي'] = 'كتابة شرح كافٍ عن خبرتك وعملك تزيد نسبة الاكتمال بـ 15%.';
            }

            // Short description
            $hasShortDesc = !empty($scoreSource['short_description_ar']);
            if ($hasShortDesc) {
                $completenessScore += 10;
            } else {
                $missingFields['الوصف المختصر'] = 'نبذة قصيرة تظهر في نتائج البحث تزيد نسبة الاكتمال بـ 10%.';
            }

            // WhatsApp
            $hasWhatsapp = !empty($scoreSource['whatsapp']);
            if ($hasWhatsapp) {
                $completenessScore += 10;
            } else {
                $missingFields['رقم الواتساب'] = 'إضافة وسيلة تواصل سريعة عبر الواتساب تزيد الاكتمال بـ 10%.';
            }

            // Email
            $hasEmail = !empty($scoreSource['email']);
            if ($hasEmail) {
                $completenessScore += 10;
            } else {
                $missingFields['البريد الإلكتروني'] = 'تحديد بريد إلكتروني للتواصل يزيد نسبة الاكتمال بـ 10%.';
            }

            // Work photos
            $hasPhotos = false;
            if (isset($scoreSource['work_photos_json'])) {
                $hasPhotos = !empty($scoreSource['work_photos_json']);
            } elseif (isset($scoreSource['work_photos'])) {
                $hasPhotos = !empty($scoreSource['work_photos']);
            }
            if ($hasPhotos) {
                $completenessScore += 15;
            } else {
                $missingFields['معرض الأعمال'] = 'إضافة صور لأعمالك السابقة تزيد نسبة الاكتمال بـ 15%.';
            }

            // Experience
            $hasExp = !empty($scoreSource['years_experience']) && (int)$scoreSource['years_experience'] > 0;
            if ($hasExp) {
                $completenessScore += 5;
            } else {
                $missingFields['سنوات الخبرة'] = 'تحديد عدد سنوات الخبرة يزيد الاكتمال بنسبة 5%.';
            }

            // Secondary services
            $hasServices = false;
            if (isset($scoreSource['secondary_services_json'])) {
                $hasServices = !empty($scoreSource['secondary_services_json']);
            } elseif (isset($scoreSource['services'])) {
                $hasServices = !empty($scoreSource['services']);
            }
            if ($hasServices) {
                $completenessScore += 5;
            } else {
                $missingFields['الخدمات الثانوية'] = 'إضافة تخصصات إضافية تزيد نسبة الاكتمال بـ 5%.';
            }
        }

        // Fetch recent contact events or reviews for the provider if published
        $reviews = [];
        $contactCount = 0;
        if ($provider) {
            $db = \App\Core\Database::getInstance();
            $reviews = $db->fetchAll(
                "SELECT * FROM `reviews` WHERE `provider_id` = :pid ORDER BY `created_at` DESC LIMIT 5",
                ['pid' => $provider['id']]
            );
            $contactCount = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM `contact_events` WHERE `provider_id` = :pid",
                ['pid' => $provider['id']]
            );
        }

        return $this->render('provider/dashboard', [
            'account' => $account,
            'provider' => $provider,
            'draft' => $draft,
            'completenessScore' => $completenessScore,
            'missingFields' => $missingFields,
            'reviews' => $reviews,
            'contactCount' => $contactCount
        ]);
    }

    /**
     * Handle verification document upload.
     */
    public function uploadVerification(Request $request): Response
    {
        $accountId = Session::get('provider_account_id');
        $account = $this->accountRepo->find($accountId);
        if (!$account || !$account['provider_id']) {
            \App\Core\Flash::error('يجب تأسيس الملف الشخصي أولاً قبل طلب التوثيق.');
            return Response::redirect('/provider/dashboard');
        }

        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            \App\Core\Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/provider/dashboard');
        }

        $file = $request->file('verification_document');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            \App\Core\Flash::error('يرجى اختيار ملف صحيح للرفع.');
            return Response::redirect('/provider/dashboard');
        }

        // Validate type (jpg, png, pdf)
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $fileType = $file['type'];
        if (!in_array($fileType, $allowedTypes)) {
            \App\Core\Flash::error('نوع الملف غير مسموح. المسموح فقط: JPG, PNG, PDF.');
            return Response::redirect('/provider/dashboard');
        }

        // Validate size (5MB max)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            \App\Core\Flash::error('حجم الملف كبير جداً. الحد الأقصى هو 5 ميجابايت.');
            return Response::redirect('/provider/dashboard');
        }

        // Ensure target directory exists
        $uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/storage/secure_uploads/verification';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate a secure, non-guessable filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'verif_' . $account['provider_id'] . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            \App\Core\Flash::error('خطأ أثناء حفظ الملف. يرجى المحاولة لاحقاً.');
            return Response::redirect('/provider/dashboard');
        }

        // Update provider verification status and document path
        $db = \App\Core\Database::getInstance();
        $provider = $this->providerRepo->find($account['provider_id']);
        
        $newStatus = ($provider['verification_status'] === 'rejected') ? 'resubmitted' : 'documents_uploaded';

        $db->execute(
            "UPDATE `providers` SET 
                `verification_status` = :status, 
                `verification_document_path` = :doc_path,
                `verification_rejection_reason` = NULL 
             WHERE `id` = :pid",
            [
                'status' => $newStatus,
                'doc_path' => $filename,
                'pid' => $account['provider_id']
            ]
        );

        // Update platform score & aggregates!
        $aggregationService = new \App\Services\ReviewAggregationService();
        $aggregationService->recalculateProviderStats($account['provider_id']);

        // Log Audit Event
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->execute(
            "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`) 
             VALUES (NULL, 'upload_verification_document', 'providers', :entity_id, :ip)",
            [
                'entity_id' => $account['provider_id'],
                'ip' => hash('sha256', $ipAddress)
            ]
        );

        \App\Core\Flash::success('تم رفع وثيقة التوثيق بنجاح وهي قيد المراجعة الإدارية الآن.');
        return Response::redirect('/provider/dashboard');
    }
}
