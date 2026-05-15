<?php

namespace App\Domains\Transports\Repositories;

use App\Models\EventTransport;
use Illuminate\Database\Eloquent\Collection;

interface EventTransportRepositoryInterface
{
    public function create(array $data): EventTransport;
    public function update(int $id, array $data): bool;
    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool;
    public function getByEvent(int $eventId): Collection;
    public function find(int $id): ?EventTransport;
    public function saveEventTransport(array $data, ?int $id = null): EventTransport;
}
