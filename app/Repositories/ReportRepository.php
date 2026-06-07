<?php
/**
 * ReportRepository.php
 * Khadomeh Report Data Repository
 */

namespace App\Repositories;

use App\Core\Database;

class ReportRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Count total reports.
     */
    public function count() {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `reports` WHERE `deleted_at` IS NULL"
        );
    }

    /**
     * Count reports by status.
     */
    public function countByStatus($status) {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `reports` WHERE `status` = ? AND `deleted_at` IS NULL",
            [$status]
        );
    }
}
