<?php

namespace App\Domains\Shared\Services;

use App\Models\AirfareAirline;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AirfareAirlineServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): AirfareAirline;
    public function update(int $id, array $data): AirfareAirline;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
