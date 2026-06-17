<?php

namespace App\Services\Auth;

use App\Core\Request;
use App\Core\Config;
use App\Core\Database;
use App\Repositories\ProviderAccountRepository;
use App\Repositories\ProviderDraftRepository;

class DevAuthProvider implements AuthProviderInterface
{
    private ProviderAccountRepository $accountRepo;
    private ProviderDraftRepository $draftRepo;
    private Database $db;

    public function __construct()
    {
        $this->accountRepo = new ProviderAccountRepository();
        $this->draftRepo = new ProviderDraftRepository();
        $this->db = Database::getInstance();
    }

    /**
     * Authenticate via Dev Username/ID and Shared Password.
     */
    public function authenticate(Request $request): ?array
    {
        $username = trim($request->input('username', ''));
        $password = trim($request->input('password', ''));

        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('يرجى إدخال معرف مقدم الخدمة وكلمة المرور.');
        }

        // Validate shared password from config/auth
        $sharedPassword = Config::get('auth.dev_provider_password', 'dev_pass_123');
        if ($password !== $sharedPassword) {
            throw new \RuntimeException('كلمة المرور الخاصة بالتطوير غير صحيحة.');
        }

        $account = null;

        // 1. Try to find directly by email in provider_accounts (if username looks like email)
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $account = $this->accountRepo->findByEmail($username);
        }

        // 2. Try to find by numeric provider_id in provider_accounts
        if (!$account && is_numeric($username)) {
            $account = $this->db->fetch(
                "SELECT * FROM `provider_accounts` WHERE `provider_id` = :provider_id LIMIT 1",
                ['provider_id' => (int)$username]
            ) ?: null;

            // Try to find by direct account id in provider_accounts
            if (!$account) {
                $account = $this->accountRepo->find((int)$username);
            }
        }

        // 3. Try to find by slug in providers
        if (!$account) {
            $provider = $this->db->fetch(
                "SELECT * FROM `providers` WHERE `slug` = :slug AND `deleted_at` IS NULL LIMIT 1",
                ['slug' => $username]
            );
            if ($provider) {
                // Find account mapped to this provider
                $account = $this->db->fetch(
                    "SELECT * FROM `provider_accounts` WHERE `provider_id` = :provider_id LIMIT 1",
                    ['provider_id' => $provider['id']]
                ) ?: null;

                // Dynamic creation for seeding compatibility if no account exists yet
                if (!$account) {
                    $email = $provider['email'] ?: ($provider['slug'] . '@dev.khadomeh.com');
                    $existing = $this->accountRepo->findByEmail($email);
                    if ($existing) {
                        $account = $existing;
                        if (!$account['provider_id']) {
                            $this->accountRepo->linkProvider($account['id'], $provider['id']);
                            $account['provider_id'] = $provider['id'];
                        }
                    } else {
                        $accountId = $this->accountRepo->create([
                            'email' => $email,
                            'google_id' => 'dev-' . uniqid(),
                            'display_name' => $provider['display_name_ar'],
                            'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($provider['display_name_ar']) . '&background=0D8ABC&color=fff',
                            'status' => 'active',
                            'provider_id' => $provider['id']
                        ]);
                        $account = $this->accountRepo->find($accountId);
                    }
                }
            }
        }

        if (!$account) {
            // Also check if username points directly to a provider with no account
            if (is_numeric($username)) {
                $provider = $this->db->fetch(
                    "SELECT * FROM `providers` WHERE `id` = :id AND `deleted_at` IS NULL LIMIT 1",
                    ['id' => (int)$username]
                );
                if ($provider) {
                    $email = $provider['email'] ?: ($provider['slug'] . '@dev.khadomeh.com');
                    $existing = $this->accountRepo->findByEmail($email);
                    if ($existing) {
                        $account = $existing;
                        if (!$account['provider_id']) {
                            $this->accountRepo->linkProvider($account['id'], $provider['id']);
                            $account['provider_id'] = $provider['id'];
                        }
                    } else {
                        $accountId = $this->accountRepo->create([
                            'email' => $email,
                            'google_id' => 'dev-' . uniqid(),
                            'display_name' => $provider['display_name_ar'],
                            'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($provider['display_name_ar']) . '&background=0D8ABC&color=fff',
                            'status' => 'active',
                            'provider_id' => $provider['id']
                        ]);
                        $account = $this->accountRepo->find($accountId);
                    }
                }
            }
        }

        if (!$account) {
            throw new \RuntimeException('عذراً، لم يتم العثور على حساب مقدم الخدمة المطابق للمعرف المدخل.');
        }

        if ($account['status'] !== 'active') {
            throw new \RuntimeException('عذراً، هذا الحساب معطل. يرجى التواصل مع الإدارة.');
        }

        // Initialize draft if needed
        if (!$account['provider_id']) {
            $draft = $this->draftRepo->getLatestDraftForAccount($account['id']);
            if (!$draft) {
                $this->draftRepo->create([
                    'provider_account_id' => $account['id'],
                    'status' => 'draft',
                    'display_name_ar' => $account['display_name'],
                    'email' => $account['email']
                ]);
            }
        }

        return $account;
    }
}
