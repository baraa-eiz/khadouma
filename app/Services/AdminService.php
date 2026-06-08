<?php

namespace App\Services;

use App\Core\Service;
use App\Core\Logger;
use App\Core\Database;
use App\Repositories\AdminRepository;

class AdminService extends Service
{
    private AdminRepository $adminRepository;

    public function __construct()
    {
        $this->adminRepository = new AdminRepository();
    }

    /**
     * Authenticate an admin user.
     *
     * @param string $email
     * @param string $password
     * @param string $ipAddress Client IP address
     * @return array|null The admin user data on success, null on failure
     */
    public function authenticate(string $email, string $password, string $ipAddress): ?array
    {
        try {
            $admin = $this->adminRepository->findByEmail($email);

            if (!$admin) {
                $this->logAuditEvent(null, 'login_failed_email_not_found', 'admin_users', null, null, null, $ipAddress);
                Logger::warning("Admin login failed: Email not found.", ['email' => $email, 'ip' => $ipAddress]);
                return null;
            }

            if (!$admin['is_active']) {
                $this->logAuditEvent($admin['id'], 'login_failed_inactive', 'admin_users', $admin['id'], null, null, $ipAddress);
                Logger::warning("Admin login failed: Account inactive.", ['email' => $email, 'ip' => $ipAddress]);
                return null;
            }

            if (!password_verify($password, $admin['password_hash'])) {
                $this->logAuditEvent($admin['id'], 'login_failed_password_incorrect', 'admin_users', $admin['id'], null, null, $ipAddress);
                Logger::warning("Admin login failed: Incorrect password.", ['email' => $email, 'ip' => $ipAddress]);
                return null;
            }

            // Authentication successful
            $this->adminRepository->updateLastLogin($admin['id']);
            $this->logAuditEvent($admin['id'], 'login_success', 'admin_users', $admin['id'], null, null, $ipAddress);
            Logger::info("Admin logged in successfully.", ['email' => $email, 'id' => $admin['id']]);

            return $admin;
        } catch (\Throwable $e) {
            Logger::error("Admin authentication error.", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Helper to write records to audit_logs database table.
     */
    public function logAuditEvent(
        ?int $adminUserId,
        string $action,
        ?string $entityType,
        ?int $entityId,
        ?string $oldValueJson,
        ?string $newValueJson,
        string $ipAddress
    ): bool {
        try {
            $ipHash = hash('sha256', $ipAddress);
            $db = Database::getInstance();
            
            $sql = "INSERT INTO `audit_logs` 
                    (`admin_user_id`, `action`, `entity_type`, `entity_id`, `old_value_json`, `new_value_json`, `ip_hash`) 
                    VALUES (:admin_user_id, :action, :entity_type, :entity_id, :old_value_json, :new_value_json, :ip_hash)";

            return $db->execute($sql, [
                'admin_user_id' => $adminUserId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_value_json' => $oldValueJson,
                'new_value_json' => $newValueJson,
                'ip_hash' => $ipHash
            ]);
        } catch (\Throwable $e) {
            Logger::error("Failed to write audit log to database.", ['error' => $e->getMessage()]);
            return false;
        }
    }
}
