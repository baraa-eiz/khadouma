<?php
/**
 * ProviderRepository.php
 * Khadomeh Provider Data Repository
 */

namespace App\Repositories;

use App\Core\Database;

class ProviderRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Count total active providers.
     */
    public function count() {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `providers` WHERE `is_active` = 1 AND `deleted_at` IS NULL"
        );
    }

    /**
     * Count providers by their workflow status (pending, approved, rejected, suspended).
     */
    public function countByStatus($status) {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `providers` WHERE `status` = ? AND `deleted_at` IS NULL",
            [$status]
        );
    }

    /**
     * Get latest approved providers with limit.
     */
    public function getLatestApproved($limit = 10) {
        $sql = "SELECT p.*, s.display_name_ar as service_name, c.display_name_ar as city_name 
                FROM `providers` p
                LEFT JOIN `services` s ON p.primary_service_id = s.id
                LEFT JOIN `cities` c ON p.city_id = c.id
                WHERE p.is_active = 1 AND p.status = 'approved' AND p.deleted_at IS NULL
                ORDER BY p.is_featured DESC, p.sort_weight DESC, p.id DESC
                LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql);
    }
}
