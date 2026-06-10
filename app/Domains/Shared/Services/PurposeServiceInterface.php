<?php

namespace App\Domains\Shared\Services;

use App\Models\Purpose;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PurposeServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Purpose;
    public function update(int $id, array $data): Purpose;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
