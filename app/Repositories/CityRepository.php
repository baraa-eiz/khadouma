<?php
/**
 * CityRepository.php
 * Khadomeh City Data Repository (Legacy Wrapper)
 */

namespace App\Repositories;

use App\Modules\Locations\CitiesRepository;

class CityRepository {
    private $newRepo;

    public function __construct() {
        $this->newRepo = new CitiesRepository();
    }

    /**
     * Count all active cities.
     */
    public function count() {
        return $this->newRepo->countSearch(['is_active' => 1, 'is_deleted' => 0]);
    }

    /**
     * Get all active cities.
     */
    public function getAllActive() {
        return $this->newRepo->search(['is_active' => 1, 'is_deleted' => 0], 'sort_order', 'ASC', 100);
    }
}
