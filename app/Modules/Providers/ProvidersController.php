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
}
