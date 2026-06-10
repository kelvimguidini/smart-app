<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Broker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BrokerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * List brokers with filters, sorting, and pagination.
     * Overrides base list to include 'city'.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
}
