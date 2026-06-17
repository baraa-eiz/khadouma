<?php

namespace App\Services\Auth;

use App\Core\Config;
use App\Core\Session;
use App\Repositories\ProviderAccountRepository;

class ProviderAuthService
{
    private array $providers = [];
    private ProviderAccountRepository $accountRepo;

    public function __construct()
    {
        $this->accountRepo = new ProviderAccountRepository();
        $this->providers['google'] = new GoogleAuthProvider();
        $this->providers['dev'] = new DevAuthProvider();
    }

    /**
     * Check if dev login is active.
     */
    public function isDevLoginEnabled(): bool
    {
        return (bool) Config::get('auth.dev_provider_login', false);
    }

    /**
     * Get specific Auth Provider.
     */
    public function getProvider(string $type): AuthProviderInterface
    {
        if (!isset($this->providers[$type])) {
            throw new \InvalidArgumentException("معالج تسجيل الدخول غير معروف: " . $type);
        }
        return $this->providers[$type];
    }

    /**
     * Log user in and initialize session.
     */
    public function loginUser(array $account): void
    {
        // Update last login timestamp
        $this->accountRepo->updateLastLogin($account['id']);

        // Session security & values population
        Session::regenerate();
        Session::set('provider_account_id', $account['id']);
        Session::set('provider_account_email', $account['email']);
        Session::set('provider_account_name', $account['display_name']);
        Session::set('provider_account_avatar', $account['avatar_url']);
        Session::set('provider_last_activity', time());
    }
}
