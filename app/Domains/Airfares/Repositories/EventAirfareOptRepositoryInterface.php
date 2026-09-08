<?php

namespace App\Domains\Airfares\Repositories;

use App\Models\EventAirfareOpt;

interface EventAirfareOptRepositoryInterface
{
    public function find(int $id): ?EventAirfareOpt;
    public function findWithDetails(int $id): ?EventAirfareOpt;
    public function create(array $data): EventAirfareOpt;
    public function update(int $id, array $data): EventAirfareOpt;
    public function delete(int $id): bool;
}
