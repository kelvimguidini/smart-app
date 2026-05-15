<?php

namespace App\Domains\Halls\Repositories;

use App\Models\EventHallOpt;

class EloquentEventHallOptRepository implements EventHallOptRepositoryInterface
{
    public function find(int $id): ?EventHallOpt
    {
        return EventHallOpt::find($id);
    }

    public function findWithDetails(int $id): ?EventHallOpt
    {
        return EventHallOpt::with('event_hall')->find($id);
    }

    public function create(array $data): EventHallOpt
    {
        return EventHallOpt::create($data);
    }

    public function update(int $id, array $data): EventHallOpt
    {
        $opt = EventHallOpt::findOrFail($id);
        $opt->update($data);
        return $opt;
    }

    public function delete(int $id): bool
    {
        $opt = EventHallOpt::findOrFail($id);
        return $opt->delete();
    }
}
