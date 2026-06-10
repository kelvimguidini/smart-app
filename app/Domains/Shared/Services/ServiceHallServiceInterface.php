<?php

namespace App\Domains\Shared\Services;

use App\Models\ServiceHall;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServiceHallServiceInterface
{
    public function getPaginatedServiceHalls(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function saveServiceHall(array $data): ServiceHall;
    public function deleteServiceHall(int $id): bool;
    public function activateServiceHall(int $id): bool;
    public function deactivateServiceHall(int $id): bool;
}
