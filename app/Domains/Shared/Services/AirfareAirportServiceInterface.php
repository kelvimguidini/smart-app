<?php

namespace App\Domains\Shared\Services;

use App\Models\AirfareAirport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AirfareAirportServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): AirfareAirport;
    public function update(int $id, array $data): AirfareAirport;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
