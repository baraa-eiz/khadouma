<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Services\AdminService;

class AuthController extends Controller
{
    private AdminService $adminService;

    public function __construct()
    {
        $this->adminService = new AdminService();
    }

    /**
     * Show the login form.
     */
    public function showLogin(Request $request): Response
    {
        return $this->render('admin/login');
    }

    /**
     * Process the login request.
     */
    public function login(Request $request): Response
    {
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/admin/login');
        }

        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');

        if (empty($email) || empty($password)) {
            Flash::error('يرجى إدخال البريد الإلكتروني وكلمة المرور.');
            return Response::redirect('/admin/login');
        }

        $admin = $this->adminService->authenticate($email, $password, $request->getIp());

        if (!$admin) {
            Flash::error('البريد الإلكتروني أو كلمة المرور غير صحيحة.');
            return Response::redirect('/admin/login');
        }

        // Set session parameters
        Session::regenerate();
        Session::set('admin_user_id', $admin['id']);
        Session::set('admin_user_name', $admin['name']);
        Session::set('admin_user_role', $admin['role']);
        Session::set('admin_last_activity', time());

        Flash::success('أهلاً بك مجدداً في لوحة التحكم.');
        return Response::redirect('/admin/dashboard');
    }

    /**
     * Process logout request.
     */
    public function logout(Request $request): Response
    {
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/admin/dashboard');
        }

        // Log the logout audit event
        $adminId = Session::get('admin_user_id');
        if ($adminId) {
            $this->adminService->logAuditEvent($adminId, 'logout', 'admin_users', $adminId, null, null, $request->getIp());
        }

        Session::destroy();

        // Redirect to login page
        $response = new Response();
        $response->redirect('/admin/login');
        return $response;
    }
}
