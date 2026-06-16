<?php
/**
 * ProvidersController.php
 * Khadomeh Providers Admin CRUD Controller
 */

namespace App\Modules\Providers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Core\Database;
use App\Repositories\ProviderRepository;
use App\Repositories\ProviderDraftRepository;
use App\Repositories\ProviderAccountRepository;

class ProvidersController extends Controller
{
    private ProvidersService $service;
    private ProvidersValidation $validation;
    private ProviderRepository $repo;

    public function __construct()
    {
        $this->repo = new ProviderRepository();
        $this->service = new ProvidersService($this->repo);
        $this->validation = new ProvidersValidation($this->repo);
    }

    /**
     * Display a paginated list of providers.
     */
    public function index(Request $request): Response
    {
        $criteria = ['admin_mode' => true];
        
        // Handle search keyword
        $keyword = $request->query('keyword');
        if ($keyword !== null && trim($keyword) !== '') {
            $criteria['keyword'] = trim($keyword);
        }

        // Handle active/deleted/status filters
        $isActive = $request->query('is_active');
        if ($isActive !== null && $isActive !== '') {
            $criteria['is_active'] = (int)$isActive;
        }

        $isDeleted = $request->query('is_deleted');
        if ($isDeleted !== null && $isDeleted !== '') {
            $criteria['is_deleted'] = (int)$isDeleted;
        }

        $status = $request->query('status');
        if ($status !== null && $status !== '') {
            $criteria['status'] = trim($status);
        }

        $cityId = $request->query('city_id');
        if ($cityId !== null && $cityId !== '') {
            $criteria['city_id'] = (int)$cityId;
        }

        $serviceId = $request->query('service_id');
        if ($serviceId !== null && $serviceId !== '') {
            $criteria['service_id'] = (int)$serviceId;
        }

        // Advanced filters
        $ratingMin = $request->query('rating_min');
        if ($ratingMin !== null && $ratingMin !== '') {
            $criteria['rating_min'] = (float)$ratingMin;
        }
        $ratingMax = $request->query('rating_max');
        if ($ratingMax !== null && $ratingMax !== '') {
            $criteria['rating_max'] = (float)$ratingMax;
        }
        $experienceMin = $request->query('experience_min');
        if ($experienceMin !== null && $experienceMin !== '') {
            $criteria['experience_min'] = (int)$experienceMin;
        }
        $experienceMax = $request->query('experience_max');
        if ($experienceMax !== null && $experienceMax !== '') {
            $criteria['experience_max'] = (int)$experienceMax;
        }
        $businessType = $request->query('business_type');
        if ($businessType !== null && $businessType !== '') {
            $criteria['business_type'] = trim($businessType);
        }
        $verified = $request->query('verified');
        if ($verified !== null && $verified !== '') {
            $criteria['verified'] = (int)$verified;
        }
        $phoneVerified = $request->query('phone_verified');
        if ($phoneVerified !== null && $phoneVerified !== '') {
            $criteria['phone_verified'] = (int)$phoneVerified;
        }
        $identityVerified = $request->query('identity_verified');
        if ($identityVerified !== null && $identityVerified !== '') {
            $criteria['identity_verified'] = (int)$identityVerified;
        }
        $isFeatured = $request->query('is_featured');
        if ($isFeatured !== null && $isFeatured !== '') {
            $criteria['is_featured'] = (int)$isFeatured;
        }
        $completionMin = $request->query('completion_min');
        if ($completionMin !== null && $completionMin !== '') {
            $criteria['completion_min'] = (int)$completionMin;
        }
        $completionMax = $request->query('completion_max');
        if ($completionMax !== null && $completionMax !== '') {
            $criteria['completion_max'] = (int)$completionMax;
        }

        // Sorting params
        $sortBy = $request->query('sort_by', 'sort_weight');
        $sortDir = $request->query('sort_dir', 'DESC');

        // Pagination setup
        $page = max(1, (int)$request->query('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        // Fetch data
        $items = $this->service->searchProviders($criteria, $sortBy, $sortDir, $limit, $offset);
        $totalItems = $this->service->countProviders($criteria);
        $totalPages = (int)ceil($totalItems / $limit);

        // Fetch lookup filters lists
        $db = Database::getInstance();
        $cities = $db->fetchAll("SELECT id, display_name_ar FROM `cities` WHERE `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
        $services = $db->fetchAll("SELECT id, display_name_ar FROM `services` WHERE `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");

        // Pass variables to view
        $viewData = [
            'items' => $items,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit,
            'keyword' => $keyword,
            'is_active' => $isActive,
            'is_deleted' => $isDeleted,
            'status' => $status,
            'city_id' => $cityId,
            'service_id' => $serviceId,
            'rating_min' => $ratingMin,
            'rating_max' => $ratingMax,
            'experience_min' => $experienceMin,
            'experience_max' => $experienceMax,
            'business_type' => $businessType,
            'verified' => $verified,
            'phone_verified' => $phoneVerified,
            'identity_verified' => $identityVerified,
            'is_featured' => $isFeatured,
            'completion_min' => $completionMin,
            'completion_max' => $completionMax,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'cities' => $cities,
            'services' => $services,
        ];

        $content = $this->renderView('list', $viewData);

        return $this->render('layouts/admin', [
            'title' => 'إدارة مزودي الخدمات',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة مزودي الخدمات']
            ]
        ]);
    }

    /**
     * Show form to create a new provider.
     */
    public function create(Request $request): Response
    {
        $db = Database::getInstance();
        $cities = $db->fetchAll("SELECT id, display_name_ar FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
        $areas = $db->fetchAll("SELECT id, city_id, display_name_ar FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
        $services = $db->fetchAll("SELECT id, display_name_ar FROM `services` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");

        $content = $this->renderView('create', [
            'errors' => [],
            'old' => [],
            'cities' => $cities,
            'areas' => $areas,
            'services' => $services,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'إضافة مزود خدمة جديد',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة مزودي الخدمات', 'url' => '/admin/providers'],
                ['label' => 'إضافة مزود خدمة']
            ]
        ]);
    }

    /**
     * Store newly created provider in database.
     */
    public function store(Request $request): Response
    {
        $this->validateCsrf($request);
        $errors = [];

        $data = $request->input();

        // 1. Process Logo Upload
        $logoPath = null;
        $logoFile = $request->file('logo');
        if ($logoFile && $logoFile['error'] === UPLOAD_ERR_OK) {
            try {
                $logoPath = \App\Core\Upload::savePublicImage($logoFile, 'avatars');
            } catch (\Exception $e) {
                $errors['logo'][] = $e->getMessage();
            }
        }
        $data['logo'] = $logoPath;

        // 2. Process Gallery Photos Upload
        $workPhotoPaths = [];
        $workPhotos = $_FILES['work_photos'] ?? null;
        if ($workPhotos && is_array($workPhotos['name'])) {
            for ($i = 0; $i < count($workPhotos['name']); $i++) {
                if ($workPhotos['error'][$i] === UPLOAD_ERR_OK) {
                    $singleFile = [
                        'name' => $workPhotos['name'][$i],
                        'type' => $workPhotos['type'][$i],
                        'tmp_name' => $workPhotos['tmp_name'][$i],
                        'error' => $workPhotos['error'][$i],
                        'size' => $workPhotos['size'][$i],
                    ];
                    try {
                        $path = \App\Core\Upload::savePublicImage($singleFile, 'galleries');
                        $workPhotoPaths[] = $path;
                    } catch (\Exception $e) {
                        $errors['work_photos'][] = "فشل رفع الصورة " . ($i + 1) . ": " . $e->getMessage();
                    }
                }
            }
        }
        $data['work_photos'] = $workPhotoPaths;

        // Run validation pipeline
        [$valErrors, $dto] = $this->validation->validate($data);
        $errors = array_merge_recursive($errors, $valErrors);

        if (!empty($errors)) {
            $db = Database::getInstance();
            $cities = $db->fetchAll("SELECT id, display_name_ar FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
            $areas = $db->fetchAll("SELECT id, city_id, display_name_ar FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
            $services = $db->fetchAll("SELECT id, display_name_ar FROM `services` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");

            $content = $this->renderView('create', [
                'errors' => $errors,
                'old' => $data,
                'cities' => $cities,
                'areas' => $areas,
                'services' => $services,
            ]);

            return $this->render('layouts/admin', [
                'title' => 'إضافة مزود خدمة جديد',
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة مزودي الخدمات', 'url' => '/admin/providers'],
                    ['label' => 'إضافة مزود خدمة']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->createProvider($dto, $adminUserId, $ipAddress);

        Flash::success('تم إضافة مزود الخدمة بنجاح.');
        $this->redirect('/admin/providers');
        return new Response();
    }

    /**
     * Display a single provider details.
     */
    public function show(Request $request, string $id): Response
    {
        $item = $this->service->getById((int)$id);
        if (!$item) {
            Flash::error('مزود الخدمة المطلوب غير موجود.');
            $this->redirect('/admin/providers');
            return new Response();
        }

        // Fetch audit logs for this provider
        $db = Database::getInstance();
        $auditLogs = $db->fetchAll(
            "SELECT a.*, u.name as admin_name 
             FROM `audit_logs` a 
             LEFT JOIN `admin_users` u ON a.`admin_user_id` = u.`id` 
             WHERE a.`entity_type` = 'providers' AND a.`entity_id` = :entity_id 
             ORDER BY a.`created_at` DESC",
            ['entity_id' => $id]
        );

        // Fetch location and service names
        $city = $db->fetch("SELECT display_name_ar FROM `cities` WHERE id = :id", ['id' => $item['city_id']]);
        $service = $db->fetch("SELECT display_name_ar FROM `services` WHERE id = :id", ['id' => $item['primary_service_id']]);
        
        $item['city_name'] = $city ? $city['display_name_ar'] : '';
        $item['service_name'] = $service ? $service['display_name_ar'] : '';

        // Fetch mapped covered areas
        $areas = $db->fetchAll(
            "SELECT a.display_name_ar FROM `areas` a 
             INNER JOIN `provider_area_map` pam ON a.id = pam.area_id
             WHERE pam.provider_id = :id",
            ['id' => $id]
        );
        $item['areas_covered'] = array_column($areas, 'display_name_ar');

        // Fetch secondary services
        $secServices = $db->fetchAll(
            "SELECT s.display_name_ar FROM `services` s 
             INNER JOIN `provider_service_map` psm ON s.id = psm.service_id
             WHERE psm.provider_id = :id",
            ['id' => $id]
        );
        $item['secondary_services'] = array_column($secServices, 'display_name_ar');

        $content = $this->renderView('show', [
            'item' => $item,
            'auditLogs' => $auditLogs,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تفاصيل مزود الخدمة: ' . $item['display_name_ar'],
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة مزودي الخدمات', 'url' => '/admin/providers'],
                ['label' => $item['display_name_ar']]
            ]
        ]);
    }

    /**
     * Show form to edit a provider.
     */
    public function edit(Request $request, string $id): Response
    {
        $item = $this->service->getById((int)$id);
        if (!$item) {
            Flash::error('مزود الخدمة المطلوب غير موجود.');
            $this->redirect('/admin/providers');
            return new Response();
        }

        $db = Database::getInstance();
        $cities = $db->fetchAll("SELECT id, display_name_ar FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
        $areas = $db->fetchAll("SELECT id, city_id, display_name_ar FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
        $services = $db->fetchAll("SELECT id, display_name_ar FROM `services` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");

        $content = $this->renderView('edit', [
            'item' => $item,
            'errors' => [],
            'old' => $item,
            'cities' => $cities,
            'areas' => $areas,
            'services' => $services,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تعديل مزود الخدمة: ' . $item['display_name_ar'],
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة مزودي الخدمات', 'url' => '/admin/providers'],
                ['label' => 'تعديل مزود الخدمة']
            ]
        ]);
    }

    /**
     * Update an existing provider in database.
     */
    public function update(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;
        $errors = [];

        $item = $this->service->getById($idVal);
        if (!$item) {
            Flash::error('مزود الخدمة المطلوب غير موجود.');
            $this->redirect('/admin/providers');
            return new Response();
        }

        $data = $request->input();

        // 1. Process Logo Upload
        $logoPath = null;
        $logoFile = $request->file('logo');
        if ($logoFile && $logoFile['error'] === UPLOAD_ERR_OK) {
            try {
                $logoPath = \App\Core\Upload::savePublicImage($logoFile, 'avatars');
            } catch (\Exception $e) {
                $errors['logo'][] = $e->getMessage();
            }
        }
        $data['logo'] = $logoPath ?: ($item['logo'] ?? null);

        // 2. Process Gallery Photos Upload
        $workPhotoPaths = [];
        $workPhotos = $_FILES['work_photos'] ?? null;
        if ($workPhotos && is_array($workPhotos['name'])) {
            for ($i = 0; $i < count($workPhotos['name']); $i++) {
                if ($workPhotos['error'][$i] === UPLOAD_ERR_OK) {
                    $singleFile = [
                        'name' => $workPhotos['name'][$i],
                        'type' => $workPhotos['type'][$i],
                        'tmp_name' => $workPhotos['tmp_name'][$i],
                        'error' => $workPhotos['error'][$i],
                        'size' => $workPhotos['size'][$i],
                    ];
                    try {
                        $path = \App\Core\Upload::savePublicImage($singleFile, 'galleries');
                        $workPhotoPaths[] = $path;
                    } catch (\Exception $e) {
                        $errors['work_photos'][] = "فشل رفع الصورة " . ($i + 1) . ": " . $e->getMessage();
                    }
                }
            }
        }
        $data['work_photos'] = !empty($workPhotoPaths) ? $workPhotoPaths : ($item['work_photos'] ?? []);

        // Run validation pipeline
        [$valErrors, $dto] = $this->validation->validate($data, $idVal);
        $errors = array_merge_recursive($errors, $valErrors);

        if (!empty($errors)) {
            $db = Database::getInstance();
            $cities = $db->fetchAll("SELECT id, display_name_ar FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
            $areas = $db->fetchAll("SELECT id, city_id, display_name_ar FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");
            $services = $db->fetchAll("SELECT id, display_name_ar FROM `services` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC, `display_name_ar` ASC");

            $content = $this->renderView('edit', [
                'item' => $item,
                'errors' => $errors,
                'old' => $data,
                'cities' => $cities,
                'areas' => $areas,
                'services' => $services,
            ]);

            return $this->render('layouts/admin', [
                'title' => 'تعديل مزود الخدمة: ' . $item['display_name_ar'],
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة مزودي الخدمات', 'url' => '/admin/providers'],
                    ['label' => 'تعديل مزود الخدمة']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->updateProvider($idVal, $dto, $adminUserId, $ipAddress);

        Flash::success('تم تحديث مزود الخدمة بنجاح.');
        $this->redirect('/admin/providers');
        return new Response();
    }

    /**
     * Soft delete a provider.
     */
    public function delete(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->softDeleteProvider($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم حذف مزود الخدمة بنجاح (حذف مؤقت).');
        } else {
            Flash::error('فشل في حذف مزود الخدمة.');
        }

        $this->redirect('/admin/providers');
        return new Response();
    }

    /**
     * Restore a soft-deleted provider.
     */
    public function restore(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->restoreProvider($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم استعادة مزود الخدمة بنجاح.');
        } else {
            Flash::error('فشل في استعادة مزود الخدمة.');
        }

        $this->redirect('/admin/providers');
        return new Response();
    }

    /**
     * Handle bulk actions for selected providers.
     */
    public function bulkAction(Request $request): Response
    {
        $this->validateCsrf($request);
        
        $ids = $request->input('ids');
        $action = $request->input('action');
        
        if (empty($ids) || !is_array($ids)) {
            Flash::error('الرجاء تحديد مزود خدمة واحد على الأقل.');
            $this->redirect('/admin/providers');
            return new Response();
        }
        
        $ids = array_map('intval', $ids);
        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $success = false;
        $message = '';
        
        switch ($action) {
            case 'approve':
                $success = $this->repo->bulkUpdateStatus($ids, 'approved');
                $message = 'تمت الموافقة على مزودي الخدمة المحددين.';
                break;
            case 'pending':
                $success = $this->repo->bulkUpdateStatus($ids, 'pending');
                $message = 'تم تغيير حالة مزودي الخدمة المحددين إلى قيد الانتظار.';
                break;
            case 'reject':
                $success = $this->repo->bulkUpdateStatus($ids, 'rejected');
                $message = 'تم رفض مزودي الخدمة المحددين.';
                break;
            case 'suspend':
                $success = $this->repo->bulkUpdateStatus($ids, 'suspended');
                $message = 'تم تعليق مزودي الخدمة المحددين.';
                break;
            case 'publish':
                $success = $this->repo->bulkUpdateIsActive($ids, true);
                $message = 'تم تفعيل/نشر مزودي الخدمة المحددين.';
                break;
            case 'hide':
                $success = $this->repo->bulkUpdateIsActive($ids, false);
                $message = 'تم إخفاء مزودي الخدمة المحددين.';
                break;
            case 'delete':
                $success = $this->repo->bulkSoftDelete($ids);
                $message = 'تم حذف مزودي الخدمة المحددين مؤقتاً.';
                break;
            case 'restore':
                $success = $this->repo->bulkRestore($ids);
                $message = 'تم استعادة مزودي الخدمة المحددين.';
                break;
            default:
                Flash::error('إجراء غير صالح.');
                $this->redirect('/admin/providers');
                return new Response();
        }
        
        if ($success) {
            // Write to audit log for bulk action
            $db = Database::getInstance();
            $db->execute(
                "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `new_value_json`, `ip_hash`) 
                 VALUES (:admin_id, :action, 'providers', :val, :ip)",
                [
                    'admin_id' => $adminUserId,
                    'action' => 'bulk_' . $action,
                    'val' => json_encode(['affected_ids' => $ids]),
                    'ip' => hash('sha256', $ipAddress)
                ]
            );
            Flash::success($message);
        } else {
            Flash::error('فشل تنفيذ الإجراء الجماعي.');
        }
        
        $this->redirect('/admin/providers');
        return new Response();
    }

    /**
     * Export matching providers as CSV.
     */
    public function export(Request $request): Response
    {
        $criteria = ['admin_mode' => true];
        
        // Match same filters as index
        $keyword = $request->query('keyword');
        if ($keyword !== null && trim($keyword) !== '') {
            $criteria['keyword'] = trim($keyword);
        }
        $isActive = $request->query('is_active');
        if ($isActive !== null && $isActive !== '') {
            $criteria['is_active'] = (int)$isActive;
        }
        $isDeleted = $request->query('is_deleted');
        if ($isDeleted !== null && $isDeleted !== '') {
            $criteria['is_deleted'] = (int)$isDeleted;
        }
        $status = $request->query('status');
        if ($status !== null && $status !== '') {
            $criteria['status'] = trim($status);
        }
        $cityId = $request->query('city_id');
        if ($cityId !== null && $cityId !== '') {
            $criteria['city_id'] = (int)$cityId;
        }
        $serviceId = $request->query('service_id');
        if ($serviceId !== null && $serviceId !== '') {
            $criteria['service_id'] = (int)$serviceId;
        }
        $ratingMin = $request->query('rating_min');
        if ($ratingMin !== null && $ratingMin !== '') {
            $criteria['rating_min'] = (float)$ratingMin;
        }
        $ratingMax = $request->query('rating_max');
        if ($ratingMax !== null && $ratingMax !== '') {
            $criteria['rating_max'] = (float)$ratingMax;
        }
        $experienceMin = $request->query('experience_min');
        if ($experienceMin !== null && $experienceMin !== '') {
            $criteria['experience_min'] = (int)$experienceMin;
        }
        $experienceMax = $request->query('experience_max');
        if ($experienceMax !== null && $experienceMax !== '') {
            $criteria['experience_max'] = (int)$experienceMax;
        }
        $businessType = $request->query('business_type');
        if ($businessType !== null && $businessType !== '') {
            $criteria['business_type'] = trim($businessType);
        }
        $verified = $request->query('verified');
        if ($verified !== null && $verified !== '') {
            $criteria['verified'] = (int)$verified;
        }
        $phoneVerified = $request->query('phone_verified');
        if ($phoneVerified !== null && $phoneVerified !== '') {
            $criteria['phone_verified'] = (int)$phoneVerified;
        }
        $identityVerified = $request->query('identity_verified');
        if ($identityVerified !== null && $identityVerified !== '') {
            $criteria['identity_verified'] = (int)$identityVerified;
        }
        $isFeatured = $request->query('is_featured');
        if ($isFeatured !== null && $isFeatured !== '') {
            $criteria['is_featured'] = (int)$isFeatured;
        }
        $completionMin = $request->query('completion_min');
        if ($completionMin !== null && $completionMin !== '') {
            $criteria['completion_min'] = (int)$completionMin;
        }
        $completionMax = $request->query('completion_max');
        if ($completionMax !== null && $completionMax !== '') {
            $criteria['completion_max'] = (int)$completionMax;
        }

        $sortBy = $request->query('sort_by', 'sort_weight');
        $sortDir = $request->query('sort_dir', 'DESC');

        // Fetch up to 5000 records for export
        $items = $this->service->searchProviders($criteria, $sortBy, $sortDir, 5000, 0);

        // Build CSV content with UTF-8 BOM
        $csvContent = "\xEF\xBB\xBF";
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['ID', 'الاسم', 'الهاتف', 'الواتساب', 'المدينة', 'الخدمة الأساسية', 'الحالة', 'نشط', 'التقييم', 'عدد التقييمات', 'سنوات الخبرة', 'السعر الأولي']);
        foreach ($items as $item) {
            fputcsv($out, [
                $item['id'],
                $item['display_name_ar'],
                $item['phone'],
                $item['whatsapp'],
                $item['city_name'],
                $item['service_name'],
                $item['status'],
                $item['is_active'] ? 'نعم' : 'لا',
                $item['rating'],
                $item['reviews_count'],
                $item['years_experience'],
                $item['starting_price']
            ]);
        }
        rewind($out);
        $csvContent .= stream_get_contents($out);
        fclose($out);

        $response = new Response();
        $response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->setHeader('Content-Disposition', 'attachment; filename="providers_export_' . date('Y-m-d') . '.csv"');
        $response->setContent($csvContent);
        return $response;
    }

    /**
     * Import providers from CSV.
     */
    public function import(Request $request): Response
    {
        $this->validateCsrf($request);

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Flash::error('الرجاء اختيار ملف CSV صالح.');
            $this->redirect('/admin/providers');
            return new Response();
        }

        $filePath = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            Flash::error('فشل في فتح ملف الـ CSV.');
            $this->redirect('/admin/providers');
            return new Response();
        }

        // Detect and skip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Load mappings
        $db = Database::getInstance();
        $citiesDb = $db->fetchAll("SELECT id, display_name_ar FROM `cities` WHERE `is_deleted` = 0");
        $servicesDb = $db->fetchAll("SELECT id, display_name_ar FROM `services` WHERE `is_deleted` = 0");

        $citiesMap = [];
        foreach ($citiesDb as $c) {
            $citiesMap[normalize_arabic($c['display_name_ar'])] = (int)$c['id'];
        }
        $servicesMap = [];
        foreach ($servicesDb as $s) {
            $servicesMap[normalize_arabic($s['display_name_ar'])] = (int)$s['id'];
        }

        // Read headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            Flash::error('ملف CSV فارغ أو غير صالح.');
            $this->redirect('/admin/providers');
            return new Response();
        }

        // Map headers to fields
        $headerMap = [];
        foreach ($headers as $index => $header) {
            $header = trim($header);
            $normHeader = normalize_arabic($header);
            
            if ($header === 'ID' || $header === 'id' || $normHeader === 'الرقم') {
                $headerMap['id'] = $index;
            } elseif ($normHeader === 'الاسم' || $header === 'name') {
                $headerMap['display_name_ar'] = $index;
            } elseif ($normHeader === 'الهاتف' || $header === 'phone') {
                $headerMap['phone'] = $index;
            } elseif ($normHeader === 'الواتساب' || $header === 'whatsapp') {
                $headerMap['whatsapp'] = $index;
            } elseif ($normHeader === 'المدينه' || $normHeader === 'المدينة' || $header === 'city') {
                $headerMap['city'] = $index;
            } elseif ($normHeader === 'الخدمه اساسيه' || $normHeader === 'الخدمة الأساسية' || $header === 'service') {
                $headerMap['service'] = $index;
            } elseif ($normHeader === 'سنوات الخبره' || $normHeader === 'سنوات الخبرة' || $header === 'experience' || $header === 'years_experience') {
                $headerMap['years_experience'] = $index;
            } elseif ($normHeader === 'السعر الاولي' || $normHeader === 'السعر الأولي' || $header === 'starting_price') {
                $headerMap['starting_price'] = $index;
            } elseif ($normHeader === 'نوع العمل' || $header === 'business_type') {
                $headerMap['business_type'] = $index;
            } elseif ($normHeader === 'الحاله' || $normHeader === 'الحالة' || $header === 'status') {
                $headerMap['status'] = $index;
            } elseif ($normHeader === 'نشط' || $header === 'is_active') {
                $headerMap['is_active'] = $index;
            }
        }

        // Ensure required fields exist in header
        if (!isset($headerMap['display_name_ar']) || !isset($headerMap['phone'])) {
            fclose($handle);
            Flash::error('الملف يجب أن يحتوي على عمودين على الأقل للاسم والهاتف.');
            $this->redirect('/admin/providers');
            return new Response();
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $imported = 0;
        $updated = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            
            // Extract values using header map
            $rawId = isset($headerMap['id']) && isset($row[$headerMap['id']]) ? trim($row[$headerMap['id']]) : null;
            $rawName = isset($row[$headerMap['display_name_ar']]) ? trim($row[$headerMap['display_name_ar']]) : '';
            $rawPhone = isset($row[$headerMap['phone']]) ? phone_format(trim($row[$headerMap['phone']])) : '';
            $rawWhatsapp = isset($headerMap['whatsapp']) && isset($row[$headerMap['whatsapp']]) ? phone_format(trim($row[$headerMap['whatsapp']])) : null;
            
            $rawCityName = isset($headerMap['city']) && isset($row[$headerMap['city']]) ? trim($row[$headerMap['city']]) : '';
            $rawServiceName = isset($headerMap['service']) && isset($row[$headerMap['service']]) ? trim($row[$headerMap['service']]) : '';
            
            $rawExp = isset($headerMap['years_experience']) && isset($row[$headerMap['years_experience']]) ? (int)$row[$headerMap['years_experience']] : 0;
            $rawPrice = isset($headerMap['starting_price']) && isset($row[$headerMap['starting_price']]) && trim($row[$headerMap['starting_price']]) !== '' ? (float)$row[$headerMap['starting_price']] : null;
            
            $rawBusinessType = isset($headerMap['business_type']) && isset($row[$headerMap['business_type']]) ? trim($row[$headerMap['business_type']]) : 'individual';
            if ($rawBusinessType === 'شركة' || $rawBusinessType === 'company') {
                $rawBusinessType = 'company';
            } else {
                $rawBusinessType = 'individual';
            }

            $rawStatus = isset($headerMap['status']) && isset($row[$headerMap['status']]) ? trim($row[$headerMap['status']]) : 'approved';
            if ($rawStatus === 'مقبول' || $rawStatus === 'approved' || $rawStatus === 'نشط') {
                $rawStatus = 'approved';
            } elseif ($rawStatus === 'معلق' || $rawStatus === 'pending') {
                $rawStatus = 'pending';
            } elseif ($rawStatus === 'مرفوض' || $rawStatus === 'rejected') {
                $rawStatus = 'rejected';
            } elseif ($rawStatus === 'معلق مؤقتا' || $rawStatus === 'suspended') {
                $rawStatus = 'suspended';
            }

            $rawIsActive = 1;
            if (isset($headerMap['is_active']) && isset($row[$headerMap['is_active']])) {
                $actVal = trim($row[$headerMap['is_active']]);
                if ($actVal === '0' || $actVal === 'لا' || strtolower($actVal) === 'false' || strtolower($actVal) === 'no') {
                    $rawIsActive = 0;
                }
            }

            // Map City and Service
            $cityId = 0;
            $serviceId = 0;
            
            $normCity = normalize_arabic($rawCityName);
            if (isset($citiesMap[$normCity])) {
                $cityId = $citiesMap[$normCity];
            } else {
                // If not found, use first city as default
                $cityId = !empty($citiesMap) ? reset($citiesMap) : 0;
            }

            $normService = normalize_arabic($rawServiceName);
            if (isset($servicesMap[$normService])) {
                $serviceId = $servicesMap[$normService];
            } else {
                // If not found, use first service as default
                $serviceId = !empty($servicesMap) ? reset($servicesMap) : 0;
            }

            if (empty($rawName) || empty($rawPhone)) {
                $errors[] = "السطر {$rowNum}: الاسم والهاتف مطلوبان.";
                continue;
            }

            // Find existing provider
            $existing = null;
            if ($rawId) {
                $existing = $this->repo->find((int)$rawId);
            }
            if (!$existing && !empty($rawPhone)) {
                $existingPhone = $db->fetch("SELECT * FROM `providers` WHERE `phone` = ? AND `deleted_at` IS NULL LIMIT 1", [$rawPhone]);
                if ($existingPhone) {
                    $existing = $this->repo->find((int)$existingPhone['id']);
                }
            }

            // Prepare validation array
            $slugVal = $existing ? $existing['slug'] : slugify($rawName);
            $validationData = [
                'display_name_ar' => $rawName,
                'business_type' => $rawBusinessType,
                'phone' => $rawPhone,
                'whatsapp' => $rawWhatsapp,
                'city_id' => $cityId,
                'primary_service_id' => $serviceId,
                'years_experience' => $rawExp,
                'starting_price' => $rawPrice,
                'price_unit' => $existing ? $existing['price_unit'] : 'hour',
                'verified' => $existing ? $existing['verified'] : 0,
                'is_active' => $rawIsActive,
                'sort_weight' => $existing ? $existing['sort_weight'] : 0,
                'status' => $rawStatus,
                'slug' => $slugVal,
                'areas' => $existing ? $existing['areas'] : [],
                'services' => $existing ? $existing['services'] : [],
                'logo' => $existing ? $existing['logo'] : null,
                'work_photos' => $existing ? $existing['work_photos'] : [],
                'meta_title_ar' => $existing ? $existing['meta_title_ar'] : null,
                'meta_description_ar' => $existing ? $existing['meta_description_ar'] : null,
            ];

            [$valErrors, $dto] = $this->validation->validate($validationData, $existing ? (int)$existing['id'] : null);
            if (!empty($valErrors)) {
                $flatErrors = [];
                foreach ($valErrors as $fieldErrors) {
                    $flatErrors = array_merge($flatErrors, $fieldErrors);
                }
                $errors[] = "السطر {$rowNum} [{$rawName}]: " . implode(' ', $flatErrors);
                continue;
            }

            try {
                if ($existing) {
                    $this->service->updateProvider((int)$existing['id'], $dto, $adminUserId, $ipAddress);
                    $updated++;
                } else {
                    $this->service->createProvider($dto, $adminUserId, $ipAddress);
                    $imported++;
                }
            } catch (\Throwable $e) {
                $errors[] = "السطر {$rowNum} [{$rawName}]: خطأ أثناء حفظ البيانات: " . $e->getMessage();
            }
        }

        fclose($handle);

        $report = "تم الاستيراد بنجاح: إضافة {$imported} مزودين جدد، وتحديث {$updated} مزودين.";
        if (!empty($errors)) {
            $report .= " حدثت أخطاء في بعض السطور: \n" . implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $report .= "\n...وغيرها من الأخطاء (" . count($errors) . " إجمالاً)";
            }
            Flash::warning($report);
        } else {
            Flash::success($report);
        }

        $this->redirect('/admin/providers');
        return new Response();
    }

    /**
     * Local view renderer helper.
     */
    private function renderView(string $view, array $data = []): string
    {
        $viewFile = __DIR__ . '/Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Providers View file not found: " . $viewFile);
        }
        extract($data);
        ob_start();
        require $viewFile;
        return ob_get_clean();
    }

    /**
     * List all provider drafts pending review.
     */
    public function listDrafts(Request $request): Response
    {
        $draftRepo = new ProviderDraftRepository();
        $items = $draftRepo->getPendingDrafts();

        $content = $this->renderView('drafts', [
            'items' => $items
        ]);

        return $this->render('layouts/admin', [
            'title' => 'مراجعة طلبات التسجيل والتعديل المعلقة',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة مزودي الخدمات', 'url' => '/admin/providers'],
                ['label' => 'طلبات المراجعة المعلقة']
            ]
        ]);
    }

    /**
     * Compare draft profile against current live profile side-by-side.
     */
    public function compareDraft(Request $request, string $id): Response
    {
        $id = (int)$id;
        $draftRepo = new ProviderDraftRepository();
        $draft = $draftRepo->find($id);

        if (!$draft || $draft['status'] !== 'pending_review') {
            Flash::error('الطلب المحدد غير موجود أو غير معلق للمراجعة حالياً.');
            $this->redirect('/admin/providers/drafts');
            return new Response();
        }

        $liveProvider = null;
        if ($draft['provider_id']) {
            $liveProvider = $this->repo->find($draft['provider_id']);
        }

        // Fetch cities, services, and areas for full details translation
        $db = Database::getInstance();
        $citiesRaw = $db->fetchAll("SELECT id, display_name_ar FROM `cities` WHERE `is_deleted` = 0");
        $servicesRaw = $db->fetchAll("SELECT id, display_name_ar FROM `services` WHERE `is_deleted` = 0");
        $areasRaw = $db->fetchAll("SELECT id, display_name_ar FROM `areas` WHERE `is_deleted` = 0");

        $cities = array_column($citiesRaw, 'display_name_ar', 'id');
        $services = array_column($servicesRaw, 'display_name_ar', 'id');
        $areas = array_column($areasRaw, 'display_name_ar', 'id');

        $content = $this->renderView('compare', [
            'draft' => $draft,
            'liveProvider' => $liveProvider,
            'cities' => $cities,
            'services' => $services,
            'areas' => $areas
        ]);

        return $this->render('layouts/admin', [
            'title' => 'مقارنة المسودة مع الملف المنشور',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة مزودي الخدمات', 'url' => '/admin/providers'],
                ['label' => 'طلبات المراجعة المعلقة', 'url' => '/admin/providers/drafts'],
                ['label' => 'مقارنة التغييرات']
            ]
        ]);
    }

    /**
     * Approve and publish draft updates/registration.
     */
    public function approveDraft(Request $request, string $id): Response
    {
        $id = (int)$id;
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            $this->redirect("/admin/providers/drafts/{$id}/compare");
            return new Response();
        }

        $draftRepo = new ProviderDraftRepository();
        $accountRepo = new ProviderAccountRepository();
        $draft = $draftRepo->find($id);

        if (!$draft || $draft['status'] !== 'pending_review') {
            Flash::error('الطلب المحدد غير معلق للمراجعة.');
            $this->redirect('/admin/providers/drafts');
            return new Response();
        }

        $liveProvider = null;
        if ($draft['provider_id']) {
            $liveProvider = $this->repo->find($draft['provider_id']);
        }

        // Map draft fields to provider update structure
        $providerData = [
            'slug' => $draft['slug'],
            'display_name_ar' => $draft['display_name_ar'],
            'business_type' => $draft['business_type'],
            'phone' => $draft['phone'],
            'whatsapp' => $draft['whatsapp'],
            'city_id' => $draft['city_id'],
            'primary_service_id' => $draft['primary_service_id'],
            'short_description_ar' => $draft['short_description_ar'],
            'description_ar' => $draft['description_ar'],
            'years_experience' => $draft['years_experience'],
            'starting_price' => $draft['starting_price'],
            'price_unit' => $draft['price_unit'],
            'verified' => $liveProvider ? $liveProvider['verified'] : 0,
            'is_active' => 1,
            'sort_weight' => $liveProvider ? $liveProvider['sort_weight'] : 0,
            'status' => 'approved',
            'website' => $draft['website'],
            'working_hours' => $draft['working_hours'],
            'social_links' => $draft['social_links'],
            'areas' => $draft['coverage_areas_json'],
            'services' => $draft['secondary_services_json'],
            'logo' => $draft['logo_path'],
            'work_photos' => $draft['work_photos_json'],
            'meta_title_ar' => $draft['meta_title_ar'],
            'meta_description_ar' => $draft['meta_description_ar'],
        ];

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $db = Database::getInstance();

        try {
            if ($draft['provider_id']) {
                // Update existing provider
                $this->repo->update($draft['provider_id'], $providerData);
                
                // Set draft status as approved
                $draft['status'] = 'approved';
                $draftRepo->update($id, $draft);

                // Audit Log
                $db->execute(
                    "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`) 
                     VALUES (:admin_id, 'approve_profile_update', 'providers', :entity_id, :ip)",
                    [
                        'admin_id' => $adminUserId,
                        'entity_id' => $draft['provider_id'],
                        'ip' => hash('sha256', $ipAddress)
                    ]
                );

                Flash::success('تم قبول التحديثات ونشرها بنجاح للمزود: ' . $draft['display_name_ar']);
            } else {
                // Create new provider profile
                $newProviderId = $this->repo->create($providerData);

                // Link to provider account
                $accountRepo->linkProvider($draft['provider_account_id'], $newProviderId);

                // Update draft with linked provider ID and status
                $draft['provider_id'] = $newProviderId;
                $draft['status'] = 'approved';
                $draftRepo->update($id, $draft);

                // Audit Log
                $db->execute(
                    "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`) 
                     VALUES (:admin_id, 'approve_registration', 'providers', :entity_id, :ip)",
                    [
                        'admin_id' => $adminUserId,
                        'entity_id' => $newProviderId,
                        'ip' => hash('sha256', $ipAddress)
                    ]
                );

                Flash::success('تم قبول طلب التسجيل وتأسيس الملف الشخصي بنجاح للمزود الجديد: ' . $draft['display_name_ar']);
            }
        } catch (\Throwable $e) {
            Flash::error('خطأ أثناء النشر: ' . $e->getMessage());
            $this->redirect("/admin/providers/drafts/{$id}/compare");
            return new Response();
        }

        $this->redirect('/admin/providers/drafts');
        return new Response();
    }

    /**
     * Reject draft updates with comments/reasons.
     */
    public function rejectDraft(Request $request, string $id): Response
    {
        $id = (int)$id;
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            $this->redirect("/admin/providers/drafts/{$id}/compare");
            return new Response();
        }

        $draftRepo = new ProviderDraftRepository();
        $draft = $draftRepo->find($id);

        if (!$draft || $draft['status'] !== 'pending_review') {
            Flash::error('الطلب المحدد غير معلق للمراجعة.');
            $this->redirect('/admin/providers/drafts');
            return new Response();
        }

        $reason = trim($request->input('rejection_reason', ''));
        if (empty($reason)) {
            Flash::error('يرجى تحديد سبب الرفض لتوجيه الحرفي.');
            $this->redirect("/admin/providers/drafts/{$id}/compare");
            return new Response();
        }

        $draft['status'] = 'rejected';
        $draft['admin_notes'] = $reason;
        $draftRepo->update($id, $draft);

        // Audit Log
        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO `audit_logs` (`admin_user_id`, `action`, `entity_type`, `entity_id`, `ip_hash`, `new_value_json`) 
             VALUES (:admin_id, 'reject_draft', 'provider_drafts', :entity_id, :ip, :notes)",
            [
                'admin_id' => $adminUserId,
                'entity_id' => $id,
                'ip' => hash('sha256', $ipAddress),
                'notes' => json_encode(['reason' => $reason], JSON_UNESCAPED_UNICODE)
            ]
        );

        Flash::success('تم رفض التعديلات وإرجاعها للحرفي مع الملاحظات.');
        $this->redirect('/admin/providers/drafts');
        return new Response();
    }
}
