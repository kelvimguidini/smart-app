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
            ->where('airfare_id', $providerId)
            ->update($data) > 0;
    }

    public function getByEvent(int $eventId): Collection
    {
        return EventAirfare::with([
            'eventAirfareOpts' => function ($q) {
                $q->orderBy('order', 'asc')->orderBy('id');
            },
            'eventAirfareOpts.outbound_airline',
            'eventAirfareOpts.inbound_airline',
            'eventAirfareOpts.currency',
            'eventAirfareOpts.baggage',
            'eventAirfareOpts.cabin',
            'provider.city',
            'currency',
            'event',
            'passengers' => function ($q) {
                $q->orderBy('name', 'asc');
            }
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
                $q->orderBy('order', 'asc')->orderBy('id');
            },
            'eventAirfareOpts.outbound_airline',
            'eventAirfareOpts.inbound_airline',
            'eventAirfareOpts.currency',
            'eventAirfareOpts.baggage',
            'eventAirfareOpts.cabin',
            'provider.city',
            'currency',
            'event',
            'passengers' => function ($q) {
                $q->orderBy('name', 'asc');
            }
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
