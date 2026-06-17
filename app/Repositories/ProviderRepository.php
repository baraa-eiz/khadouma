<?php
/**
 * ProviderRepository.php
 * Khadomeh Provider Data Repository
 */

namespace App\Repositories;

use App\Core\Repository;
use App\Modules\Providers\ProvidersRepositoryInterface;

class ProviderRepository extends Repository implements ProvidersRepositoryInterface
{
    /**
     * Count total active providers.
     */
    public function count(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `providers` WHERE `is_active` = 1 AND `deleted_at` IS NULL"
        );
    }

    /**
     * Count providers by their workflow status (pending, approved, rejected, suspended).
     */
    public function countByStatus(string $status): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `providers` WHERE `status` = ? AND `deleted_at` IS NULL",
            [$status]
        );
    }

    /**
     * Get latest approved providers with limit.
     */
    public function getLatestApproved(int $limit = 10): array
    {
        $sql = "SELECT p.*, s.display_name_ar as service_name, c.display_name_ar as city_name 
                FROM `providers` p
                LEFT JOIN `services` s ON p.primary_service_id = s.id
                LEFT JOIN `cities` c ON p.city_id = c.id
                WHERE p.is_active = 1 AND p.status = 'approved' AND p.deleted_at IS NULL
                ORDER BY p.is_featured DESC, p.sort_weight DESC, p.id DESC
                LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql);
    }

    /**
     * Find a provider by primary key ID.
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT p.*, seo.meta_title_ar, seo.meta_description_ar 
                FROM `providers` p
                LEFT JOIN `seo_metadata` seo ON seo.entity_type = 'provider' AND seo.entity_id = p.id
                WHERE p.id = :id LIMIT 1";
        $provider = $this->db->fetch($sql, ['id' => $id]);
        if (!$provider) {
            return null;
        }
        
        // Load mapped area IDs
        $areasSql = "SELECT area_id FROM `provider_area_map` WHERE provider_id = :id";
        $areas = $this->db->fetchAll($areasSql, ['id' => $id]);
        $provider['areas'] = array_column($areas, 'area_id');
        
        // Load mapped secondary service IDs
        $servicesSql = "SELECT service_id FROM `provider_service_map` WHERE provider_id = :id";
        $services = $this->db->fetchAll($servicesSql, ['id' => $id]);
        $provider['services'] = array_column($services, 'service_id');
        
        // Load logo
        $provider['logo'] = $this->getProviderProfileImage($id);
        
        // Load work photos
        $photosSql = "SELECT image_path FROM `provider_images` WHERE provider_id = :id AND image_type = 'work_photo' AND deleted_at IS NULL";
        $photos = $this->db->fetchAll($photosSql, ['id' => $id]);
        $provider['work_photos'] = array_column($photos, 'image_path');
        
        return $provider;
    }

    /**
     * Find a provider by slug.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->getBySlug($slug);
    }

    /**
     * Fetch single approved provider by slug.
     */
    public function getBySlug(string $slug): ?array
    {
        $sql = "SELECT p.*, s.display_name_ar as service_name, s.slug as service_slug, c.display_name_ar as city_name, c.slug as city_slug
                FROM `providers` p
                LEFT JOIN `services` s ON p.primary_service_id = s.id
                LEFT JOIN `cities` c ON p.city_id = c.id
                WHERE p.slug = :slug AND p.is_active = 1 AND p.status = 'approved' AND p.deleted_at IS NULL
                LIMIT 1";
        $result = $this->db->fetch($sql, ['slug' => $slug]);
        return $result ?: null;
    }

    /**
     * Check if slug exists.
     */
    public function existsBySlug(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `providers` WHERE `slug` = :slug AND `deleted_at` IS NULL";
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Check if phone exists.
     */
    public function existsByPhone(string $phone, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `providers` WHERE `phone` = :phone AND `deleted_at` IS NULL";
        $params = ['phone' => $phone];
        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Search and filter providers (supports pagination, keyword search, and admin flags).
     */
    public function search(
        array $filters,
        string $orderBy = 'sort_weight',
        string $orderDir = 'DESC',
        int $limit = 100,
        int $offset = 0
    ): array {
        $where = [];
        $params = [];
        
        $completionScoreSql = "(
            (CASE WHEN EXISTS(SELECT 1 FROM `provider_images` WHERE `provider_id` = p.id AND `image_type` = 'profile' AND `is_active` = 1 AND `deleted_at` IS NULL) THEN 15 ELSE 0 END) +
            (CASE WHEN EXISTS(SELECT 1 FROM `provider_area_map` WHERE `provider_id` = p.id) THEN 15 ELSE 0 END) +
            (CASE WHEN p.description_ar IS NOT NULL AND TRIM(p.description_ar) != '' THEN 15 ELSE 0 END) +
            (CASE WHEN p.short_description_ar IS NOT NULL AND TRIM(p.short_description_ar) != '' THEN 10 ELSE 0 END) +
            (CASE WHEN p.whatsapp IS NOT NULL AND TRIM(p.whatsapp) != '' THEN 10 ELSE 0 END) +
            (CASE WHEN p.email IS NOT NULL AND TRIM(p.email) != '' THEN 10 ELSE 0 END) +
            (CASE WHEN EXISTS(SELECT 1 FROM `provider_images` WHERE `provider_id` = p.id AND `image_type` = 'work_photo' AND `is_active` = 1 AND `deleted_at` IS NULL) THEN 15 ELSE 0 END) +
            (CASE WHEN p.years_experience > 0 THEN 5 ELSE 0 END) +
            (CASE WHEN EXISTS(SELECT 1 FROM `provider_service_map` WHERE `provider_id` = p.id) THEN 5 ELSE 0 END)
        )";

        $joinServices = false;
        $joinAreas = false;

        if (!empty($filters['service'])) {
            $joinServices = true;
        }

        if (!empty($filters['area'])) {
            $joinAreas = true;
        }

        if (!empty($filters['keyword'])) {
            $joinServices = true;
            $joinAreas = true;
        }

        $sql = "SELECT DISTINCT p.*, s.display_name_ar as service_name, c.display_name_ar as city_name,
                $completionScoreSql AS completion_score 
                FROM `providers` p
                LEFT JOIN `services` s ON p.primary_service_id = s.id
                LEFT JOIN `cities` c ON p.city_id = c.id";

        if ($joinServices) {
            $sql .= " LEFT JOIN `provider_service_map` psm ON p.id = psm.provider_id
                      LEFT JOIN `services` s2 ON psm.service_id = s2.id";
        }

        if ($joinAreas) {
            $sql .= " LEFT JOIN `provider_area_map` pam ON p.id = pam.provider_id
                      LEFT JOIN `areas` a ON pam.area_id = a.id";
        }

        if (!empty($filters['service'])) {
            $where[] = "(s.slug = :service1 OR s2.slug = :service2)";
            $params['service1'] = $filters['service'];
            $params['service2'] = $filters['service'];
        }

        if (!empty($filters['city'])) {
            $where[] = "c.slug = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['area'])) {
            $where[] = "a.slug = :area";
            $params['area'] = $filters['area'];
        }
        
        // Admin specific filters
        if (isset($filters['city_id']) && $filters['city_id'] > 0) {
            $where[] = "p.city_id = :city_id";
            $params['city_id'] = (int)$filters['city_id'];
        }
        
        if (isset($filters['service_id']) && $filters['service_id'] > 0) {
            $where[] = "p.primary_service_id = :primary_service_id";
            $params['primary_service_id'] = (int)$filters['service_id'];
        }

        if (isset($filters['is_active'])) {
            $where[] = "p.is_active = :is_active";
            $params['is_active'] = $filters['is_active'] ? 1 : 0;
        } elseif (!isset($filters['admin_mode'])) {
            $where[] = "p.is_active = 1";
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "p.status = :status";
            $params['status'] = $filters['status'];
        } elseif (!isset($filters['admin_mode'])) {
            $where[] = "p.status = 'approved'";
        }

        if (isset($filters['is_deleted'])) {
            if ($filters['is_deleted'] == 1) {
                $where[] = "p.deleted_at IS NOT NULL";
            } else {
                $where[] = "p.deleted_at IS NULL";
            }
        } else {
            $where[] = "p.deleted_at IS NULL";
        }

        // Advanced filters
        if (isset($filters['rating_min']) && $filters['rating_min'] !== '') {
            $where[] = "p.rating >= :rating_min";
            $params['rating_min'] = (float)$filters['rating_min'];
        }
        if (isset($filters['rating_max']) && $filters['rating_max'] !== '') {
            $where[] = "p.rating <= :rating_max";
            $params['rating_max'] = (float)$filters['rating_max'];
        }
        if (isset($filters['experience_min']) && $filters['experience_min'] !== '') {
            $where[] = "p.years_experience >= :experience_min";
            $params['experience_min'] = (int)$filters['experience_min'];
        }
        if (isset($filters['experience_max']) && $filters['experience_max'] !== '') {
            $where[] = "p.years_experience <= :experience_max";
            $params['experience_max'] = (int)$filters['experience_max'];
        }
        if (isset($filters['business_type']) && $filters['business_type'] !== '') {
            $where[] = "p.business_type = :business_type";
            $params['business_type'] = $filters['business_type'];
        }
        if (isset($filters['verified']) && $filters['verified'] !== '') {
            $where[] = "p.verified = :verified";
            $params['verified'] = $filters['verified'] ? 1 : 0;
        }
        if (isset($filters['phone_verified']) && $filters['phone_verified'] !== '') {
            $where[] = "p.phone_verified = :phone_verified";
            $params['phone_verified'] = $filters['phone_verified'] ? 1 : 0;
        }
        if (isset($filters['identity_verified']) && $filters['identity_verified'] !== '') {
            $where[] = "p.identity_verified = :identity_verified";
            $params['identity_verified'] = $filters['identity_verified'] ? 1 : 0;
        }
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $where[] = "p.is_featured = :is_featured";
            $params['is_featured'] = $filters['is_featured'] ? 1 : 0;
        }
        if (isset($filters['completion_min']) && $filters['completion_min'] !== '') {
            $where[] = "$completionScoreSql >= :completion_min";
            $params['completion_min'] = (int)$filters['completion_min'];
        }
        if (isset($filters['completion_max']) && $filters['completion_max'] !== '') {
            $where[] = "$completionScoreSql <= :completion_max";
            $params['completion_max'] = (int)$filters['completion_max'];
        }

        // Smart Search Keyword Parsing
        if (!empty($filters['keyword'])) {
            $keywords = preg_split('/\s+/u', trim($filters['keyword']), -1, PREG_SPLIT_NO_EMPTY);
            $keywordConditions = [];
            $kIdx = 0;
            foreach ($keywords as $word) {
                $normalizedWord = normalize_arabic($word);
                $paramWord = 'k_word_' . $kIdx;
                $paramRaw = 'k_raw_' . $kIdx;
                
                $keywordConditions[] = "(
                    p.normalized_name LIKE :{$paramWord} OR 
                    p.phone LIKE :{$paramRaw} OR 
                    p.short_description_ar LIKE :{$paramWord} OR 
                    p.description_ar LIKE :{$paramWord} OR 
                    s.display_name_ar LIKE :{$paramWord} OR 
                    s2.display_name_ar LIKE :{$paramWord} OR 
                    c.display_name_ar LIKE :{$paramWord} OR 
                    a.display_name_ar LIKE :{$paramWord}
                )";
                $params[$paramWord] = '%' . $normalizedWord . '%';
                $params[$paramRaw] = '%' . $word . '%';
                $kIdx++;
            }
            if (!empty($keywordConditions)) {
                $where[] = '(' . implode(' AND ', $keywordConditions) . ')';
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // Sanitize sorting column to prevent SQL injection
        $allowedCols = ['id', 'display_name_ar', 'phone', 'city_id', 'primary_service_id', 'sort_weight', 'rating', 'verified', 'is_active', 'status', 'created_at', 'completion_score'];
        if (!in_array($orderBy, $allowedCols)) {
            $orderBy = 'sort_weight';
        }
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY `$orderBy` $orderDir LIMIT :limit OFFSET :offset";

        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare($sql);

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
        $where = [];
        $params = [];
        
        $completionScoreSql = "(
            (CASE WHEN EXISTS(SELECT 1 FROM `provider_images` WHERE `provider_id` = p.id AND `image_type` = 'profile' AND `is_active` = 1 AND `deleted_at` IS NULL) THEN 15 ELSE 0 END) +
            (CASE WHEN EXISTS(SELECT 1 FROM `provider_area_map` WHERE `provider_id` = p.id) THEN 15 ELSE 0 END) +
            (CASE WHEN p.description_ar IS NOT NULL AND TRIM(p.description_ar) != '' THEN 15 ELSE 0 END) +
            (CASE WHEN p.short_description_ar IS NOT NULL AND TRIM(p.short_description_ar) != '' THEN 10 ELSE 0 END) +
            (CASE WHEN p.whatsapp IS NOT NULL AND TRIM(p.whatsapp) != '' THEN 10 ELSE 0 END) +
            (CASE WHEN p.email IS NOT NULL AND TRIM(p.email) != '' THEN 10 ELSE 0 END) +
            (CASE WHEN EXISTS(SELECT 1 FROM `provider_images` WHERE `provider_id` = p.id AND `image_type` = 'work_photo' AND `is_active` = 1 AND `deleted_at` IS NULL) THEN 15 ELSE 0 END) +
            (CASE WHEN p.years_experience > 0 THEN 5 ELSE 0 END) +
            (CASE WHEN EXISTS(SELECT 1 FROM `provider_service_map` WHERE `provider_id` = p.id) THEN 5 ELSE 0 END)
        )";

        $joinServices = false;
        $joinAreas = false;

        if (!empty($criteria['service'])) {
            $joinServices = true;
        }

        if (!empty($criteria['area'])) {
            $joinAreas = true;
        }

        if (!empty($criteria['keyword'])) {
            $joinServices = true;
            $joinAreas = true;
        }

        $sql = "SELECT COUNT(DISTINCT p.id) FROM `providers` p
                LEFT JOIN `services` s ON p.primary_service_id = s.id
                LEFT JOIN `cities` c ON p.city_id = c.id";

        if ($joinServices) {
            $sql .= " LEFT JOIN `provider_service_map` psm ON p.id = psm.provider_id
                      LEFT JOIN `services` s2 ON psm.service_id = s2.id";
        }

        if ($joinAreas) {
            $sql .= " LEFT JOIN `provider_area_map` pam ON p.id = pam.provider_id
                      LEFT JOIN `areas` a ON pam.area_id = a.id";
        }

        if (!empty($criteria['service'])) {
            $where[] = "(s.slug = :service1 OR s2.slug = :service2)";
            $params['service1'] = $criteria['service'];
            $params['service2'] = $criteria['service'];
        }

        if (!empty($criteria['city'])) {
            $where[] = "c.slug = :city";
            $params['city'] = $criteria['city'];
        }

        if (!empty($criteria['area'])) {
            $where[] = "a.slug = :area";
            $params['area'] = $criteria['area'];
        }
        
        if (isset($criteria['city_id']) && $criteria['city_id'] > 0) {
            $where[] = "p.city_id = :city_id";
            $params['city_id'] = (int)$criteria['city_id'];
        }
        
        if (isset($criteria['service_id']) && $criteria['service_id'] > 0) {
            $where[] = "p.primary_service_id = :primary_service_id";
            $params['primary_service_id'] = (int)$criteria['service_id'];
        }

        if (isset($criteria['is_active'])) {
            $where[] = "p.is_active = :is_active";
            $params['is_active'] = $criteria['is_active'] ? 1 : 0;
        } elseif (!isset($criteria['admin_mode'])) {
            $where[] = "p.is_active = 1";
        }

        if (isset($criteria['status']) && $criteria['status'] !== '') {
            $where[] = "p.status = :status";
            $params['status'] = $criteria['status'];
        } elseif (!isset($criteria['admin_mode'])) {
            $where[] = "p.status = 'approved'";
        }

        if (isset($criteria['is_deleted'])) {
            if ($criteria['is_deleted'] == 1) {
                $where[] = "p.deleted_at IS NOT NULL";
            } else {
                $where[] = "p.deleted_at IS NULL";
            }
        } else {
            $where[] = "p.deleted_at IS NULL";
        }

        // Advanced filters
        if (isset($criteria['rating_min']) && $criteria['rating_min'] !== '') {
            $where[] = "p.rating >= :rating_min";
            $params['rating_min'] = (float)$criteria['rating_min'];
        }
        if (isset($criteria['rating_max']) && $criteria['rating_max'] !== '') {
            $where[] = "p.rating <= :rating_max";
            $params['rating_max'] = (float)$criteria['rating_max'];
        }
        if (isset($criteria['experience_min']) && $criteria['experience_min'] !== '') {
            $where[] = "p.years_experience >= :experience_min";
            $params['experience_min'] = (int)$criteria['experience_min'];
        }
        if (isset($criteria['experience_max']) && $criteria['experience_max'] !== '') {
            $where[] = "p.years_experience <= :experience_max";
            $params['experience_max'] = (int)$criteria['experience_max'];
        }
        if (isset($criteria['business_type']) && $criteria['business_type'] !== '') {
            $where[] = "p.business_type = :business_type";
            $params['business_type'] = $criteria['business_type'];
        }
        if (isset($criteria['verified']) && $criteria['verified'] !== '') {
            $where[] = "p.verified = :verified";
            $params['verified'] = $criteria['verified'] ? 1 : 0;
        }
        if (isset($criteria['phone_verified']) && $criteria['phone_verified'] !== '') {
            $where[] = "p.phone_verified = :phone_verified";
            $params['phone_verified'] = $criteria['phone_verified'] ? 1 : 0;
        }
        if (isset($criteria['identity_verified']) && $criteria['identity_verified'] !== '') {
            $where[] = "p.identity_verified = :identity_verified";
            $params['identity_verified'] = $criteria['identity_verified'] ? 1 : 0;
        }
        if (isset($criteria['is_featured']) && $criteria['is_featured'] !== '') {
            $where[] = "p.is_featured = :is_featured";
            $params['is_featured'] = $criteria['is_featured'] ? 1 : 0;
        }
        if (isset($criteria['completion_min']) && $criteria['completion_min'] !== '') {
            $where[] = "$completionScoreSql >= :completion_min";
            $params['completion_min'] = (int)$criteria['completion_min'];
        }
        if (isset($criteria['completion_max']) && $criteria['completion_max'] !== '') {
            $where[] = "$completionScoreSql <= :completion_max";
            $params['completion_max'] = (int)$criteria['completion_max'];
        }

        // Smart Search Keyword Parsing
        if (!empty($criteria['keyword'])) {
            $keywords = preg_split('/\s+/u', trim($criteria['keyword']), -1, PREG_SPLIT_NO_EMPTY);
            $keywordConditions = [];
            $kIdx = 0;
            foreach ($keywords as $word) {
                $normalizedWord = normalize_arabic($word);
                $paramWord = 'k_word_' . $kIdx;
                $paramRaw = 'k_raw_' . $kIdx;
                
                $keywordConditions[] = "(
                    p.normalized_name LIKE :{$paramWord} OR 
                    p.phone LIKE :{$paramRaw} OR 
                    p.short_description_ar LIKE :{$paramWord} OR 
                    p.description_ar LIKE :{$paramWord} OR 
                    s.display_name_ar LIKE :{$paramWord} OR 
                    s2.display_name_ar LIKE :{$paramWord} OR 
                    c.display_name_ar LIKE :{$paramWord} OR 
                    a.display_name_ar LIKE :{$paramWord}
                )";
                $params[$paramWord] = '%' . $normalizedWord . '%';
                $params[$paramRaw] = '%' . $word . '%';
                $kIdx++;
            }
            if (!empty($keywordConditions)) {
                $where[] = '(' . implode(' AND ', $keywordConditions) . ')';
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    /**
     * Bulk update workflow status.
     */
    public function bulkUpdateStatus(array $ids, string $status): bool
    {
        if (empty($ids)) return false;
        $inQuery = implode(',', array_map('intval', $ids));
        return $this->db->execute(
            "UPDATE `providers` SET `status` = :status, `updated_at` = CURRENT_TIMESTAMP WHERE `id` IN ($inQuery)",
            ['status' => $status]
        );
    }

    /**
     * Bulk update is_active status.
     */
    public function bulkUpdateIsActive(array $ids, bool $isActive): bool
    {
        if (empty($ids)) return false;
        $inQuery = implode(',', array_map('intval', $ids));
        return $this->db->execute(
            "UPDATE `providers` SET `is_active` = :is_active, `updated_at` = CURRENT_TIMESTAMP WHERE `id` IN ($inQuery)",
            ['is_active' => $isActive ? 1 : 0]
        );
    }

    /**
     * Bulk soft delete.
     */
    public function bulkSoftDelete(array $ids): bool
    {
        if (empty($ids)) return false;
        $inQuery = implode(',', array_map('intval', $ids));
        return $this->db->execute(
            "UPDATE `providers` SET `deleted_at` = CURRENT_TIMESTAMP WHERE `id` IN ($inQuery)"
        );
    }

    /**
     * Bulk restore.
     */
    public function bulkRestore(array $ids): bool
    {
        if (empty($ids)) return false;
        $inQuery = implode(',', array_map('intval', $ids));
        return $this->db->execute(
            "UPDATE `providers` SET `deleted_at` = NULL WHERE `id` IN ($inQuery)"
        );
    }

    /**
     * Create a new provider.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `providers` 
                (`slug`, `display_name_ar`, `normalized_name`, `business_type`, `phone`, `whatsapp`, `city_id`, `primary_service_id`, `short_description_ar`, `description_ar`, `years_experience`, `starting_price`, `price_unit`, `verified`, `is_active`, `sort_weight`, `status`, `website`, `working_hours`, `social_links`) 
                VALUES 
                (:slug, :display_name_ar, :normalized_name, :business_type, :phone, :whatsapp, :city_id, :primary_service_id, :short_description_ar, :description_ar, :years_experience, :starting_price, :price_unit, :verified, :is_active, :sort_weight, :status, :website, :working_hours, :social_links)";
        
        $this->db->execute($sql, [
            'slug' => $data['slug'],
            'display_name_ar' => $data['display_name_ar'],
            'normalized_name' => normalize_arabic($data['display_name_ar']),
            'business_type' => $data['business_type'],
            'phone' => $data['phone'],
            'whatsapp' => $data['whatsapp'] ?? null,
            'city_id' => $data['city_id'],
            'primary_service_id' => $data['primary_service_id'],
            'short_description_ar' => $data['short_description_ar'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
            'starting_price' => $data['starting_price'] ?? null,
            'price_unit' => $data['price_unit'] ?? 'hour',
            'verified' => $data['verified'] ? 1 : 0,
            'is_active' => $data['is_active'] ? 1 : 0,
            'sort_weight' => $data['sort_weight'] ?? 0,
            'status' => $data['status'] ?? 'approved',
            'website' => $data['website'] ?? null,
            'working_hours' => $data['working_hours'] ?? null,
            'social_links' => isset($data['social_links']) ? (is_array($data['social_links']) ? json_encode($data['social_links'], JSON_UNESCAPED_UNICODE) : $data['social_links']) : null,
        ]);
        
        $providerId = (int)$this->db->lastInsertId();
        
        // Sync relations
        $this->syncAreas($providerId, $data['areas'] ?? []);
        $this->syncServices($providerId, $data['services'] ?? []);
        $this->syncImages($providerId, $data['logo'] ?? null, $data['work_photos'] ?? []);
        $this->syncSeo($providerId, $data['meta_title_ar'] ?? null, $data['meta_description_ar'] ?? null);
        
        return $providerId;
    }

    /**
     * Update an existing provider.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `providers` SET 
                `slug` = :slug,
                `display_name_ar` = :display_name_ar,
                `normalized_name` = :normalized_name,
                `business_type` = :business_type,
                `phone` = :phone,
                `whatsapp` = :whatsapp,
                `city_id` = :city_id,
                `primary_service_id` = :primary_service_id,
                `short_description_ar` = :short_description_ar,
                `description_ar` = :description_ar,
                `years_experience` = :years_experience,
                `starting_price` = :starting_price,
                `price_unit` = :price_unit,
                `verified` = :verified,
                `is_active` = :is_active,
                `sort_weight` = :sort_weight,
                `status` = :status,
                `website` = :website,
                `working_hours` = :working_hours,
                `social_links` = :social_links
                WHERE `id` = :id";
        
        $res = $this->db->execute($sql, [
            'id' => $id,
            'slug' => $data['slug'],
            'display_name_ar' => $data['display_name_ar'],
            'normalized_name' => normalize_arabic($data['display_name_ar']),
            'business_type' => $data['business_type'],
            'phone' => $data['phone'],
            'whatsapp' => $data['whatsapp'] ?? null,
            'city_id' => $data['city_id'],
            'primary_service_id' => $data['primary_service_id'],
            'short_description_ar' => $data['short_description_ar'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
            'starting_price' => $data['starting_price'] ?? null,
            'price_unit' => $data['price_unit'] ?? 'hour',
            'verified' => $data['verified'] ? 1 : 0,
            'is_active' => $data['is_active'] ? 1 : 0,
            'sort_weight' => $data['sort_weight'] ?? 0,
            'status' => $data['status'] ?? 'approved',
            'website' => $data['website'] ?? null,
            'working_hours' => $data['working_hours'] ?? null,
            'social_links' => isset($data['social_links']) ? (is_array($data['social_links']) ? json_encode($data['social_links'], JSON_UNESCAPED_UNICODE) : $data['social_links']) : null,
        ]);
        
        // Sync relations
        $this->syncAreas($id, $data['areas'] ?? []);
        $this->syncServices($id, $data['services'] ?? []);
        $this->syncImages($id, $data['logo'] ?? null, $data['work_photos'] ?? []);
        $this->syncSeo($id, $data['meta_title_ar'] ?? null, $data['meta_description_ar'] ?? null);
        
        return $res;
    }

    /**
     * Soft delete a provider.
     */
    public function softDelete(int $id): bool
    {
        return $this->db->execute(
            "UPDATE `providers` SET `deleted_at` = CURRENT_TIMESTAMP WHERE `id` = :id",
            ['id' => $id]
        );
    }

    /**
     * Restore a soft-deleted provider.
     */
    public function restore(int $id): bool
    {
        return $this->db->execute(
            "UPDATE `providers` SET `deleted_at` = NULL WHERE `id` = :id",
            ['id' => $id]
        );
    }

    /**
     * Get areas covered by a provider.
     */
    public function getProviderAreas(int $providerId): array
    {
        $sql = "SELECT a.* 
                FROM `areas` a
                INNER JOIN `provider_area_map` pam ON a.id = pam.area_id
                WHERE pam.provider_id = :provider_id AND a.is_active = 1 AND a.deleted_at IS NULL
                ORDER BY a.sort_order ASC, a.display_name_ar ASC";
        return $this->db->fetchAll($sql, ['provider_id' => $providerId]);
    }

    /**
     * Get secondary services of a provider.
     */
    public function getProviderSecondaryServices(int $providerId): array
    {
        $sql = "SELECT s.* 
                FROM `services` s
                INNER JOIN `provider_service_map` psm ON s.id = psm.service_id
                WHERE psm.provider_id = :provider_id AND s.is_active = 1 AND s.is_deleted = 0
                ORDER BY s.sort_order ASC, s.display_name_ar ASC";
        return $this->db->fetchAll($sql, ['provider_id' => $providerId]);
    }

    /**
     * Get avatar/logo path of a provider.
     */
    public function getProviderProfileImage(int $providerId): ?string
    {
        $sql = "SELECT image_path FROM `provider_images` 
                WHERE provider_id = :provider_id AND image_type = 'profile' AND is_active = 1 AND deleted_at IS NULL
                LIMIT 1";
        return $this->db->fetchColumn($sql, ['provider_id' => $providerId]);
    }

    /**
     * Get work photos of a provider.
     */
    public function getProviderWorkPhotos(int $providerId): array
    {
        $sql = "SELECT image_path, thumbnail_path, alt_text_ar, caption_ar 
                FROM `provider_images` 
                WHERE provider_id = :provider_id AND image_type = 'work_photo' AND is_active = 1 AND deleted_at IS NULL
                ORDER BY sort_order ASC, id ASC";
        return $this->db->fetchAll($sql, ['provider_id' => $providerId]);
    }

    /**
     * Get approved reviews for a provider.
     */
    public function getProviderReviews(int $providerId): array
    {
        $sql = "SELECT * FROM `reviews` 
                WHERE provider_id = :provider_id AND is_approved = 1 AND deleted_at IS NULL
                ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, ['provider_id' => $providerId]);
    }

    /**
     * Sync areas.
     */
    public function syncAreas(int $providerId, array $areaIds): void
    {
        $this->db->execute(
            "DELETE FROM `provider_area_map` WHERE `provider_id` = :id",
            ['id' => $providerId]
        );
        foreach ($areaIds as $areaId) {
            $this->db->execute(
                "INSERT INTO `provider_area_map` (`provider_id`, `area_id`) VALUES (:provider_id, :area_id)",
                ['provider_id' => $providerId, 'area_id' => (int)$areaId]
            );
        }
    }

    /**
     * Sync services.
     */
    public function syncServices(int $providerId, array $serviceIds): void
    {
        $this->db->execute(
            "DELETE FROM `provider_service_map` WHERE `provider_id` = :id",
            ['id' => $providerId]
        );
        foreach ($serviceIds as $serviceId) {
            $this->db->execute(
                "INSERT INTO `provider_service_map` (`provider_id`, `service_id`) VALUES (:provider_id, :service_id)",
                ['provider_id' => $providerId, 'service_id' => (int)$serviceId]
            );
        }
    }

    /**
     * Sync images.
     */
    public function syncImages(int $providerId, ?string $logoPath, array $galleryPaths): void
    {
        if ($logoPath !== null && trim($logoPath) !== '') {
            $this->db->execute(
                "DELETE FROM `provider_images` WHERE `provider_id` = :id AND `image_type` = 'profile'",
                ['id' => $providerId]
            );
            $this->db->execute(
                "INSERT INTO `provider_images` (`provider_id`, `image_path`, `image_type`, `is_active`) 
                 VALUES (:id, :path, 'profile', 1)",
                ['id' => $providerId, 'path' => $logoPath]
            );
        }
        
        if (!empty($galleryPaths)) {
            $this->db->execute(
                "DELETE FROM `provider_images` WHERE `provider_id` = :id AND `image_type` = 'work_photo'",
                ['id' => $providerId]
            );
            foreach ($galleryPaths as $path) {
                if (trim($path) !== '') {
                    $this->db->execute(
                        "INSERT INTO `provider_images` (`provider_id`, `image_path`, `image_type`, `is_active`) 
                         VALUES (:id, :path, 'work_photo', 1)",
                        ['id' => $providerId, 'path' => $path]
                    );
                }
            }
        }
    }

    /**
     * Sync SEO data.
     */
    public function syncSeo(int $providerId, ?string $metaTitle, ?string $metaDesc): void
    {
        $this->db->execute(
            "DELETE FROM `seo_metadata` WHERE `entity_type` = 'provider' AND `entity_id` = :id",
            ['id' => $providerId]
        );
        
        if (($metaTitle !== null && trim($metaTitle) !== '') || ($metaDesc !== null && trim($metaDesc) !== '')) {
            $this->db->execute(
                "INSERT INTO `seo_metadata` (`entity_type`, `entity_id`, `meta_title_ar`, `meta_description_ar`) 
                 VALUES ('provider', :id, :title, :desc)",
                [
                    'id' => $providerId,
                    'title' => $metaTitle ?? '',
                    'desc' => $metaDesc ?? ''
                ]
            );
        }
    }
}
