<?php
/**
 * CitiesRepositoryInterface.php
 * Khadomeh Cities Repository Interface Contract
 */

namespace App\Modules\Locations;

interface CitiesRepositoryInterface
{
    /**
     * Find a city by its primary key ID.
     */
    public function find(int $id): ?array;

    /**
     * Find a city by its public UUID.
     */
    public function findByPublicId(string $publicId): ?array;

    /**
     * Find a city by its slug.
     */
    public function findBySlug(string $slug): ?array;

    /**
     * Search and paginate cities.
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
     * Check if key exists.
     */
    public function existsByKey(string $key, ?int $excludeId = null): bool;

    /**
     * Check if slug exists.
     */
    public function existsBySlug(string $slug, ?int $excludeId = null): bool;

    /**
     * Check if Arabic display name exists.
     */
    public function existsByName(string $name, ?int $excludeId = null): bool;

    /**
     * Create a new city record and return the generated ID.
     */
    public function create(array $data): int;

    /**
     * Update an existing city record.
     */
    public function update(int $id, array $data): bool;

    /**
     * Soft delete a city record.
     */
    public function softDelete(int $id): bool;

    /**
     * Restore a soft-deleted city record.
     */
    public function restore(int $id): bool;
}
