<?php
/**
 * UsersService.php
 * Khadomeh Users Module Business Logic Service
 */

namespace App\Modules\Users;

use App\Core\Database;

class UsersService
{
    private UsersRepositoryInterface $repo;
    private UsersValidation $validation;
    private \App\Services\AdminService $adminService;

    public function __construct(UsersRepositoryInterface $repo)
    {
        $this->repo = $repo;
        $this->validation = new UsersValidation($repo);
        $this->adminService = new \App\Services\AdminService();
    }

    public function getUserById(int $id): ?UserData
    {
        return $this->repo->findById($id);
    }

    public function getUserByPublicId(string $publicId): ?UserData
    {
        return $this->repo->findByPublicId($publicId);
    }

    public function listUsers(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        return $this->repo->listUsers($filters, $limit, $offset);
    }

    public function countUsers(array $filters = []): int
    {
        return $this->repo->countSearch($filters);
    }

    public function searchUsers(array $criteria, string $sortBy = 'created_at', string $sortDir = 'DESC', int $limit = 15, int $offset = 0): array
    {
        return $this->repo->search($criteria, $sortBy, $sortDir, $limit, $offset);
    }

    /**
     * Create a new user account (Admin Action).
     */
    public function createUser(UserData $dto, int $adminUserId, string $ipAddress): int
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $user = $this->repo->create($dto);
            $id = $user->id;

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'create_user',
                'users',
                $id,
                null,
                json_encode($dto->toArray(), JSON_UNESCAPED_UNICODE),
                $ipAddress
            );

            $db->commit();
            return $id;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing user's profile (Admin Action).
     */
    public function updateUser(int $id, UserData $dto, int $adminUserId, string $ipAddress): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $oldUser = $this->repo->findById($id);
            if (!$oldUser) {
                $db->rollBack();
                return false;
            }

            // Ensure correct ID in DTO
            $data = $dto->toArray();
            $data['id'] = $id;
            $updatedDto = UserData::fromArray($data);

            $success = $this->repo->update($updatedDto);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'update_user',
                'users',
                $id,
                json_encode($oldUser->toArray(), JSON_UNESCAPED_UNICODE),
                json_encode($updatedDto->toArray(), JSON_UNESCAPED_UNICODE),
                $ipAddress
            );

            $db->commit();
            return $success;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing user's profile (User Action).
     * Returns: [array $errors, bool $success]
     */
    public function updateProfile(int $userId, array $data): array
    {
        $existing = $this->repo->findById($userId);
        if (!$existing) {
            return [['global' => ['المستخدم غير موجود.']], false];
        }

        // Merge existing non-editable fields with updated inputs
        $mergedData = array_merge($existing->toArray(), $data);

        [$errors, $dto] = $this->validation->validate($mergedData, $userId);
        if (!empty($errors)) {
            return [$errors, false];
        }

        $success = $this->repo->update($dto);
        return [null, $success];
    }

    /**
     * Toggle status (active/suspended) (Admin Action).
     */
    public function toggleUserStatus(int $userId, string $status, int $adminUserId, string $ipAddress): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $oldUser = $this->repo->findById($userId);
            if (!$oldUser) {
                $db->rollBack();
                return false;
            }

            $success = $this->repo->changeStatus($userId, $status);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'toggle_user_status',
                'users',
                $userId,
                json_encode(['status' => $oldUser->status], JSON_UNESCAPED_UNICODE),
                json_encode(['status' => $status], JSON_UNESCAPED_UNICODE),
                $ipAddress
            );

            $db->commit();
            return $success;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete user (Admin Action).
     */
    public function softDeleteUser(int $userId, int $adminUserId, string $ipAddress): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $oldUser = $this->repo->findById($userId);
            if (!$oldUser) {
                $db->rollBack();
                return false;
            }

            $success = $this->repo->delete($userId);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'delete_user',
                'users',
                $userId,
                json_encode($oldUser->toArray(), JSON_UNESCAPED_UNICODE),
                null,
                $ipAddress
            );

            $db->commit();
            return $success;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Restore soft-deleted user (Admin Action).
     */
    public function restoreUser(int $userId, int $adminUserId, string $ipAddress): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $oldUser = $this->repo->findById($userId);
            if (!$oldUser) {
                $db->rollBack();
                return false;
            }

            $success = $this->repo->restore($userId);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'restore_user',
                'users',
                $userId,
                json_encode($oldUser->toArray(), JSON_UNESCAPED_UNICODE),
                null,
                $ipAddress
            );

            $db->commit();
            return $success;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Calculate profile completeness score (0-100%).
     */
    public function calculateCompletionScore(UserData $user): int
    {
        return $user->completion_score;
    }

    // Favorites Logic
    public function getFavorites(int $userId): array
    {
        return $this->repo->getFavorites($userId);
    }

    public function isFavorite(int $userId, string $entityType, string $entityPublicId): bool
    {
        return $this->repo->isFavorite($userId, $entityType, $entityPublicId);
    }

    public function addFavorite(int $userId, string $entityType, string $entityPublicId): bool
    {
        return $this->repo->addFavorite($userId, $entityType, $entityPublicId);
    }

    public function removeFavorite(int $userId, string $entityType, string $entityPublicId): bool
    {
        return $this->repo->removeFavorite($userId, $entityType, $entityPublicId);
    }
}
