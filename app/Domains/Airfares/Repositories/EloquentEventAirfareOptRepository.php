<?php

namespace App\Domains\Airfares\Repositories;

use App\Models\EventAirfareOpt;

class EloquentEventAirfareOptRepository implements EventAirfareOptRepositoryInterface
{
    public function find(int $id): ?EventAirfareOpt
    {
        return EventAirfareOpt::find($id);
    }

    public function findWithDetails(int $id): ?EventAirfareOpt
    {
        return EventAirfareOpt::with('event_airfare')->find($id);
    }

    public function create(array $data): EventAirfareOpt
    {
        return EventAirfareOpt::create($data);
    }

    public function update(int $id, array $data): EventAirfareOpt
    {
        $opt = EventAirfareOpt::findOrFail($id);
        $opt->update($data);
        return $opt;
    }

    public function delete(int $id): bool
    {
        $opt = EventAirfareOpt::findOrFail($id);
        return $opt->delete();
    }
}
