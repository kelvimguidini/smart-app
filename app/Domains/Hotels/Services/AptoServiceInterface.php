<?php

namespace App\Domains\Hotels\Services;

use App\Models\Apto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AptoServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Apto;
    public function update(int $id, array $data): Apto;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
