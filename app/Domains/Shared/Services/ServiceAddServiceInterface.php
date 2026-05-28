<?php

namespace App\Domains\Shared\Services;

use App\Models\ServiceAdd;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServiceAddServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): ServiceAdd;
    public function update(int $id, array $data): ServiceAdd;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
