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
        
        $sql = "SELECT DISTINCT p.*, s.display_name_ar as service_name, c.display_name_ar as city_name 
                FROM `providers` p
                LEFT JOIN `services` s ON p.primary_service_id = s.id
                LEFT JOIN `cities` c ON p.city_id = c.id";

        if (!empty($filters['service'])) {
            $sql .= " LEFT JOIN `provider_service_map` psm ON p.id = psm.provider_id
                      LEFT JOIN `services` s2 ON psm.service_id = s2.id";
            $where[] = "(s.slug = :service1 OR s2.slug = :service2)";
            $params['service1'] = $filters['service'];
            $params['service2'] = $filters['service'];
        }

        if (!empty($filters['city'])) {
            $where[] = "c.slug = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['area'])) {
            $sql .= " INNER JOIN `provider_area_map` pam ON p.id = pam.provider_id
                      INNER JOIN `areas` a ON pam.area_id = a.id";
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

        if (!empty($filters['keyword'])) {
            $normalizedKeyword = normalize_arabic($filters['keyword']);
            $where[] = "(p.normalized_name LIKE :keyword OR p.phone LIKE :keyword_raw1 OR p.short_description_ar LIKE :keyword_raw2 OR p.description_ar LIKE :keyword_raw3)";
            $params['keyword'] = '%' . $normalizedKeyword . '%';
            $params['keyword_raw1'] = '%' . $filters['keyword'] . '%';
            $params['keyword_raw2'] = '%' . $filters['keyword'] . '%';
            $params['keyword_raw3'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // Sanitize sorting column to prevent SQL injection
        $allowedCols = ['id', 'display_name_ar', 'phone', 'city_id', 'primary_service_id', 'sort_weight', 'rating', 'verified', 'is_active', 'status', 'created_at'];
        if (!in_array($orderBy, $allowedCols)) {
            $orderBy = 'sort_weight';
        }
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY p.`$orderBy` $orderDir LIMIT :limit OFFSET :offset";

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
        
        $sql = "SELECT COUNT(DISTINCT p.id) FROM `providers` p
                LEFT JOIN `services` s ON p.primary_service_id = s.id
                LEFT JOIN `cities` c ON p.city_id = c.id";

        if (!empty($criteria['service'])) {
            $sql .= " LEFT JOIN `provider_service_map` psm ON p.id = psm.provider_id
                      LEFT JOIN `services` s2 ON psm.service_id = s2.id";
            $where[] = "(s.slug = :service1 OR s2.slug = :service2)";
            $params['service1'] = $criteria['service'];
            $params['service2'] = $criteria['service'];
        }

        if (!empty($criteria['city'])) {
            $where[] = "c.slug = :city";
            $params['city'] = $criteria['city'];
        }

        if (!empty($criteria['area'])) {
            $sql .= " INNER JOIN `provider_area_map` pam ON p.id = pam.provider_id
                      INNER JOIN `areas` a ON pam.area_id = a.id";
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

        if (!empty($criteria['keyword'])) {
            $normalizedKeyword = normalize_arabic($criteria['keyword']);
            $where[] = "(p.normalized_name LIKE :keyword OR p.phone LIKE :keyword_raw1 OR p.short_description_ar LIKE :keyword_raw2 OR p.description_ar LIKE :keyword_raw3)";
            $params['keyword'] = '%' . $normalizedKeyword . '%';
            $params['keyword_raw1'] = '%' . $criteria['keyword'] . '%';
            $params['keyword_raw2'] = '%' . $criteria['keyword'] . '%';
            $params['keyword_raw3'] = '%' . $criteria['keyword'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    /**
     * Create a new provider.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `providers` 
                (`slug`, `display_name_ar`, `normalized_name`, `business_type`, `phone`, `whatsapp`, `city_id`, `primary_service_id`, `short_description_ar`, `description_ar`, `years_experience`, `starting_price`, `price_unit`, `verified`, `is_active`, `sort_weight`, `status`) 
                VALUES 
                (:slug, :display_name_ar, :normalized_name, :business_type, :phone, :whatsapp, :city_id, :primary_service_id, :short_description_ar, :description_ar, :years_experience, :starting_price, :price_unit, :verified, :is_active, :sort_weight, :status)";
        
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
                `status` = :status
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
            "UPDATE `providers` SET `deleted_at` = NOW() WHERE `id` = :id",
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
