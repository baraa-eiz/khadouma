<?php
/**
 * ServiceRepository.php
 * Khadomeh Service Data Repository
 */

namespace App\Repositories;

use App\Core\Database;

class ServiceRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Count all active services.
     */
    public function count() {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `services` WHERE `is_active` = 1 AND `deleted_at` IS NULL"
        );
    }

    /**
     * Get all active services ordered by sort_order.
     */
    public function getAllActive() {
        return $this->db->fetchAll(
            "SELECT * FROM `services` WHERE `is_active` = 1 AND `deleted_at` IS NULL ORDER BY `sort_order` ASC"
        );
    }
}
