<?php

namespace App\Domains\FoodBeverage\Repositories;

use App\Models\EventAB;
use Illuminate\Database\Eloquent\Collection;

interface EventABRepositoryInterface
{
    public function create(array $data): EventAB;
    public function update(int $id, array $data): EventAB;
    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool;
    public function findWithDetails(int $id): ?EventAB;
    public function getByEvent(int $eventId): Collection;
    public function getIdsByEvent(int $eventId): array;
}
