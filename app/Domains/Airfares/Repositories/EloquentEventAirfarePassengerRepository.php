<?php

namespace App\Domains\Airfares\Repositories;

use App\Models\EventAirfarePassenger;

class EloquentEventAirfarePassengerRepository implements EventAirfarePassengerRepositoryInterface
{
    public function find(int $id): ?EventAirfarePassenger
    {
        return EventAirfarePassenger::find($id);
    }

    public function findWithDetails(int $id): ?EventAirfarePassenger
    {
        return EventAirfarePassenger::with('event_airfare')->find($id);
    }

    public function create(array $data): EventAirfarePassenger
    {
        return EventAirfarePassenger::create($data);
    }

    public function update(int $id, array $data): EventAirfarePassenger
    {
        $passenger = EventAirfarePassenger::findOrFail($id);
        $passenger->update($data);
        return $passenger;
    }

    public function delete(int $id): bool
    {
        $passenger = EventAirfarePassenger::findOrFail($id);
        return $passenger->delete();
    }
}
