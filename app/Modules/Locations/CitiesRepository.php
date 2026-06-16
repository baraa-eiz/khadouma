<?php
/**
 * CitiesRepository.php
 * Khadomeh Cities Repository Database Layer
 */

namespace App\Modules\Locations;

use App\Core\Repository;

class CitiesRepository extends Repository implements CitiesRepositoryInterface
{
    /**
     * Find a city by its primary key ID.
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `cities` WHERE `id` = :id LIMIT 1",
            ['id' => $id]
        );
    }

    /**
     * Find a city by its public UUID.
     */
    public function findByPublicId(string $publicId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `cities` WHERE `public_id` = :public_id LIMIT 1",
            ['public_id' => $publicId]
        );
    }

    /**
     * Find a city by its slug.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `cities` WHERE `slug` = :slug AND `is_deleted` = 0 LIMIT 1",
            ['slug' => $slug]
        );
    }

    /**
     * Search and paginate cities.
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
        $allowedCols = ['id', 'key', 'slug', 'display_name_ar', 'sort_order', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $allowedCols)) {
            $orderBy = 'sort_order';
        }
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM `cities` 
                $whereClause 
                ORDER BY `$orderBy` $orderDir 
                LIMIT :limit OFFSET :offset";

        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare($sql);

        // Bind parameters
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
        $sql = "SELECT COUNT(*) FROM `cities` $whereClause";
        return (int) $this->db->fetchColumn($sql, $params);
    }

    /**
     * Check if key exists.
     */
    public function existsByKey(string $key, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `cities` WHERE `key` = :key AND `is_deleted` = 0";
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
        $sql = "SELECT COUNT(*) FROM `cities` WHERE `slug` = :slug AND `is_deleted` = 0";
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Check if Arabic display name exists.
     */
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `cities` WHERE `display_name_ar` = :display_name_ar AND `is_deleted` = 0";
        $params = ['display_name_ar' => $name];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Create a new city record.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `cities` 
                (`public_id`, `key`, `slug`, `display_name_ar`, `display_name_en`, `sort_order`, `meta_title_ar`, `meta_description_ar`, `is_active`, `is_deleted`) 
                VALUES 
                (:public_id, :key, :slug, :display_name_ar, :display_name_en, :sort_order, :meta_title_ar, :meta_description_ar, :is_active, 0)";
        
        $this->db->execute($sql, [
            'public_id' => $data['public_id'],
            'key' => $data['key'],
            'slug' => $data['slug'],
            'display_name_ar' => $data['display_name_ar'],
            'display_name_en' => $data['display_name_en'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title_ar' => $data['meta_title_ar'] ?? null,
            'meta_description_ar' => $data['meta_description_ar'] ?? null,
            'is_active' => $data['is_active'] ? 1 : 0
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing city record.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `cities` SET 
                `key` = :key,
                `slug` = :slug,
                `display_name_ar` = :display_name_ar,
                `display_name_en` = :display_name_en,
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
            'display_name_en' => $data['display_name_en'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title_ar' => $data['meta_title_ar'] ?? null,
            'meta_description_ar' => $data['meta_description_ar'] ?? null,
            'is_active' => $data['is_active'] ? 1 : 0
        ]);
    }

    /**
     * Soft delete a city record.
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE `cities` SET 
                `is_deleted` = 1,
                `deleted_at` = CURRENT_TIMESTAMP 
                WHERE `id` = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Restore a soft-deleted city record.
     */
    public function restore(int $id): bool
    {
        $sql = "UPDATE `cities` SET 
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
            $keyword = normalize_arabic($criteria['keyword']);
            $where[] = "(`key` LIKE :kw_key OR `slug` LIKE :kw_slug OR `display_name_ar` LIKE :kw_display OR `display_name_en` LIKE :kw_display_en)";
            $params['kw_key'] = '%' . $keyword . '%';
            $params['kw_slug'] = '%' . $keyword . '%';
            $params['kw_display'] = '%' . $keyword . '%';
            $params['kw_display_en'] = '%' . $keyword . '%';
        }

        $clause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
        return [$clause, $params];
    }
}
