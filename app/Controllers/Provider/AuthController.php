<?php

namespace App\Controllers\Provider;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Repositories\ProviderAccountRepository;
use App\Repositories\ProviderDraftRepository;
use App\Services\Auth\ProviderAuthService;

class AuthController extends Controller
{
    private ProviderAccountRepository $accountRepo;
    private ProviderDraftRepository $draftRepo;
    private ProviderAuthService $authService;

    public function __construct()
    {
        $this->accountRepo = new ProviderAccountRepository();
        $this->draftRepo = new ProviderDraftRepository();
        $this->authService = new ProviderAuthService();
    }

    /**
     * Display the login page.
     */
    public function showLogin(Request $request): Response
    {
        $devLoginEnabled = $this->authService->isDevLoginEnabled();
        return $this->render('provider/auth/login', [
            'devLoginEnabled' => $devLoginEnabled
        ]);
    }

    /**
     * Redirect to the Google auth stub.
     */
    public function googleAuth(Request $request): Response
    {
        return Response::redirect('/provider/auth/google/stub');
    }

    /**
     * Show the simulated Google Authentication Stub.
     */
    public function showGoogleStub(Request $request): Response
    {
        return $this->render('provider/auth/google_stub');
    }

    /**
     * Handle the form submission from the Google Auth Stub.
     */
    public function processGoogleStub(Request $request): Response
    {
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/provider/auth/google/stub');
        }

        try {
            $googleProvider = $this->authService->getProvider('google');
            $account = $googleProvider->authenticate($request);
            
            $this->authService->loginUser($account);
            Flash::success('تم تسجيل الدخول بنجاح عبر بوابة Google (محاكاة).');

            if ($account['provider_id']) {
                return Response::redirect('/provider/dashboard');
            }

            $draft = $this->draftRepo->getLatestDraftForAccount($account['id']);
            if (!$draft) {
                // Should not happen as GoogleAuthProvider creates it, but fallback:
                $this->draftRepo->create([
                    'provider_account_id' => $account['id'],
                    'status' => 'draft',
                    'display_name_ar' => $account['display_name'],
                    'email' => $account['email']
                ]);
                Flash::success('أهلاً بك! يرجى إكمال خطوات إنشاء ملفك المهني للبدء.');
                return Response::redirect('/provider/wizard');
            }

            return Response::redirect('/provider/dashboard');

        } catch (\Exception $e) {
            Flash::error($e->getMessage());
            return Response::redirect('/provider/auth/google/stub');
        }
    }

    /**
     * Handle the form submission from the Dev Credentials Login.
     */
    public function processDevLogin(Request $request): Response
    {
        // 1. Feature Flag Protection
        if (!$this->authService->isDevLoginEnabled()) {
            Flash::error('بوابة تسجيل الدخول للتطوير غير مفعلة.');
            return Response::redirect('/provider/login');
        }

        // 2. Validate CSRF
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/provider/login');
        }

        // 3. Rate Limiting (Session based)
        $failedAttempts = Session::get('dev_login_failed_attempts', 0);
        $lastAttemptTime = Session::get('dev_login_last_attempt_time', 0);

        if ($failedAttempts >= 5 && (time() - $lastAttemptTime) < 300) {
            $secondsLeft = 300 - (time() - $lastAttemptTime);
            Flash::error("لقد تجاوزت الحد الأقصى لمحاولات تسجيل الدخول. يرجى الانتظار {$secondsLeft} ثانية.");
            return Response::redirect('/provider/login');
        }

        try {
            $devProvider = $this->authService->getProvider('dev');
            $account = $devProvider->authenticate($request);

            // Reset rate limiter on success
            Session::remove('dev_login_failed_attempts');
            Session::remove('dev_login_last_attempt_time');

            $this->authService->loginUser($account);
            Flash::success('تم تسجيل الدخول للتطوير بنجاح.');

            if ($account['provider_id']) {
                return Response::redirect('/provider/dashboard');
            }

            return Response::redirect('/provider/dashboard');

        } catch (\Exception $e) {
            // Increment rate limiter on failure
            Session::set('dev_login_failed_attempts', $failedAttempts + 1);
            Session::set('dev_login_last_attempt_time', time());

            Flash::error($e->getMessage());
            return Response::redirect('/provider/login');
        }
    }

    /**
     * Log the provider out.
     */
    public function logout(Request $request): Response
    {
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/provider/dashboard');
        }

        Session::destroy();
        
        $response = new Response();
        $response->redirect('/provider/login');
        return $response;
    }
}
