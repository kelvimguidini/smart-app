<?php

namespace App\Domains\Halls\Repositories;

use App\Models\EventHallOpt;

interface EventHallOptRepositoryInterface
{
    public function find(int $id): ?EventHallOpt;
    public function findWithDetails(int $id): ?EventHallOpt;
    public function create(array $data): EventHallOpt;
    public function update(int $id, array $data): EventHallOpt;
    public function delete(int $id): bool;
}
