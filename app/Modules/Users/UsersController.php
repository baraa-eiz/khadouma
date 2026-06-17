<?php
/**
 * UsersController.php
 * Khadomeh Users Admin CRUD Controller
 */

namespace App\Modules\Users;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Core\Database;

class UsersController extends Controller
{
    private UsersService $service;
    private UsersValidation $validation;

    public function __construct()
    {
        $repository = new UsersRepository();
        $this->service = new UsersService($repository);
        $this->validation = new UsersValidation($repository);
    }

    /**
     * Display a paginated list of users.
     */
    public function index(Request $request): Response
    {
        $criteria = [];
        
        $keyword = $request->query('keyword');
        if ($keyword !== null && trim($keyword) !== '') {
            $criteria['keyword'] = trim($keyword);
        }

        $status = $request->query('status');
        if ($status !== null && $status !== '') {
            $criteria['status'] = trim($status);
        }

        $isDeleted = $request->query('is_deleted');
        if ($isDeleted !== null && $isDeleted !== '') {
            $criteria['is_deleted'] = (int)$isDeleted;
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'DESC');

        $page = max(1, (int)$request->query('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $items = $this->service->searchUsers($criteria, $sortBy, $sortDir, $limit, $offset);
        $totalItems = $this->service->countUsers($criteria);
        $totalPages = (int)ceil($totalItems / $limit);

        $viewData = [
            'items' => $items,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit,
            'keyword' => $keyword,
            'status' => $status,
            'is_deleted' => $isDeleted,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ];

        $content = $this->renderView('list', $viewData);

        return $this->render('layouts/admin', [
            'title' => 'إدارة المستخدمين',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المستخدمين']
            ]
        ]);
    }

    /**
     * Show form to create a new user.
     */
    public function create(Request $request): Response
    {
        $db = Database::getInstance();
        $cities = $db->fetchAll("SELECT * FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");
        $areas = $db->fetchAll("SELECT * FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");

        $content = $this->renderView('create', [
            'errors' => [],
            'old' => [],
            'cities' => $cities,
            'areas' => $areas,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'إضافة مستخدم جديد',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المستخدمين', 'url' => '/admin/users'],
                ['label' => 'إضافة مستخدم']
            ]
        ]);
    }

    /**
     * Store newly created user.
     */
    public function store(Request $request): Response
    {
        $this->validateCsrf($request);

        [$errors, $dto] = $this->validation->validate($request->input());

        if (!empty($errors)) {
            $db = Database::getInstance();
            $cities = $db->fetchAll("SELECT * FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");
            $areas = $db->fetchAll("SELECT * FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");

            $content = $this->renderView('create', [
                'errors' => $errors,
                'old' => $request->input(),
                'cities' => $cities,
                'areas' => $areas,
            ]);

            return $this->render('layouts/admin', [
                'title' => 'إضافة مستخدم جديد',
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة المستخدمين', 'url' => '/admin/users'],
                    ['label' => 'إضافة مستخدم']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->createUser($dto, $adminUserId, $ipAddress);

        Flash::success('تم إضافة المستخدم بنجاح.');
        $this->redirect('/admin/users');
        return new Response();
    }

    /**
     * Display single user details.
     */
    public function show(Request $request, string $id): Response
    {
        $item = $this->service->getUserById((int)$id);
        if (!$item) {
            Flash::error('المستخدم المطلوب غير موجود.');
            $this->redirect('/admin/users');
            return new Response();
        }

        // Fetch audit logs
        $db = Database::getInstance();
        $auditLogs = $db->fetchAll(
            "SELECT a.*, u.name as admin_name 
             FROM `audit_logs` a 
             LEFT JOIN `admin_users` u ON a.`admin_user_id` = u.`id` 
             WHERE a.`entity_type` = 'users' AND a.`entity_id` = :entity_id 
             ORDER BY a.`created_at` DESC",
            ['entity_id' => $id]
        );

        $content = $this->renderView('show', [
            'item' => $item,
            'auditLogs' => $auditLogs,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تفاصيل المستخدم: ' . $item->display_name,
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المستخدمين', 'url' => '/admin/users'],
                ['label' => $item->display_name]
            ]
        ]);
    }

    /**
     * Show form to edit user.
     */
    public function edit(Request $request, string $id): Response
    {
        $item = $this->service->getUserById((int)$id);
        if (!$item) {
            Flash::error('المستخدم المطلوب غير موجود.');
            $this->redirect('/admin/users');
            return new Response();
        }

        $db = Database::getInstance();
        $cities = $db->fetchAll("SELECT * FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");
        $areas = $db->fetchAll("SELECT * FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");

        $content = $this->renderView('edit', [
            'item' => $item,
            'errors' => [],
            'old' => $item->toArray(),
            'cities' => $cities,
            'areas' => $areas,
        ]);

        return $this->render('layouts/admin', [
            'title' => 'تعديل المستخدم: ' . $item->display_name,
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'إدارة المستخدمين', 'url' => '/admin/users'],
                ['label' => 'تعديل المستخدم']
            ]
        ]);
    }

