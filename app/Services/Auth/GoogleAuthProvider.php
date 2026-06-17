<?php

namespace App\Services\Auth;

use App\Core\Request;
use App\Repositories\ProviderAccountRepository;
use App\Repositories\ProviderDraftRepository;

class GoogleAuthProvider implements AuthProviderInterface
{
    private ProviderAccountRepository $accountRepo;
    private ProviderDraftRepository $draftRepo;

    public function __construct()
    {
        $this->accountRepo = new ProviderAccountRepository();
        $this->draftRepo = new ProviderDraftRepository();
    }

    /**
     * Authenticate via simulated Google OAuth details.
     */
    public function authenticate(Request $request): ?array
    {
        $email = trim($request->input('email', ''));
        $name = trim($request->input('name', ''));

        if (empty($email) || empty($name)) {
            throw new \InvalidArgumentException('يرجى ملء كافة حقول الهوية لمواصلة المحاكاة.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('البريد الإلكتروني المدخل غير صالح.');
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
                throw new \RuntimeException('عذراً، هذا الحساب معطل. يرجى التواصل مع الإدارة.');
            }
        }

        // Check if account has a draft or linked provider; if not, initialize one
        if (!$account['provider_id']) {
            $draft = $this->draftRepo->getLatestDraftForAccount($account['id']);
            if (!$draft) {
                // Create a blank initial draft for onboarding
                $this->draftRepo->create([
                    'provider_account_id' => $account['id'],
                    'status' => 'draft',
                    'display_name_ar' => $name,
                    'email' => $email
                ]);
            }
        }

        return $account;
    }
}
