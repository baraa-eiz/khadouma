<?php
/**
 * AreasService.php
 * Khadomeh Areas Business Logic Layer
 */

namespace App\Modules\Locations;

use App\Core\Database;
use App\Services\AdminService;

class AreasService
{
    private AreasRepositoryInterface $repo;
    private AdminService $adminService;

    public function __construct(AreasRepositoryInterface $repo)
    {
        $this->repo = $repo;
        $this->adminService = new AdminService();
    }

    /**
     * Get area by its internal ID.
     */
    public function getById(int $id): ?array
    {
        return $this->repo->find($id);
    }

    /**
     * Get area by its public UUID.
     */
    public function getByPublicId(string $publicId): ?array
    {
        return $this->repo->findByPublicId($publicId);
    }

    /**
     * Create a new area under transaction with audit logs.
     */
    public function createArea(AreaData $dto, int $adminUserId, string $ipAddress): int
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $data = $dto->toArray();
            $id = $this->repo->create($data);

            // Log administrative action
            $this->adminService->logAuditEvent(
                $adminUserId,
                'create_area',
                'areas',
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
     * Update an area under transaction with audit logs.
     */
    public function updateArea(int $id, AreaData $dto, int $adminUserId, string $ipAddress): bool
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
                'update_area',
                'areas',
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
     * Soft delete an area under transaction with audit logs.
     */
    public function softDeleteArea(int $id, int $adminUserId, string $ipAddress): bool
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
                'delete_area',
                'areas',
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
     * Restore a soft-deleted area under transaction with audit logs.
     */
    public function restoreArea(int $id, int $adminUserId, string $ipAddress): bool
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
                'restore_area',
                'areas',
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
     * Search and paginate areas.
     */
    public function searchAreas(
        array $criteria,
        string $orderBy = 'sort_order',
        string $orderDir = 'ASC',
        int $limit = 15,
        int $offset = 0
    ): array {
        return $this->repo->search($criteria, $orderBy, $orderDir, $limit, $offset);
    }

    /**
     * Get count of areas matching criteria.
     */
    public function countAreas(array $criteria): int
    {
        return $this->repo->countSearch($criteria);
    }
}
