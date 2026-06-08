<?php
/**
 * ServicesRepository.php
 * Khadomeh Services Repository Database Layer
 */

namespace App\Modules\Services;

use App\Core\Repository;

class ServicesRepository extends Repository implements ServicesRepositoryInterface
{
    /**
     * Find a service by its primary key ID.
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `services` WHERE `id` = :id LIMIT 1",
            ['id' => $id]
        );
    }

    /**
     * Find a service by its public UUID.
     */
    public function findByPublicId(string $publicId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `services` WHERE `public_id` = :public_id LIMIT 1",
            ['public_id' => $publicId]
        );
    }

    /**
     * Find a service by its slug.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `services` WHERE `slug` = :slug AND `is_deleted` = 0 LIMIT 1",
            ['slug' => $slug]
        );
    }

    /**
     * Search and paginate services.
     */
    public function search(
        array $criteria,
        string $orderBy = 'sort_order',
        string $orderDir = 'ASC',
        int $limit = 15,
        int $offset = 0
    ): array {
        [$whereClause, $params] = $this->buildWhereClause($criteria);

        // Sanitize sorting column to prevent SQL injection
        $allowedCols = ['id', 'key', 'slug', 'display_name_ar', 'short_name_ar', 'sort_order', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $allowedCols)) {
            $orderBy = 'sort_order';
        }
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM `services` 
                $whereClause 
                ORDER BY `$orderBy` $orderDir 
                LIMIT :limit OFFSET :offset";

        // Bind LIMIT and OFFSET as integers since PDO/MySQL requires it under strict emulated prepares
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare($sql);

        // Bind standard params
        foreach ($params as $param => $value) {
            $stmt->bindValue(':' . $param, $value);
        }

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Count matching search items for pagination.
     */
    public function countSearch(array $criteria): int
    {
        [$whereClause, $params] = $this->buildWhereClause($criteria);
        $sql = "SELECT COUNT(*) FROM `services` $whereClause";
        return (int) $this->db->fetchColumn($sql, $params);
    }

    /**
     * Check if key exists.
     */
    public function existsByKey(string $key, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `services` WHERE `key` = :key AND `is_deleted` = 0";
        $params = ['key' => $key];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Check if slug exists.
     */
    public function existsBySlug(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `services` WHERE `slug` = :slug AND `is_deleted` = 0";
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Create a new service record.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `services` 
                (`public_id`, `key`, `slug`, `display_name_ar`, `short_name_ar`, `description_ar`, `icon`, `sort_order`, `meta_title_ar`, `meta_description_ar`, `is_active`, `is_deleted`) 
                VALUES 
                (:public_id, :key, :slug, :display_name_ar, :short_name_ar, :description_ar, :icon, :sort_order, :meta_title_ar, :meta_description_ar, :is_active, 0)";
        
        $this->db->execute($sql, [
            'public_id' => $data['public_id'],
            'key' => $data['key'],
            'slug' => $data['slug'],
            'display_name_ar' => $data['display_name_ar'],
            'short_name_ar' => $data['short_name_ar'],
            'description_ar' => $data['description_ar'] ?? null,
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title_ar' => $data['meta_title_ar'] ?? null,
            'meta_description_ar' => $data['meta_description_ar'] ?? null,
            'is_active' => $data['is_active'] ? 1 : 0
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing service record.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `services` SET 
                `key` = :key,
                `slug` = :slug,
                `display_name_ar` = :display_name_ar,
                `short_name_ar` = :short_name_ar,
                `description_ar` = :description_ar,
                `icon` = :icon,
                `sort_order` = :sort_order,
                `meta_title_ar` = :meta_title_ar,
                `meta_description_ar` = :meta_description_ar,
                `is_active` = :is_active
                WHERE `id` = :id";

        return $this->db->execute($sql, [
            'id' => $id,
            'key' => $data['key'],
            'slug' => $data['slug'],
            'display_name_ar' => $data['display_name_ar'],
            'short_name_ar' => $data['short_name_ar'],
            'description_ar' => $data['description_ar'] ?? null,
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title_ar' => $data['meta_title_ar'] ?? null,
            'meta_description_ar' => $data['meta_description_ar'] ?? null,
            'is_active' => $data['is_active'] ? 1 : 0
        ]);
    }

    /**
     * Soft delete a service record.
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE `services` SET 
                `is_deleted` = 1,
                `deleted_at` = NOW() 
                WHERE `id` = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Restore a soft-deleted service record.
     */
    public function restore(int $id): bool
    {
        $sql = "UPDATE `services` SET 
                `is_deleted` = 0,
                `deleted_at` = NULL 
                WHERE `id` = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Helper to build SQL WHERE clause dynamically.
     */
    private function buildWhereClause(array $criteria): array
    {
        $where = [];
        $params = [];

        // In admin context, we can filter by is_deleted explicitly,
        // but by default we only show non-deleted elements.
        if (isset($criteria['is_deleted'])) {
            $where[] = "`is_deleted` = :is_deleted";
            $params['is_deleted'] = $criteria['is_deleted'] ? 1 : 0;
        } else {
            $where[] = "`is_deleted` = 0";
        }

        if (isset($criteria['is_active'])) {
            $where[] = "`is_active` = :is_active";
            $params['is_active'] = $criteria['is_active'] ? 1 : 0;
        }

        if (!empty($criteria['keyword'])) {
            // Normalize inputs via shared helper
            $keyword = normalize_arabic($criteria['keyword']);
            $where[] = "(`key` LIKE :kw_key OR `slug` LIKE :kw_slug OR `display_name_ar` LIKE :kw_display OR `short_name_ar` LIKE :kw_short OR `description_ar` LIKE :kw_desc)";
            $params['kw_key'] = '%' . $keyword . '%';
            $params['kw_slug'] = '%' . $keyword . '%';
            $params['kw_display'] = '%' . $keyword . '%';
            $params['kw_short'] = '%' . $keyword . '%';
            $params['kw_desc'] = '%' . $keyword . '%';
        }

        $clause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
        return [$clause, $params];
    }
}
