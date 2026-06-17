<?php
/**
 * AuthController.php
 * Khadomeh User Portal Authentication Controller
 */

namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Services\Auth\UserAuthService;

class AuthController extends Controller
{
    private UserAuthService $authService;

    public function __construct()
    {
        $this->authService = new UserAuthService();
    }

    /**
     * Display the login page.
     */
    public function showLogin(Request $request): Response
    {
        $devLoginEnabled = $this->authService->isDevLoginEnabled();
        return $this->render('user/auth/login', [
            'devLoginEnabled' => $devLoginEnabled
        ]);
    }

    /**
     * Handle the form submission from the Dev Credentials Login.
     */
    public function processLogin(Request $request): Response
    {
        if (!$this->authService->isDevLoginEnabled()) {
            Flash::error('بوابة تسجيل الدخول للتطوير غير مفعلة.');
            return Response::redirect('/user/login');
        }

        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/user/login');
        }

        // Rate Limiting (Session based)
        $failedAttempts = Session::get('user_login_failed_attempts', 0);
        $lastAttemptTime = Session::get('user_login_last_attempt_time', 0);

        if ($failedAttempts >= 5 && (time() - $lastAttemptTime) < 300) {
            $secondsLeft = 300 - (time() - $lastAttemptTime);
            Flash::error("لقد تجاوزت الحد الأقصى لمحاولات تسجيل الدخول. يرجى الانتظار {$secondsLeft} ثانية.");
            return Response::redirect('/user/login');
        }

        $username = trim($request->input('username', ''));
        $password = trim($request->input('password', ''));

        if ($username === '' || $password === '') {
            Flash::error('يرجى إدخال اسم المستخدم وكلمة المرور.');
            return Response::redirect('/user/login');
        }

        $user = $this->authService->authenticateDev($username, $password);

        if ($user) {
            // Reset rate limiter on success
            Session::remove('user_login_failed_attempts');
            Session::remove('user_login_last_attempt_time');

            $this->authService->loginUser($user);
            Flash::success('تم تسجيل الدخول بنجاح.');

            return Response::redirect('/user/dashboard');
        } else {
            // Increment rate limiter on failure
            Session::set('user_login_failed_attempts', $failedAttempts + 1);
            Session::set('user_login_last_attempt_time', time());

            Flash::error('اسم المستخدم أو كلمة المرور غير صالحة، أو أن الحساب موقوف.');
            return Response::redirect('/user/login');
        }
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): Response
    {
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/user/dashboard');
        }

        $this->authService->logoutUser();
        Flash::success('تم تسجيل الخروج بنجاح.');
        return Response::redirect('/user/login');
    }
}