    /**
     * Update user.
     */
    public function update(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $item = $this->service->getUserById($idVal);
        if (!$item) {
            Flash::error('المستخدم المطلوب غير موجود.');
            $this->redirect('/admin/users');
            return new Response();
        }

        $data = $request->input();
        $data['public_id'] = $item->public_id;

        [$errors, $dto] = $this->validation->validate($data, $idVal);

        if (!empty($errors)) {
            $db = Database::getInstance();
            $cities = $db->fetchAll("SELECT * FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");
            $areas = $db->fetchAll("SELECT * FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");

            $content = $this->renderView('edit', [
                'item' => $item,
                'errors' => $errors,
                'old' => $data,
                'cities' => $cities,
                'areas' => $areas,
            ]);

            return $this->render('layouts/admin', [
                'title' => 'تعديل المستخدم: ' . $item->display_name,
                'content' => $content,
                'breadcrumbs' => [
                    ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                    ['label' => 'إدارة المستخدمين', 'url' => '/admin/users'],
                    ['label' => 'تعديل المستخدم']
                ]
            ]);
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->service->updateUser($idVal, $dto, $adminUserId, $ipAddress);

        Flash::success('تم تحديث بيانات المستخدم بنجاح.');
        $this->redirect('/admin/users');
        return new Response();
    }

    /**
     * Soft delete user.
     */
    public function delete(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->softDeleteUser($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم حذف حساب المستخدم مؤقتاً.');
        } else {
            Flash::error('فشل في حذف حساب المستخدم.');
        }

        $this->redirect('/admin/users');
        return new Response();
    }

    /**
     * Restore soft-deleted user.
     */
    public function restore(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->restoreUser($idVal, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم استعادة حساب المستخدم بنجاح.');
        } else {
            Flash::error('فشل في استعادة حساب المستخدم.');
        }

        $this->redirect('/admin/users');
        return new Response();
    }

    /**
     * Change user status (active/suspended).
     */
    public function changeStatus(Request $request, string $id): Response
    {
        $this->validateCsrf($request);
        $idVal = (int)$id;
        $status = trim($request->input('status', ''));

        if (!in_array($status, ['active', 'suspended'])) {
            Flash::error('الحالة المطلوبة غير صالحة.');
            $this->redirect('/admin/users');
            return new Response();
        }

        $adminUserId = (int)Session::get('admin_user_id');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $success = $this->service->toggleUserStatus($idVal, $status, $adminUserId, $ipAddress);

        if ($success) {
            Flash::success('تم تعديل حالة المستخدم بنجاح.');
        } else {
            Flash::error('فشل في تعديل حالة المستخدم.');
        }

        $this->redirect('/admin/users/' . $idVal);
        return new Response();
    }

    /**
     * Local view renderer helper.
     */
    private function renderView(string $view, array $data = []): string
    {
        $viewFile = __DIR__ . '/Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Users View file not found: " . $viewFile);
        }
        extract($data);
        ob_start();
        require $viewFile;
        return ob_get_clean();
    }
}
