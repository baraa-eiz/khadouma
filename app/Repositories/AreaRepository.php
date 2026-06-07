<?php
/**
 * AreaRepository.php
 * Khadomeh Area Data Repository
 */

namespace App\Repositories;

use App\Core\Database;

class AreaRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Count all active areas.
     */
    public function count() {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `areas` WHERE `is_active` = 1 AND `deleted_at` IS NULL"
        );
    }

    /**
     * Get all active areas in a specific city.
     */
    public function getByCityId($cityId) {
        return $this->db->fetchAll(
            "SELECT * FROM `areas` WHERE `city_id` = ? AND `is_active` = 1 AND `deleted_at` IS NULL ORDER BY `sort_order` ASC",
            [$cityId]
        );
    }
}
