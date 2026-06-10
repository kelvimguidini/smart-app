<?php

namespace App\Domains\Shared\Services;

use App\Models\Regime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RegimeServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Regime;
    public function update(int $id, array $data): Regime;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
