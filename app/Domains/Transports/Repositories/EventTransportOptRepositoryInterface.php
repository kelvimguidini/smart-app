<?php

namespace App\Domains\Transports\Repositories;

use App\Models\EventTransportOpt;

interface EventTransportOptRepositoryInterface
{
    public function find(int $id): ?EventTransportOpt;
    public function findWithDetails(int $id): ?EventTransportOpt;
    public function create(array $data): EventTransportOpt;
    public function update(int $id, array $data): EventTransportOpt;
    public function delete(int $id): bool;
}
