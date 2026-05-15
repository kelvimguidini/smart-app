<?php

namespace App\Domains\Additions\Repositories;

use App\Models\EventAdd;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventAddRepository implements EventAddRepositoryInterface
{
    public function create(array $data): EventAdd
    {
        return EventAdd::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return EventAdd::findOrFail($id)->update($data);
    }

    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool
    {
        return EventAdd::where('event_id', $eventId)
            ->where('add_id', $providerId)
            ->update($data) > 0;
    }

    public function findWithDetails(int $id): ?EventAdd
    {
        return EventAdd::with(['add', 'currency', 'eventAddOpts'])->find($id);
    }

    public function getByEvent(int $eventId): Collection
    {
        return EventAdd::where('event_id', $eventId)->with('add')->get();
    }

    public function getIdsByEvent(int $eventId): array
    {
        return EventAdd::where('event_id', $eventId)->pluck('id')->toArray();
    }

    public function find(int $id): ?EventAdd
    {
        return EventAdd::find($id);
    }

    public function saveEventAdd(array $data, ?int $id = null): EventAdd
    {
        $item = $id ? EventAdd::findOrFail($id) : new EventAdd();
        $item->fill($data);
        $item->save();
        return $item;
    }
}
