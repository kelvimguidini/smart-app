<?php

namespace App\Domains\Shared\Services;

use App\Models\Measure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MeasureServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Measure;
    public function update(int $id, array $data): Measure;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
