<?php

namespace App\Domains\Shared\Services;

use App\Models\Local;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LocalServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Local;
    public function update(int $id, array $data): Local;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
