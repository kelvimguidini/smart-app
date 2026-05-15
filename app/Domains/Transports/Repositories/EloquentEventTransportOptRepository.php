<?php

namespace App\Domains\Transports\Repositories;

use App\Models\EventTransportOpt;

class EloquentEventTransportOptRepository implements EventTransportOptRepositoryInterface
{
    public function find(int $id): ?EventTransportOpt
    {
        return EventTransportOpt::find($id);
    }

    public function findWithDetails(int $id): ?EventTransportOpt
    {
        return EventTransportOpt::with('event_transport')->find($id);
    }

    public function create(array $data): EventTransportOpt
    {
        return EventTransportOpt::create($data);
    }

    public function update(int $id, array $data): EventTransportOpt
    {
        $opt = EventTransportOpt::findOrFail($id);
        $opt->update($data);
        return $opt;
    }

    public function delete(int $id): bool
    {
        $opt = EventTransportOpt::findOrFail($id);
        return $opt->delete();
    }
}
