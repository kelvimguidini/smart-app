<?php

namespace App\Domains\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProviderAirfareRepositoryInterface extends BaseRepositoryInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
}
