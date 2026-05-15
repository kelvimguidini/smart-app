<?php

namespace App\Domains\Hotels\Repositories;

use App\Models\EventHotel;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventHotelRepository implements EventHotelRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): EventHotel
    {
        $eventHotel = EventHotel::findOrFail($id);
        $eventHotel->update($data);
        return $eventHotel;
    }

    /**
     * @inheritDoc
     */
    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool
    {
        return EventHotel::where('event_id', $eventId)
            ->where('provider_id', $providerId)
            ->update($data) > 0;
    }

    /**
     * @inheritDoc
     */
    public function findWithDetails(int $id): ?EventHotel
    {
        return EventHotel::with([
            'eventHotelsOpt' => function ($q) {
                $q->orderBy('order', 'asc')->orderby('in');
            },
            'hotel.city',
            'currency',
            'event',
            'status_his'
        ])->find($id);
    }

    /**
     * @inheritDoc
     */
    public function getByEvent(int $eventId): Collection
    {
        return EventHotel::with([
            'eventHotelsOpt' => function ($q) {
                $q->orderBy('order', 'asc')->orderby('in');
            },
            'eventHotelsOpt.broker',
            'eventHotelsOpt.regime',
            'eventHotelsOpt.purpose',
            'eventHotelsOpt.apto_hotel',
            'eventHotelsOpt.category_hotel',
            'hotel.city',
            'currency',
            'event'
        ])->where('event_id', '=', $eventId)->get();
    }
}
