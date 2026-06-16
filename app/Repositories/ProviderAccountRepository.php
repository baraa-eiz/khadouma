<?php

namespace App\Repositories;

use App\Core\Database;

class ProviderAccountRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find account by ID.
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `provider_accounts` WHERE `id` = :id LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    /**
     * Find account by email.
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `provider_accounts` WHERE `email` = :email LIMIT 1",
            ['email' => $email]
        ) ?: null;
    }

    /**
     * Create a new provider account.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `provider_accounts` 
                (`email`, `google_id`, `display_name`, `avatar_url`, `provider_id`, `status`) 
                VALUES 
                (:email, :google_id, :display_name, :avatar_url, :provider_id, :status)";
        
        $this->db->execute($sql, [
            'email' => $data['email'],
            'google_id' => $data['google_id'] ?? null,
            'display_name' => $data['display_name'],
            'avatar_url' => $data['avatar_url'] ?? null,
            'provider_id' => $data['provider_id'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update account details.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `provider_accounts` SET 
                `email` = :email,
                `google_id` = :google_id,
                `display_name` = :display_name,
                `avatar_url` = :avatar_url,
                `provider_id` = :provider_id,
                `status` = :status
                WHERE `id` = :id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'email' => $data['email'],
            'google_id' => $data['google_id'] ?? null,
            'display_name' => $data['display_name'],
            'avatar_url' => $data['avatar_url'] ?? null,
            'provider_id' => $data['provider_id'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
    }

    /**
     * Link account to a provider profile.
     */
    public function linkProvider(int $accountId, int $providerId): bool
    {
        return $this->db->execute(
            "UPDATE `provider_accounts` SET `provider_id` = :provider_id WHERE `id` = :id",
            ['id' => $accountId, 'provider_id' => $providerId]
        );
    }

    public function updateLastLogin(int $accountId): bool
    {
        return $this->db->execute(
            "UPDATE `provider_accounts` SET `last_login_at` = :now WHERE `id` = :id",
            [
                'id' => $accountId,
                'now' => date('Y-m-d H:i:s')
            ]
        );
    }
}
