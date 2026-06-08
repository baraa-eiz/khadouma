<?php

namespace App\Repositories\Interfaces;

interface RepositoryInterface
{
    /**
     * Find a record by its primary key.
     */
    public function find(int $id): ?array;

    /**
     * Get all records.
     */
    public function all(): array;

    /**
     * Create a new record.
     *
     * @return int Last inserted ID
     */
    public function create(array $data): int;

    /**
     * Update an existing record.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record by ID.
     */
    public function delete(int $id): bool;
}
