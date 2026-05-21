<?php

namespace App\Domains\Shared\Services;

use App\Models\ServiceType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServiceTypeServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): ServiceType;
    public function update(int $id, array $data): ServiceType;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
