<?php

namespace App\Domains\Shared\Services;

use App\Models\Broker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BrokerServiceInterface
{
    /**
     * List brokers with filters, sorting, and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new broker.
     *
     * @param array $data
     * @return Broker
     */
    public function create(array $data): Broker;

    /**
     * Update an existing broker.
     *
     * @param int $id
     * @param array $data
     * @return Broker
     */
    public function update(int $id, array $data): Broker;

    /**
     * Delete a broker.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Activate a broker.
     *
     * @param int $id
     * @return bool
     */
    public function activate(int $id): bool;

    /**
     * Deactivate a broker.
     *
     * @param int $id
     * @return bool
     */
    public function deactivate(int $id): bool;
}
