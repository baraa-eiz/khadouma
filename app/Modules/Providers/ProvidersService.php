<?php
/**
 * ProvidersService.php
 * Khadomeh Providers Business Logic Layer
 */

namespace App\Modules\Providers;

use App\Core\Database;
use App\Services\AdminService;

class ProvidersService
{
    private ProvidersRepositoryInterface $repo;
    private AdminService $adminService;

    public function __construct(ProvidersRepositoryInterface $repo)
    {
        $this->repo = $repo;
        $this->adminService = new AdminService();
    }

    public function getById(int $id): ?array
    {
        return $this->repo->find($id);
    }

    public function createProvider(ProviderData $dto, int $adminUserId, string $ipAddress): int
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $data = $dto->toArray();
            $id = $this->repo->create($data);

            $this->adminService->logAuditEvent(
                $adminUserId,
                'create_provider',
                'providers',
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

    public function updateProvider(int $id, ProviderData $dto, int $adminUserId, string $ipAddress): bool
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

            $this->adminService->logAuditEvent(
                $adminUserId,
                'update_provider',
                'providers',
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

    public function softDeleteProvider(int $id, int $adminUserId, string $ipAddress): bool
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

            $this->adminService->logAuditEvent(
                $adminUserId,
                'delete_provider',
                'providers',
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

    public function restoreProvider(int $id, int $adminUserId, string $ipAddress): bool
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

            $this->adminService->logAuditEvent(
                $adminUserId,
                'restore_provider',
                'providers',
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

    public function searchProviders(
        array $criteria,
        string $orderBy = 'sort_weight',
        string $orderDir = 'DESC',
        int $limit = 15,
        int $offset = 0
    ): array {
        return $this->repo->search($criteria, $orderBy, $orderDir, $limit, $offset);
    }

    public function countProviders(array $criteria): int
    {
        return $this->repo->countSearch($criteria);
    }
}
