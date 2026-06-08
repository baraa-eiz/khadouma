<?php
/**
 * CitiesController.php
 * Khadomeh Cities Admin CRUD Controller
 */

namespace App\Modules\Locations;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Core\Database;

class CitiesController extends Controller
{
    private CitiesService $service;
    private CitiesValidation $validation;

    public function __construct()
    {
        $repository = new CitiesRepository();
        $this->service = new CitiesService($repository);
        $this->validation = new CitiesValidation($repository);
    }

    /**
     * Display a paginated list of cities.
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

        $sortBy = $request->query('sort_by', 'sort_order');
        $sortDir = $request->query('sort_dir', 'ASC');

        $page = max(1, (int)$request->query('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $items = $this->service->searchCities($criteria, $sortBy, $sortDir, $limit, $offset);
        $totalItems = $this->service->countCities($criteria);
        $totalPages = (int)ceil($totalItems / $limit);

        $viewData = [
            'items' => $items,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit,
            'keyword' => $keyword,
            'is_active' => $isActive,
            'is_deleted' => $isDeleted,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ];

        $content = $this->renderView('cities/list', $viewData);

        return $this->render('layouts/admin', [
            'title' => 'إدارة المدن',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المدن']
            ]
        ]);
    }

    /**
     * Show form to create a new city.
     */
    public function create(Request $request): Response
    {
        $content = $this->renderView('cities/create', [
            'errors' => [],
            'old' => [],
        ]);

        return $this->render('layouts/admin', [
            'title' => 'إضافة مدينة جديدة',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المدن', 'url' => '/admin/cities'],
                ['label' => 'إضافة مدينة']
            ]
        ]);
    }

    /**
     * Store newly created city in database.
     */
    public function store(Request $request): Response
    {
        $this->validateCsrf($request);

        [$errors, $dto] = $this->validation->validate($request->input());

        if (!empty($errors)) {
            $content = $this->renderView('cities/create', [
                'errors' => $errors,
                'old' => $request->input(),
            ]);

            return $this->render('layouts/admin', [
                'title' => 'إضافة مدينة جديدة',
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة المدن', 'url' => '/admin/cities'],
                    ['label' => 'إضافة مدينة']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->createCity($dto, $adminUserId, $ipAddress);

        Flash::success('تم إضافة المدينة بنجاح.');
        $this->redirect('/admin/cities');
        return new Response();
    }

    /**
     * Display a single city details.
     */
    public function show(Request $request, string $id): Response
    {
        $item = $this->service->getById((int)$id);
        if (!$item) {
            Flash::error('المدينة المطلوبة غير موجودة.');
            $this->redirect('/admin/cities');
            return new Response();
        }

        $db = Database::getInstance();
        $auditLogs = $db->fetchAll(
            "SELECT a.*, u.name as admin_name 
             FROM `audit_logs` a 
             LEFT JOIN `admin_users` u ON a.`admin_user_id` = u.`id` 
             WHERE a.`entity_type` = 'cities' AND a.`entity_id` = :entity_id 
             ORDER BY a.`created_at` DESC",
            ['entity_id' => $id]
        );

        $content = $this->renderView('cities/show', [
            'item' => $item,
            'auditLogs' => $auditLogs,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تفاصيل المدينة: ' . $item['display_name_ar'],
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المدن', 'url' => '/admin/cities'],
                ['label' => $item['display_name_ar']]
            ]
        ]);
    }

    /**
     * Show form to edit a city.
     */
    public function edit(Request $request, string $id): Response
    {
        $item = $this->service->getById((int)$id);
        if (!$item) {
            Flash::error('المدينة المطلوبة غير موجودة.');
            $this->redirect('/admin/cities');
            return new Response();
        }

        $content = $this->renderView('cities/edit', [
            'item' => $item,
            'errors' => [],
            'old' => $item,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تعديل المدينة: ' . $item['display_name_ar'],
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المدن', 'url' => '/admin/cities'],
                ['label' => 'تعديل المدينة']
            ]
        ]);
    }

    /**
     * Update an existing city in database.
     */
    public function update(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $item = $this->service->getById($idVal);
        if (!$item) {
            Flash::error('المدينة المطلوبة غير موجودة.');
            $this->redirect('/admin/cities');
            return new Response();
        }

        $data = $request->input();
        $data['public_id'] = $item['public_id'];

        [$errors, $dto] = $this->validation->validate($data, $idVal);

        if (!empty($errors)) {
            $content = $this->renderView('cities/edit', [
                'item' => $item,
                'errors' => $errors,
                'old' => $data,
            ]);

            return $this->render('layouts/admin', [
                'title' => 'تعديل المدينة: ' . $item['display_name_ar'],
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة المدن', 'url' => '/admin/cities'],
                    ['label' => 'تعديل المدينة']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->updateCity($idVal, $dto, $adminUserId, $ipAddress);

        Flash::success('تم تحديث المدينة بنجاح.');
        $this->redirect('/admin/cities');
        return new Response();
    }

    /**
     * Soft delete a city.
     */
    public function delete(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->softDeleteCity($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم حذف المدينة بنجاح (حذف مؤقت).');
        } else {
            Flash::error('فشل في حذف المدينة.');
        }

        $this->redirect('/admin/cities');
        return new Response();
    }

    /**
     * Restore a soft-deleted city.
     */
    public function restore(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->restoreCity($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم استعادة المدينة بنجاح.');
        } else {
            Flash::error('فشل في استعادة المدينة.');
        }

        $this->redirect('/admin/cities');
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
