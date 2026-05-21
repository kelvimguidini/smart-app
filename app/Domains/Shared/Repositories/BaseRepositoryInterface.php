<?php

namespace App\Domains\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * List records with filters, sorting, and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Find a record by its ID.
     *
     * @param int $id
     * @param bool $includeInactive
     * @return Model|null
     */
    public function find(int $id, bool $includeInactive = false): ?Model;

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete a record by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Activate a record by ID.
     *
     * @param int $id
     * @return bool
     */
    public function activate(int $id): bool;

    /**
     * Deactivate a record by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deactivate(int $id): bool;
}
