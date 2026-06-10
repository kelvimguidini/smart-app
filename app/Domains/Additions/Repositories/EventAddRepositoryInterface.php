<?php

namespace App\Domains\Additions\Repositories;

use App\Models\EventAdd;
use Illuminate\Database\Eloquent\Collection;

interface EventAddRepositoryInterface
{
    public function create(array $data): EventAdd;
    public function update(int $id, array $data): bool;
    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool;
    public function findWithDetails(int $id): ?EventAdd;
    public function getByEvent(int $eventId): Collection;
    public function getIdsByEvent(int $eventId): array;
    public function find(int $id): ?EventAdd;
    public function saveEventAdd(array $data, ?int $id = null): EventAdd;
}
