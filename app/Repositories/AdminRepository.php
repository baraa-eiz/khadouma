<?php

namespace App\Repositories;

use App\Core\Repository;
use App\Repositories\Interfaces\RepositoryInterface;

class AdminRepository extends Repository implements RepositoryInterface
{
    /**
     * Find an admin by ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM `admin_users` WHERE `id` = :id LIMIT 1",
            ['id' => $id]
        );
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find an admin by email address.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM `admin_users` WHERE `email` = :email LIMIT 1",
            ['email' => $email]
        );
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(int $id): bool
    {
        return $this->db->execute(
            "UPDATE `admin_users` SET `last_login_at` = CURRENT_TIMESTAMP WHERE `id` = :id",
            ['id' => $id]
        );
    }

    /**
     * Get all admins.
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM `admin_users` ORDER BY `id` ASC");
        return $stmt->fetchAll();
    }

    /**
     * Create a new admin.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `admin_users` (`name`, `email`, `password_hash`, `role`, `is_active`) 
                VALUES (:name, :email, :password_hash, :role, :is_active)";
        
        $this->db->execute($sql, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'] ?? 'admin',
            'is_active' => $data['is_active'] ?? 1
        ]);

        return (int)$this->db->getConnection()->lastInsertId();
    }

    /**
     * Update an admin record.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = "`name` = :name";
            $params['name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = "`email` = :email";
            $params['email'] = $data['email'];
        }
        if (isset($data['password'])) {
            $fields[] = "`password_hash` = :password_hash";
            $params['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        if (isset($data['role'])) {
            $fields[] = "`role` = :role";
            $params['role'] = $data['role'];
        }
        if (isset($data['is_active'])) {
            $fields[] = "`is_active` = :is_active";
            $params['is_active'] = $data['is_active'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE `admin_users` SET " . implode(', ', $fields) . " WHERE `id` = :id";
        return $this->db->execute($sql, $params);
    }

    /**
     * Delete an admin record.
     */
    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM `admin_users` WHERE `id` = :id", ['id' => $id]);
    }
}
