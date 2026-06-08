<?php
/**
 * AreaRepository.php
 * Khadomeh Area Data Repository (Legacy Wrapper)
 */

namespace App\Repositories;

use App\Modules\Locations\AreasRepository;

class AreaRepository {
    private $newRepo;

    public function __construct() {
        $this->newRepo = new AreasRepository();
    }

    /**
     * Count all active areas.
     */
    public function count() {
        return $this->newRepo->countSearch(['is_active' => 1, 'is_deleted' => 0]);
    }

    /**
     * Get all active areas in a specific city.
     */
    public function getByCityId($cityId) {
        return $this->newRepo->search([
            'city_id' => $cityId,
            'is_active' => 1,
            'is_deleted' => 0
        ], 'sort_order', 'ASC', 100);
    }
}
