<?php
/**
 * ProvidersRepositoryInterface.php
 * Khadomeh Providers Repository Interface Contract
 */

namespace App\Modules\Providers;

interface ProvidersRepositoryInterface
{
    public function find(int $id): ?array;
    public function findBySlug(string $slug): ?array;
    public function existsBySlug(string $slug, ?int $excludeId = null): bool;
    public function existsByPhone(string $phone, ?int $excludeId = null): bool;
    
    public function search(
        array $criteria,
        string $orderBy = 'sort_weight',
        string $orderDir = 'ASC',
        int $limit = 15,
        int $offset = 0
    ): array;

    public function countSearch(array $criteria): int;
    
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function softDelete(int $id): bool;
    public function restore(int $id): bool;
    
    // Relation sync methods
    public function syncAreas(int $providerId, array $areaIds): void;
    public function syncServices(int $providerId, array $serviceIds): void;
    public function syncImages(int $providerId, ?string $logoPath, array $galleryPaths): void;
}
