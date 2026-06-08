<?php
/**
 * ServiceRepository.php
 * Legacy Khadomeh Service Data Repository Wrapper
 */

namespace App\Repositories;

use App\Modules\Services\ServicesRepository;

class ServiceRepository extends ServicesRepository
{
    /**
     * Count all active services (legacy).
     */
    public function count(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `services` WHERE `is_active` = 1 AND `is_deleted` = 0"
        );
    }

    /**
     * Get all active services ordered by sort_order (legacy).
     */
    public function getAllActive(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `services` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC"
        );
    }
}
