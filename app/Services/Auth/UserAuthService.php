<?php
/**
 * UserAuthService.php
 * Khadomeh User Portal Authentication Service
 */

namespace App\Services\Auth;

use App\Core\Config;
use App\Core\Session;
use App\Modules\Users\UsersRepositoryInterface;
use App\Modules\Users\UsersRepository;
use App\Modules\Users\UserData;

class UserAuthService
{
    private UsersRepositoryInterface $repo;

    public function __construct(?UsersRepositoryInterface $repo = null)
    {
        $this->repo = $repo ?? new UsersRepository();
    }

    /**
     * Check if dev login is active for users.
     */
    public function isDevLoginEnabled(): bool
    {
        return (bool) Config::get('auth.dev_user_login', false);
    }

    /**
     * Authenticate a user via email or phone using the developer password.
     * Returns: ?UserData
     */
    public function authenticateDev(string $username, string $password): ?UserData
    {
        if (!$this->isDevLoginEnabled()) {
            return null;
        }

        $devPassword = Config::get('auth.dev_user_password', 'user_pass_123');
        if ($password !== $devPassword) {
            return null;
        }

        // Find user by email or phone
        $user = null;
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->repo->findByEmail($username);
        } else {
            $user = $this->repo->findByPhone($username);
        }

        // Ensure user is active (not suspended or deleted)
        if ($user && $user->status !== 'active') {
            return null;
        }

        return $user;
    }

    /**
     * Log user in and initialize session.
     */
    public function loginUser(UserData $user): void
    {
        // Update last login timestamp in DB
        $updatedUser = new UserData(array_merge($user->toArray(), [
            'last_login_at' => date('Y-m-d H:i:s')
        ]));
        $this->repo->update($updatedUser);

        // Session security & values population
        Session::regenerate();
        Session::set('user_id', $user->id);
        Session::set('user_public_id', $user->public_id);
        Session::set('user_display_name', $user->display_name);
        Session::set('user_email', $user->email);
        Session::set('user_phone', $user->phone);
        Session::set('user_avatar', $user->avatar);
        Session::set('user_last_activity', time());
    }

    /**
     * Log user out.
     */
    public function logoutUser(): void
    {
        Session::remove('user_id');
        Session::remove('user_public_id');
        Session::remove('user_display_name');
        Session::remove('user_email');
        Session::remove('user_phone');
        Session::remove('user_avatar');
        Session::remove('user_last_activity');
    }
}
