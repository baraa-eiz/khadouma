<?php
/**
 * UsersRepository.php
 * Khadomeh Users Repository Implementation
 */

namespace App\Modules\Users;

use App\Core\Database;
use PDO;

class UsersRepository implements UsersRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?UserData
    {
        $sql = "SELECT * FROM `user_accounts` WHERE `id` = :id LIMIT 1";
        $row = $this->db->fetch($sql, ['id' => $id]);
        return $row ? UserData::fromArray($row) : null;
    }

    public function findByPublicId(string $publicId): ?UserData
    {
        $sql = "SELECT * FROM `user_accounts` WHERE `public_id` = :public_id AND `deleted_at` IS NULL LIMIT 1";
        $row = $this->db->fetch($sql, ['public_id' => $publicId]);
        return $row ? UserData::fromArray($row) : null;
    }

    public function findByEmail(string $email): ?UserData
    {
        $sql = "SELECT * FROM `user_accounts` WHERE `email` = :email AND `deleted_at` IS NULL LIMIT 1";
        $row = $this->db->fetch($sql, ['email' => strtolower(trim($email))]);
        return $row ? UserData::fromArray($row) : null;
    }

    public function findByPhone(string $phone): ?UserData
    {
        $sql = "SELECT * FROM `user_accounts` WHERE `phone` = :phone AND `deleted_at` IS NULL LIMIT 1";
        $row = $this->db->fetch($sql, ['phone' => phone_format($phone)]);
        return $row ? UserData::fromArray($row) : null;
    }

    public function create(UserData $userData): UserData
    {
        $data = $userData->toArray();
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        unset($data['completion_score']);

        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":{$f}", $fields);

        $sql = "INSERT INTO `user_accounts` (" . implode(', ', array_map(fn($f) => "`{$f}`", $fields)) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->execute($sql, $data);
        $insertId = (int)$this->db->lastInsertId();

        // Retrieve the newly created record to populate generated values like created_at
        $freshRow = $this->db->fetch("SELECT * FROM `user_accounts` WHERE `id` = :id LIMIT 1", ['id' => $insertId]);
        return UserData::fromArray($freshRow);
    }

    public function update(UserData $userData): bool
    {
        $data = $userData->toArray();
        $id = $data['id'];
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        unset($data['completion_score']);

        $setParts = [];
        foreach (array_keys($data) as $field) {
            $setParts[] = "`{$field}` = :{$field}";
        }
        $data['id'] = $id;

        $sql = "UPDATE `user_accounts` SET " . implode(', ', $setParts) . " WHERE `id` = :id";
        return $this->db->execute($sql, $data);
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE `user_accounts` SET `deleted_at` = CURRENT_TIMESTAMP WHERE `id` = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function restore(int $id): bool
    {
        $sql = "UPDATE `user_accounts` SET `deleted_at` = NULL WHERE `id` = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function changeStatus(int $id, string $status): bool
    {
        $sql = "UPDATE `user_accounts` SET `status` = :status WHERE `id` = :id";
        return $this->db->execute($sql, ['id' => $id, 'status' => $status]);
    }

    public function listUsers(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        // Check if soft deletes should be filtered
        if (empty($filters['include_deleted'])) {
            $where[] = "`deleted_at` IS NULL";
        }

        if (!empty($filters['search'])) {
            $where[] = "(`display_name` LIKE :search OR `email` LIKE :search OR `phone` LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = "`status` = :status";
            $params['status'] = $filters['status'];
        }

        $sql = "SELECT * FROM `user_accounts` 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY `created_at` DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => UserData::fromArray($row), $rows);
    }

    public function countUsers(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (empty($filters['include_deleted'])) {
            $where[] = "`deleted_at` IS NULL";
        }

        if (!empty($filters['search'])) {
            $where[] = "(`display_name` LIKE :search OR `email` LIKE :search OR `phone` LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = "`status` = :status";
            $params['status'] = $filters['status'];
        }

        $sql = "SELECT COUNT(*) FROM `user_accounts` WHERE " . implode(' AND ', $where);
        return (int)$this->db->fetchColumn($sql, $params);
    }

    public function search(array $criteria, string $sortBy = 'created_at', string $sortDir = 'DESC', int $limit = 15, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (isset($criteria['is_deleted'])) {
            if ($criteria['is_deleted'] === 1) {
                $where[] = "`deleted_at` IS NOT NULL";
            } else {
                $where[] = "`deleted_at` IS NULL";
            }
        } else {
            $where[] = "`deleted_at` IS NULL";
        }

        if (!empty($criteria['keyword'])) {
            $where[] = "(`display_name` LIKE :keyword OR `email` LIKE :keyword OR `phone` LIKE :keyword)";
            $params['keyword'] = '%' . $criteria['keyword'] . '%';
        }

        if (!empty($criteria['status'])) {
            $where[] = "`status` = :status";
            $params['status'] = $criteria['status'];
        }

        $allowedSort = ['id', 'display_name', 'email', 'phone', 'completion_score', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'created_at';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        if ($sortBy === 'completion_score') {
            $orderBySql = "(
                (CASE WHEN `display_name` IS NOT NULL AND `display_name` != '' THEN 20 ELSE 0 END) +
                (CASE WHEN `email` IS NOT NULL AND `email` != '' THEN 15 ELSE 0 END) +
                (CASE WHEN `phone` IS NOT NULL AND `phone` != '' THEN 15 ELSE 0 END) +
                (CASE WHEN `avatar` IS NOT NULL AND `avatar` != '' THEN 15 ELSE 0 END) +
                (CASE WHEN `city_id` IS NOT NULL THEN 15 ELSE 0 END) +
                (CASE WHEN `area_id` IS NOT NULL THEN 10 ELSE 0 END) +
                (CASE WHEN `default_address` IS NOT NULL AND `default_address` != '' THEN 10 ELSE 0 END)
            )";
        } else {
            $orderBySql = "`{$sortBy}`";
        }

        $sql = "SELECT * FROM `user_accounts` 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY {$orderBySql} {$sortDir} 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => UserData::fromArray($row), $rows);
    }

    public function countSearch(array $criteria): int
    {
        $where = ["1=1"];
        $params = [];

        if (isset($criteria['is_deleted'])) {
            if ($criteria['is_deleted'] === 1) {
                $where[] = "`deleted_at` IS NOT NULL";
            } else {
                $where[] = "`deleted_at` IS NULL";
            }
        } else {
            $where[] = "`deleted_at` IS NULL";
        }

        if (!empty($criteria['keyword'])) {
            $where[] = "(`display_name` LIKE :keyword OR `email` LIKE :keyword OR `phone` LIKE :keyword)";
            $params['keyword'] = '%' . $criteria['keyword'] . '%';
        }

        if (!empty($criteria['status'])) {
            $where[] = "`status` = :status";
            $params['status'] = $criteria['status'];
        }

        $sql = "SELECT COUNT(*) FROM `user_accounts` WHERE " . implode(' AND ', $where);
        return (int)$this->db->fetchColumn($sql, $params);
    }

    public function getFavorites(int $userId): array
    {
        $sql = "SELECT * FROM `user_favorites` WHERE `user_id` = :user_id ORDER BY `created_at` DESC";
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    public function isFavorite(int $userId, string $entityType, string $entityPublicId): bool
    {
        $sql = "SELECT COUNT(*) FROM `user_favorites` 
                WHERE `user_id` = :user_id 
                  AND `entity_type` = :entity_type 
                  AND `entity_public_id` = :entity_public_id";
        $count = (int)$this->db->fetchColumn($sql, [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_public_id' => $entityPublicId
        ]);
        return $count > 0;
    }

    public function addFavorite(int $userId, string $entityType, string $entityPublicId): bool
    {
        $driver = $this->db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $sql = "INSERT IGNORE INTO `user_favorites` (`user_id`, `entity_type`, `entity_public_id`) 
                    VALUES (:user_id, :entity_type, :entity_public_id)";
        } else {
            $sql = "INSERT OR IGNORE INTO `user_favorites` (`user_id`, `entity_type`, `entity_public_id`) 
                    VALUES (:user_id, :entity_type, :entity_public_id)";
        }
        
        return $this->db->execute($sql, [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_public_id' => $entityPublicId
        ]);
    }

    public function removeFavorite(int $userId, string $entityType, string $entityPublicId): bool
    {
        $sql = "DELETE FROM `user_favorites` 
                WHERE `user_id` = :user_id 
                  AND `entity_type` = :entity_type 
                  AND `entity_public_id` = :entity_public_id";
        return $this->db->execute($sql, [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_public_id' => $entityPublicId
        ]);
    }
}
