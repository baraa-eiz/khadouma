<?php
/**
 * ServicesController.php
 * Khadomeh Services Admin CRUD Controller
 */

namespace App\Modules\Services;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Core\Database;

class ServicesController extends Controller
{
    private ServicesService $service;
    private ServicesValidation $validation;

    public function __construct()
    {
        $repository = new ServicesRepository();
        $this->service = new ServicesService($repository);
        $this->validation = new ServicesValidation($repository);
    }

    /**
     * Display a paginated list of services.
     */
    public function index(Request $request): Response
    {
        $criteria = [];
        
        // Handle search keyword
        $keyword = $request->query('keyword');
        if ($keyword !== null && trim($keyword) !== '') {
            $criteria['keyword'] = trim($keyword);
        }

        // Handle active/deleted filters
        $isActive = $request->query('is_active');
        if ($isActive !== null && $isActive !== '') {
            $criteria['is_active'] = (int)$isActive;
        }

        $isDeleted = $request->query('is_deleted');
        if ($isDeleted !== null && $isDeleted !== '') {
            $criteria['is_deleted'] = (int)$isDeleted;
        }

        // Sorting params
        $sortBy = $request->query('sort_by', 'sort_order');
        $sortDir = $request->query('sort_dir', 'ASC');

        // Pagination setup
        $page = max(1, (int)$request->query('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        // Fetch data
        $items = $this->service->searchServices($criteria, $sortBy, $sortDir, $limit, $offset);
        $totalItems = $this->service->countServices($criteria);
        $totalPages = (int)ceil($totalItems / $limit);

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
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ];

        $content = $this->renderView('list', $viewData);

        return $this->render('layouts/admin', [
            'title' => 'إدارة الخدمات',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة الخدمات']
            ]
        ]);
    }

    /**
     * Show form to create a new service.
     */
    public function create(Request $request): Response
    {
        $content = $this->renderView('create', [
            'errors' => [],
            'old' => [],
        ]);

        return $this->render('layouts/admin', [
            'title' => 'إضافة خدمة جديدة',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة الخدمات', 'url' => '/admin/services'],
                ['label' => 'إضافة خدمة']
            ]
        ]);
    }

    /**
     * Store newly created service in database.
     */
    public function store(Request $request): Response
    {
        $this->validateCsrf($request);

        [$errors, $dto] = $this->validation->validate($request->input());

        if (!empty($errors)) {
            $content = $this->renderView('create', [
                'errors' => $errors,
                'old' => $request->input(),
            ]);

            return $this->render('layouts/admin', [
                'title' => 'إضافة خدمة جديدة',
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة الخدمات', 'url' => '/admin/services'],
                    ['label' => 'إضافة خدمة']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->createService($dto, $adminUserId, $ipAddress);

        Flash::success('تم إضافة الخدمة بنجاح.');
        $this->redirect('/admin/services');
        return new Response();
    }

    /**
     * Display a single service details.
     */
    public function show(Request $request, string $id): Response
    {
        $item = $this->service->getById((int)$id);
        if (!$item) {
            Flash::error('الخدمة المطلوبة غير موجودة.');
            $this->redirect('/admin/services');
            return new Response();
        }

        // Fetch audit logs for this service
        $db = Database::getInstance();
        $auditLogs = $db->fetchAll(
            "SELECT a.*, u.name as admin_name 
             FROM `audit_logs` a 
             LEFT JOIN `admin_users` u ON a.`admin_user_id` = u.`id` 
             WHERE a.`entity_type` = 'services' AND a.`entity_id` = :entity_id 
             ORDER BY a.`created_at` DESC",
            ['entity_id' => $id]
        );

        $content = $this->renderView('show', [
            'item' => $item,
            'auditLogs' => $auditLogs,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تفاصيل الخدمة: ' . $item['display_name_ar'],
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة الخدمات', 'url' => '/admin/services'],
                ['label' => $item['display_name_ar']]
            ]
        ]);
    }

    /**
     * Show form to edit a service.
     */
    public function edit(Request $request, string $id): Response
    {
        $item = $this->service->getById((int)$id);
        if (!$item) {
            Flash::error('الخدمة المطلوبة غير موجودة.');
            $this->redirect('/admin/services');
            return new Response();
        }

        $content = $this->renderView('edit', [
            'item' => $item,
            'errors' => [],
            'old' => $item,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تعديل الخدمة: ' . $item['display_name_ar'],
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة الخدمات', 'url' => '/admin/services'],
                ['label' => 'تعديل الخدمة']
            ]
        ]);
    }

    /**
     * Update an existing service in database.
     */
    public function update(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $item = $this->service->getById($idVal);
        if (!$item) {
            Flash::error('الخدمة المطلوبة غير موجودة.');
            $this->redirect('/admin/services');
            return new Response();
        }

        // Pass public_id to validator to ensure DTO is complete and matches
        $data = $request->input();
        $data['public_id'] = $item['public_id'];

        [$errors, $dto] = $this->validation->validate($data, $idVal);

        if (!empty($errors)) {
            $content = $this->renderView('edit', [
                'item' => $item,
                'errors' => $errors,
                'old' => $data,
            ]);

            return $this->render('layouts/admin', [
                'title' => 'تعديل الخدمة: ' . $item['display_name_ar'],
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة الخدمات', 'url' => '/admin/services'],
                    ['label' => 'تعديل الخدمة']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->updateService($idVal, $dto, $adminUserId, $ipAddress);

        Flash::success('تم تحديث الخدمة بنجاح.');
        $this->redirect('/admin/services');
        return new Response();
    }

    /**
     * Soft delete a service.
     */
    public function delete(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->softDeleteService($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم حذف الخدمة بنجاح (حذف مؤقت).');
        } else {
            Flash::error('فشل في حذف الخدمة.');
        }

        $this->redirect('/admin/services');
        return new Response();
    }

    /**
     * Restore a soft-deleted service.
     */
    public function restore(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->restoreService($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم استعادة الخدمة بنجاح.');
        } else {
            Flash::error('فشل في استعادة الخدمة.');
        }

        $this->redirect('/admin/services');
        return new Response();
    }

    /**
     * Local view renderer helper.
     */
    private function renderView(string $view, array $data = []): string
    {
        $viewFile = __DIR__ . '/Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Services View file not found: " . $viewFile);
        }
        extract($data);
        ob_start();
        require $viewFile;
        return ob_get_clean();
    }
}
