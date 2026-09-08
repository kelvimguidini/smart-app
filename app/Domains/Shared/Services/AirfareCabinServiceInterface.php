<?php

namespace App\Domains\Shared\Services;

use App\Models\AirfareCabin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AirfareCabinServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): AirfareCabin;
    public function update(int $id, array $data): AirfareCabin;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
