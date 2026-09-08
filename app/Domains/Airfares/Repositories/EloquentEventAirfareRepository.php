<?php

namespace App\Domains\Airfares\Repositories;

use App\Models\EventAirfare;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventAirfareRepository implements EventAirfareRepositoryInterface
{
    public function create(array $data): EventAirfare
    {
        return EventAirfare::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return EventAirfare::findOrFail($id)->update($data);
    }

    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool
    {
        return EventAirfare::where('event_id', $eventId)
            ->where(function ($q) use ($providerId) {
                $q->where('airline_id', $providerId);
                if (\Illuminate\Support\Facades\Schema::hasColumn('event_airfare', 'airfare_id')) {
                    $q->orWhere('airfare_id', $providerId);
                }
            })
            ->update($data) > 0;
    }

    public function getByEvent(int $eventId): Collection
    {
        return EventAirfare::with([
            'eventAirfareOpts' => function ($q) {
                $q->orderBy('id', 'asc');
            },
            'eventAirfareOpts.outbound_airline',
            'provider',
            'airline',
            'currency',
            'event'
        ])->where('event_id', '=', $eventId)->get();
    }

    public function find(int $id): ?EventAirfare
    {
        return EventAirfare::find($id);
    }

    public function findWithDetails(int $id): ?EventAirfare
    {
        return EventAirfare::with([
            'eventAirfareOpts' => function ($q) {
                $q->orderBy('id', 'asc');
            },
            'eventAirfareOpts.outbound_airline',
            'provider',
            'airline',
            'currency',
            'event'
        ])->find($id);
    }

    public function saveEventAirfare(array $data, ?int $id = null): EventAirfare
    {
        $item = $id ? EventAirfare::findOrFail($id) : new EventAirfare();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function getIdsByEvent(int $eventId): array
    {
        return EventAirfare::where('event_id', $eventId)->pluck('id')->toArray();
    }
}
