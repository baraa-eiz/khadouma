<?php
/**
 * AreasRepositoryInterface.php
 * Khadomeh Areas Repository Interface Contract
 */

namespace App\Modules\Locations;

interface AreasRepositoryInterface
{
    /**
     * Find an area by its primary key ID.
     */
    public function find(int $id): ?array;

    /**
     * Find an area by its public UUID.
     */
    public function findByPublicId(string $publicId): ?array;

    /**
     * Find an area by its slug.
     */
    public function findBySlug(string $slug): ?array;

    /**
     * Search and paginate areas.
     */
    public function search(
        array $criteria,
        string $orderBy = 'sort_order',
        string $orderDir = 'ASC',
        int $limit = 15,
        int $offset = 0
    ): array;

    /**
     * Count matching search items for pagination.
     */
    public function countSearch(array $criteria): int;

    /**
     * Check if key exists in a specific city.
     */
    public function existsByKey(string $key, int $cityId, ?int $excludeId = null): bool;

    /**
     * Check if slug exists in a specific city.
     */
    public function existsBySlug(string $slug, int $cityId, ?int $excludeId = null): bool;

    /**
     * Check if Arabic display name exists in a specific city.
     */
    public function existsByName(string $name, int $cityId, ?int $excludeId = null): bool;

    /**
     * Create a new area record and return the generated ID.
     */
    public function create(array $data): int;

    /**
     * Update an existing area record.
     */
    public function update(int $id, array $data): bool;

    /**
     * Soft delete an area record.
     */
    public function softDelete(int $id): bool;

    /**
     * Restore a soft-deleted area record.
     */
    public function restore(int $id): bool;
}
