<?php

namespace App\Controllers\Provider;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Repositories\ProviderAccountRepository;
use App\Repositories\ProviderDraftRepository;

class AuthController extends Controller
{
    private ProviderAccountRepository $accountRepo;
    private ProviderDraftRepository $draftRepo;

    public function __construct()
    {
        $this->accountRepo = new ProviderAccountRepository();
        $this->draftRepo = new ProviderDraftRepository();
    }

    /**
     * Display the login page.
     */
    public function showLogin(Request $request): Response
    {
        return $this->render('provider/auth/login');
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

        $email = trim($request->input('email', ''));
        $name = trim($request->input('name', ''));

        if (empty($email) || empty($name)) {
            Flash::error('يرجى ملء كافة حقول الهوية لمواصلة المحاكاة.');
            return Response::redirect('/provider/auth/google/stub');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('البريد الإلكتروني المدخل غير صالح.');
            return Response::redirect('/provider/auth/google/stub');
        }

        // Check if account already exists
        $account = $this->accountRepo->findByEmail($email);
        
        if (!$account) {
            // Register a new provider account
            $accountId = $this->accountRepo->create([
                'email' => $email,
                'google_id' => 'google-' . uniqid(),
                'display_name' => $name,
                'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0D8ABC&color=fff',
                'status' => 'active',
                'provider_id' => null
            ]);
            $account = $this->accountRepo->find($accountId);
        } else {
            if ($account['status'] !== 'active') {
                Flash::error('عذراً، هذا الحساب معطل. يرجى التواصل مع الإدارة.');
                return Response::redirect('/provider/login');
            }
            $accountId = $account['id'];
        }

        // Update last login
        $this->accountRepo->updateLastLogin($accountId);

        // Regenerate and set session
        Session::regenerate();
        Session::set('provider_account_id', $account['id']);
        Session::set('provider_account_email', $account['email']);
        Session::set('provider_account_name', $account['display_name']);
        Session::set('provider_account_avatar', $account['avatar_url']);
        Session::set('provider_last_activity', time());

        Flash::success('تم تسجيل الدخول بنجاح عبر بوابة Google (محاكاة).');

        // Check if account is linked to a published profile
        if ($account['provider_id']) {
            return Response::redirect('/provider/dashboard');
        }

        // No linked provider profile. Check if draft exists
        $draft = $this->draftRepo->getLatestDraftForAccount($accountId);
        if (!$draft) {
            // Create a blank initial draft for onboarding
            $this->draftRepo->create([
                'provider_account_id' => $accountId,
                'status' => 'draft',
                'display_name_ar' => $name,
                'email' => $email
            ]);
            Flash::success('أهلاً بك! يرجى إكمال خطوات إنشاء ملفك المهني للبدء.');
            return Response::redirect('/provider/wizard');
        }

        return Response::redirect('/provider/dashboard');
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
