<?php

namespace App\Domains\Airfares\Repositories;

use App\Models\EventAirfare;
use Illuminate\Database\Eloquent\Collection;

interface EventAirfareRepositoryInterface
{
    public function create(array $data): EventAirfare;
    public function update(int $id, array $data): bool;
    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool;
    public function getByEvent(int $eventId): Collection;
    public function find(int $id): ?EventAirfare;
    public function findWithDetails(int $id): ?EventAirfare;
    public function saveEventAirfare(array $data, ?int $id = null): EventAirfare;
    public function getIdsByEvent(int $eventId): array;
}
