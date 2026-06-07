<?php
/**
 * ReviewRepository.php
 * Khadomeh Review Data Repository
 */

namespace App\Repositories;

use App\Core\Database;

class ReviewRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Count total reviews.
     */
    public function count() {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `reviews` WHERE `deleted_at` IS NULL"
        );
    }

    /**
     * Count reviews by approval status.
     */
    public function countByStatus($status) {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `reviews` WHERE `status` = ? AND `deleted_at` IS NULL",
            [$status]
        );
    }
}
