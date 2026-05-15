<?php

namespace App\Domains\Halls\Repositories;

use App\Models\EventHall;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventHallRepository implements EventHallRepositoryInterface
{
    public function update(int $id, array $data): \App\Models\EventHall
    {
        $eventHall = EventHall::findOrFail($id);
        $eventHall->update($data);
        return $eventHall;
    }

    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool
    {
        return EventHall::where('event_id', $eventId)
            ->where('provider_id', $providerId)
            ->update($data) > 0;
    }

    public function findWithDetails(int $id): ?EventHall
    {
        return EventHall::with([
            'eventHallOpts' => function ($q) {
                $q->orderBy('order', 'asc')->orderby('in');
            },
            'hall.city',
            'currency',
            'event',
            'status_his'
        ])->find($id);
    }

    public function getByEvent(int $eventId): Collection
    {
        return EventHall::with([
            'eventHallOpts' => function ($q) {
                $q->orderBy('order', 'asc')->orderby('in');
            },
            'eventHallOpts.broker',
            'eventHallOpts.service',
            'eventHallOpts.purpose',
            'hall.city',
            'currency',
            'event'
        ])->where('event_id', '=', $eventId)->get();
    }

    public function getIdsByEvent(int $eventId): array
    {
        return EventHall::where('event_id', $eventId)->pluck('id')->toArray();
    }
}
