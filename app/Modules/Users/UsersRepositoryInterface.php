<?php
/**
 * UsersRepositoryInterface.php
 * Khadomeh Users Repository Interface
 */

namespace App\Modules\Users;

interface UsersRepositoryInterface
{
    public function findById(int $id): ?UserData;
    public function findByPublicId(string $publicId): ?UserData;
    public function findByEmail(string $email): ?UserData;
    public function findByPhone(string $phone): ?UserData;
    public function create(UserData $userData): UserData;
    public function update(UserData $userData): bool;
    public function delete(int $id): bool;
    public function restore(int $id): bool;
    public function changeStatus(int $id, string $status): bool;
    public function listUsers(array $filters = [], int $limit = 15, int $offset = 0): array;
    public function countUsers(array $filters = []): int;
    public function search(array $criteria, string $sortBy = 'created_at', string $sortDir = 'DESC', int $limit = 15, int $offset = 0): array;
    public function countSearch(array $criteria): int;

    // Favorites management
    public function getFavorites(int $userId): array;
    public function isFavorite(int $userId, string $entityType, string $entityPublicId): bool;
    public function addFavorite(int $userId, string $entityType, string $entityPublicId): bool;
    public function removeFavorite(int $userId, string $entityType, string $entityPublicId): bool;
}
