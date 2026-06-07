<?php
/**
 * AdminUserRepository.php
 * Khadomeh Admin User Data Repository
 */

namespace App\Repositories;

use App\Core\Database;

class AdminUserRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find an active admin user by email.
     */
    public function findByEmail($email) {
        return $this->db->fetch(
            "SELECT * FROM `admin_users` WHERE `email` = ? AND `is_active` = 1 LIMIT 1",
            [$email]
        );
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin($id) {
        return $this->db->query(
            "UPDATE `admin_users` SET `last_login_at` = NOW() WHERE `id` = ?",
            [$id]
        );
    }
}
