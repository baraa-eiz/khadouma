<?php
/**
 * AreasRepository.php
 * Khadomeh Areas Repository Database Layer
 */

namespace App\Modules\Locations;

use App\Core\Repository;

class AreasRepository extends Repository implements AreasRepositoryInterface
{
    /**
     * Find an area by its primary key ID.
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT a.*, c.display_name_ar as city_name_ar 
             FROM `areas` a
             INNER JOIN `cities` c ON a.`city_id` = c.`id`
             WHERE a.`id` = :id LIMIT 1",
            ['id' => $id]
        );
    }

    /**
     * Find an area by its public UUID.
     */
    public function findByPublicId(string $publicId): ?array
    {
        return $this->db->fetch(
            "SELECT a.*, c.display_name_ar as city_name_ar 
             FROM `areas` a
             INNER JOIN `cities` c ON a.`city_id` = c.`id`
             WHERE a.`public_id` = :public_id LIMIT 1",
            ['public_id' => $publicId]
        );
    }

    /**
     * Find an area by its slug.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT a.*, c.display_name_ar as city_name_ar 
             FROM `areas` a
             INNER JOIN `cities` c ON a.`city_id` = c.`id`
             WHERE a.`slug` = :slug AND a.`is_deleted` = 0 LIMIT 1",
            ['slug' => $slug]
        );
    }

    /**
     * Search and paginate areas.
     */
    public function search(
        array $criteria,
        string $orderBy = 'sort_order',
        string $orderDir = 'ASC',
        int $limit = 15,
        int $offset = 0
    ): array {
        [$whereClause, $params] = $this->buildWhereClause($criteria);

        // Sanitize sorting column
        $allowedCols = ['id', 'key', 'slug', 'display_name_ar', 'sort_order', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $allowedCols)) {
            $orderBy = 'sort_order';
        }
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT a.*, c.display_name_ar as city_name_ar 
                FROM `areas` a
                INNER JOIN `cities` c ON a.`city_id` = c.`id`
                $whereClause 
                ORDER BY a.`$orderBy` $orderDir 
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
        $sql = "SELECT COUNT(*) FROM `areas` a
                INNER JOIN `cities` c ON a.`city_id` = c.`id` 
                $whereClause";
        return (int) $this->db->fetchColumn($sql, $params);
    }

    /**
     * Check if key exists in city.
     */
    public function existsByKey(string $key, int $cityId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `areas` WHERE `key` = :key AND `city_id` = :city_id AND `is_deleted` = 0";
        $params = [
            'key' => $key,
            'city_id' => $cityId
        ];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Check if slug exists in city.
     */
    public function existsBySlug(string $slug, int $cityId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `areas` WHERE `slug` = :slug AND `city_id` = :city_id AND `is_deleted` = 0";
        $params = [
            'slug' => $slug,
            'city_id' => $cityId
        ];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Check if Arabic name exists in city.
     */
    public function existsByName(string $name, int $cityId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `areas` WHERE `display_name_ar` = :display_name_ar AND `city_id` = :city_id AND `is_deleted` = 0";
        $params = [
            'display_name_ar' => $name,
            'city_id' => $cityId
        ];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Create a new area record.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `areas` 
                (`public_id`, `city_id`, `key`, `slug`, `display_name_ar`, `display_name_en`, `sort_order`, `meta_title_ar`, `meta_description_ar`, `is_active`, `is_deleted`) 
                VALUES 
                (:public_id, :city_id, :key, :slug, :display_name_ar, :display_name_en, :sort_order, :meta_title_ar, :meta_description_ar, :is_active, 0)";
        
        $this->db->execute($sql, [
            'public_id' => $data['public_id'],
            'city_id' => $data['city_id'],
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
     * Update an existing area record.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `areas` SET 
                `city_id` = :city_id,
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
            'city_id' => $data['city_id'],
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
     * Soft delete an area record.
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE `areas` SET 
                `is_deleted` = 1,
                `deleted_at` = NOW() 
                WHERE `id` = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Restore a soft-deleted area record.
     */
    public function restore(int $id): bool
    {
        $sql = "UPDATE `areas` SET 
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
            $where[] = "a.`is_deleted` = :is_deleted";
            $params['is_deleted'] = $criteria['is_deleted'] ? 1 : 0;
        } else {
            $where[] = "a.`is_deleted` = 0";
        }

        if (isset($criteria['is_active'])) {
            $where[] = "a.`is_active` = :is_active";
            $params['is_active'] = $criteria['is_active'] ? 1 : 0;
        }

        if (isset($criteria['city_id'])) {
            $where[] = "a.`city_id` = :city_id";
            $params['city_id'] = (int)$criteria['city_id'];
        }

        if (!empty($criteria['keyword'])) {
            $keyword = normalize_arabic($criteria['keyword']);
            $where[] = "(a.`key` LIKE :kw_key OR a.`slug` LIKE :kw_slug OR a.`display_name_ar` LIKE :kw_display OR a.`display_name_en` LIKE :kw_display_en OR c.`display_name_ar` LIKE :kw_city)";
            $params['kw_key'] = '%' . $keyword . '%';
            $params['kw_slug'] = '%' . $keyword . '%';
            $params['kw_display'] = '%' . $keyword . '%';
            $params['kw_display_en'] = '%' . $keyword . '%';
            $params['kw_city'] = '%' . $keyword . '%';
        }

        $clause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
        return [$clause, $params];
    }
}
