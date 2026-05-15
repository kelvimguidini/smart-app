<?php

namespace App\Domains\FoodBeverage\Repositories;

use App\Models\EventAB;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventABRepository implements EventABRepositoryInterface
{
    public function update(int $id, array $data): EventAB
    {
        $eventAb = EventAB::findOrFail($id);
        $eventAb->update($data);
        return $eventAb;
    }

    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool
    {
        return (bool) EventAB::where('event_id', $eventId)
            ->where('provider_id', $providerId)
            ->update($data);
    }

    public function findWithDetails(int $id): ?EventAB
    {
        return EventAB::with([
            'eventAbOpts' => function ($q) {
                $q->orderBy('order', 'asc')->orderby('in');
            },
            'ab.city',
            'currency',
            'event',
            'status_his'
        ])->find($id);
    }

    public function getByEvent(int $eventId): Collection
    {
        return EventAB::with([
            'eventAbOpts' => function ($q) {
                $q->orderBy('order', 'asc')->orderby('in');
            },
            'eventAbOpts.broker',
            'eventAbOpts.service',
            'eventAbOpts.service_type',
            'eventAbOpts.local',
            'ab.city',
            'currency',
            'event'
        ])->where('event_id', '=', $eventId)->get();
    }

    public function getIdsByEvent(int $eventId): array
    {
        return EventAB::where('event_id', $eventId)->pluck('id')->toArray();
    }
}
