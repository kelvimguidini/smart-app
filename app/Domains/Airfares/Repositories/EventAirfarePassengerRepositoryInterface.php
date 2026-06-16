<?php

namespace App\Domains\Airfares\Repositories;

use App\Models\EventAirfarePassenger;

interface EventAirfarePassengerRepositoryInterface
{
    public function find(int $id): ?EventAirfarePassenger;
    public function findWithDetails(int $id): ?EventAirfarePassenger;
    public function create(array $data): EventAirfarePassenger;
    public function update(int $id, array $data): EventAirfarePassenger;
    public function delete(int $id): bool;
}
