<?php
/**
 * AreasController.php
 * Khadomeh Areas Admin CRUD Controller
 */

namespace App\Modules\Locations;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Core\Database;

class AreasController extends Controller
{
    private AreasService $service;
    private AreasValidation $validation;
    private CitiesRepository $citiesRepo;

    public function __construct()
    {
        $repository = new AreasRepository();
        $this->citiesRepo = new CitiesRepository();
        $this->service = new AreasService($repository);
        $this->validation = new AreasValidation($repository, $this->citiesRepo);
    }

    /**
     * Display a paginated list of areas.
     */
    public function index(Request $request): Response
    {
        $criteria = [];
        
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

        $cityId = $request->query('city_id');
        if ($cityId !== null && $cityId !== '') {
            $criteria['city_id'] = (int)$cityId;
        }

        $sortBy = $request->query('sort_by', 'sort_order');
        $sortDir = $request->query('sort_dir', 'ASC');

        $page = max(1, (int)$request->query('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $items = $this->service->searchAreas($criteria, $sortBy, $sortDir, $limit, $offset);
        $totalItems = $this->service->countAreas($criteria);
        $totalPages = (int)ceil($totalItems / $limit);

        // Fetch active cities for the filter dropdown
        $cities = $this->citiesRepo->search(['is_deleted' => 0, 'is_active' => 1], 'sort_order', 'ASC', 100);

        $viewData = [
            'items' => $items,
            'cities' => $cities,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit,
            'keyword' => $keyword,
            'is_active' => $isActive,
            'is_deleted' => $isDeleted,
            'city_id' => $cityId,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ];

        $content = $this->renderView('areas/list', $viewData);

        return $this->render('layouts/admin', [
            'title' => 'إدارة المناطق',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المناطق']
            ]
        ]);
    }

    /**
     * Show form to create a new area.
     */
    public function create(Request $request): Response
    {
        $cities = $this->citiesRepo->search(['is_deleted' => 0, 'is_active' => 1], 'sort_order', 'ASC', 100);

        $content = $this->renderView('areas/create', [
            'errors' => [],
            'old' => [],
            'cities' => $cities,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'إضافة منطقة جديدة',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المناطق', 'url' => '/admin/areas'],
                ['label' => 'إضافة منطقة']
            ]
        ]);
    }

    /**
     * Store newly created area in database.
     */
    public function store(Request $request): Response
    {
        $this->validateCsrf($request);

        [$errors, $dto] = $this->validation->validate($request->input());

        if (!empty($errors)) {
            $cities = $this->citiesRepo->search(['is_deleted' => 0, 'is_active' => 1], 'sort_order', 'ASC', 100);
            $content = $this->renderView('areas/create', [
                'errors' => $errors,
                'old' => $request->input(),
                'cities' => $cities,
            ]);

            return $this->render('layouts/admin', [
                'title' => 'إضافة منطقة جديدة',
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة المناطق', 'url' => '/admin/areas'],
                    ['label' => 'إضافة منطقة']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->createArea($dto, $adminUserId, $ipAddress);

        Flash::success('تم إضافة المنطقة بنجاح.');
        $this->redirect('/admin/areas');
        return new Response();
    }

    /**
     * Display a single area details.
     */
    public function show(Request $request, string $id): Response
    {
        $item = $this->service->getById((int)$id);
        if (!$item) {
            Flash::error('المنطقة المطلوبة غير موجودة.');
            $this->redirect('/admin/areas');
            return new Response();
        }

        $db = Database::getInstance();
        $auditLogs = $db->fetchAll(
            "SELECT a.*, u.name as admin_name 
             FROM `audit_logs` a 
             LEFT JOIN `admin_users` u ON a.`admin_user_id` = u.`id` 
             WHERE a.`entity_type` = 'areas' AND a.`entity_id` = :entity_id 
             ORDER BY a.`created_at` DESC",
            ['entity_id' => $id]
        );

        $content = $this->renderView('areas/show', [
            'item' => $item,
            'auditLogs' => $auditLogs,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تفاصيل المنطقة: ' . $item['display_name_ar'],
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المناطق', 'url' => '/admin/areas'],
                ['label' => $item['display_name_ar']]
            ]
        ]);
    }

    /**
     * Show form to edit an area.
     */
    public function edit(Request $request, string $id): Response
    {
        $item = $this->service->getById((int)$id);
        if (!$item) {
            Flash::error('المنطقة المطلوبة غير موجودة.');
            $this->redirect('/admin/areas');
            return new Response();
        }

        $cities = $this->citiesRepo->search(['is_deleted' => 0, 'is_active' => 1], 'sort_order', 'ASC', 100);

        $content = $this->renderView('areas/edit', [
            'item' => $item,
            'errors' => [],
            'old' => $item,
            'cities' => $cities,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تعديل المنطقة: ' . $item['display_name_ar'],
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المناطق', 'url' => '/admin/areas'],
                ['label' => 'تعديل المنطقة']
            ]
        ]);
    }

    /**
     * Update an existing area in database.
     */
    public function update(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $item = $this->service->getById($idVal);
        if (!$item) {
            Flash::error('المنطقة المطلوبة غير موجودة.');
            $this->redirect('/admin/areas');
            return new Response();
        }

        $data = $request->input();
        $data['public_id'] = $item['public_id'];

        [$errors, $dto] = $this->validation->validate($data, $idVal);

        if (!empty($errors)) {
            $cities = $this->citiesRepo->search(['is_deleted' => 0, 'is_active' => 1], 'sort_order', 'ASC', 100);
            $content = $this->renderView('areas/edit', [
                'item' => $item,
                'errors' => $errors,
                'old' => $data,
                'cities' => $cities,
            ]);

            return $this->render('layouts/admin', [
                'title' => 'تعديل المنطقة: ' . $item['display_name_ar'],
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة المناطق', 'url' => '/admin/areas'],
                    ['label' => 'تعديل المنطقة']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->updateArea($idVal, $dto, $adminUserId, $ipAddress);

        Flash::success('تم تحديث المنطقة بنجاح.');
        $this->redirect('/admin/areas');
        return new Response();
    }

    /**
     * Soft delete an area.
     */
    public function delete(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->softDeleteArea($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم حذف المنطقة بنجاح (حذف مؤقت).');
        } else {
            Flash::error('فشل في حذف المنطقة.');
        }

        $this->redirect('/admin/areas');
        return new Response();
    }

    /**
     * Restore a soft-deleted area.
     */
    public function restore(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->restoreArea($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم استعادة المنطقة بنجاح.');
        } else {
            Flash::error('فشل في استعادة المنطقة.');
        }

        $this->redirect('/admin/areas');
        return new Response();
    }

    /**
     * View rendering helper relative to the Locations module Views directory.
     */
    private function renderView(string $view, array $data = []): string
    {
        $viewFile = __DIR__ . '/Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Locations View file not found: " . $viewFile);
        }
        extract($data);
        ob_start();
        require $viewFile;
        return ob_get_clean();
    }
}
