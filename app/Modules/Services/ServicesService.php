<?php
/**
 * ServicesService.php
 * Khadomeh Services Business Logic Layer
 */

namespace App\Modules\Services;

use App\Core\Database;
use App\Services\AdminService;

class ServicesService
{
    private ServicesRepositoryInterface $repo;
    private AdminService $adminService;

    public function __construct(ServicesRepositoryInterface $repo)
    {
        $this->repo = $repo;
        $this->adminService = new AdminService();
    }

    /**
     * Get service by its internal ID.
     */
    public function getById(int $id): ?array
    {
        return $this->repo->find($id);
    }

    /**
     * Get service by its public UUID.
     */
    public function getByPublicId(string $publicId): ?array
    {
        return $this->repo->findByPublicId($publicId);
    }

    /**
     * Create a new service under transaction with audit logs.
     */
    public function createService(ServiceData $dto, int $adminUserId, string $ipAddress): int
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $data = $dto->toArray();
            $id = $this->repo->create($data);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'create_service',
                'services',
                $id,
                null,
                json_encode($data, JSON_UNESCAPED_UNICODE),
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
     * Update a service under transaction with audit logs.
     */
    public function updateService(int $id, ServiceData $dto, int $adminUserId, string $ipAddress): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $oldData = $this->repo->find($id);
            if (!$oldData) {
                $db->rollBack();
                return false;
            }

            $newData = $dto->toArray();
            $result = $this->repo->update($id, $newData);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'update_service',
                'services',
                $id,
                json_encode($oldData, JSON_UNESCAPED_UNICODE),
                json_encode($newData, JSON_UNESCAPED_UNICODE),
                $ipAddress
            );

            $db->commit();
            return $result;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete a service under transaction with audit logs.
     */
    public function softDeleteService(int $id, int $adminUserId, string $ipAddress): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $oldData = $this->repo->find($id);
            if (!$oldData) {
                $db->rollBack();
                return false;
            }

            $result = $this->repo->softDelete($id);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'delete_service',
                'services',
                $id,
                json_encode($oldData, JSON_UNESCAPED_UNICODE),
                null,
                $ipAddress
            );

            $db->commit();
            return $result;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted service under transaction with audit logs.
     */
    public function restoreService(int $id, int $adminUserId, string $ipAddress): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $oldData = $this->repo->find($id);
            if (!$oldData) {
                $db->rollBack();
                return false;
            }

            $result = $this->repo->restore($id);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'restore_service',
                'services',
                $id,
                json_encode($oldData, JSON_UNESCAPED_UNICODE),
                null,
                $ipAddress
            );

            $db->commit();
            return $result;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Search and paginate services.
     */
    public function searchServices(
        array $criteria,
        string $orderBy = 'sort_order',
        string $orderDir = 'ASC',
        int $limit = 15,
        int $offset = 0
    ): array {
        return $this->repo->search($criteria, $orderBy, $orderDir, $limit, $offset);
    }

    /**
     * Get count of services matching criteria.
     */
    public function countServices(array $criteria): int
    {
        return $this->repo->countSearch($criteria);
    }
}
