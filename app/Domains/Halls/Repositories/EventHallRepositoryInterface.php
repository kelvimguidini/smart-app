<?php

namespace App\Domains\Halls\Repositories;

use App\Models\EventHall;
use Illuminate\Database\Eloquent\Collection;

interface EventHallRepositoryInterface
{
    public function create(array $data): EventHall;
    public function update(int $id, array $data): EventHall;
    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool;
    public function findWithDetails(int $id): ?EventHall;
    public function getByEvent(int $eventId): Collection;
    public function getIdsByEvent(int $eventId): array;
}
