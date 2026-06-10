<?php

namespace App\Domains\Shared\Services;

use App\Models\PurposeHall;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PurposeHallServiceInterface
{
    public function getPaginatedPurposeHalls(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function savePurposeHall(array $data): PurposeHall;
    public function deletePurposeHall(int $id): bool;
    public function activatePurposeHall(int $id): bool;
    public function deactivatePurposeHall(int $id): bool;
}
