<?php

namespace App\Domains\Transports\Repositories;

use App\Models\EventTransport;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventTransportRepository implements EventTransportRepositoryInterface
{
    public function create(array $data): EventTransport
    {
        return EventTransport::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return EventTransport::findOrFail($id)->update($data);
    }

    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool
    {
        return EventTransport::where('event_id', $eventId)
            ->where('transport_id', $providerId)
            ->update($data) > 0;
    }

    public function getByEvent(int $eventId): Collection
    {
        return EventTransport::where('event_id', $eventId)->with('transport')->get();
    }

    public function find(int $id): ?EventTransport
    {
        return EventTransport::find($id);
    }

    public function saveEventTransport(array $data, ?int $id = null): EventTransport
    {
        $item = $id ? EventTransport::findOrFail($id) : new EventTransport();
        $item->fill($data);
        $item->save();
        return $item;
    }
}
