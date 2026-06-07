<?php
/**
 * CityRepository.php
 * Khadomeh City Data Repository
 */

namespace App\Repositories;

use App\Core\Database;

class CityRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Count all active cities.
     */
    public function count() {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `cities` WHERE `is_active` = 1 AND `deleted_at` IS NULL"
        );
    }

    /**
     * Get all active cities.
     */
    public function getAllActive() {
        return $this->db->fetchAll(
            "SELECT * FROM `cities` WHERE `is_active` = 1 AND `deleted_at` IS NULL ORDER BY `sort_order` ASC"
        );
    }
}
