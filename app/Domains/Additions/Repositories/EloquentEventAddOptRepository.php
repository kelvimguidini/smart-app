<?php

namespace App\Domains\Additions\Repositories;

use App\Models\EventAddOpt;

class EloquentEventAddOptRepository implements EventAddOptRepositoryInterface
{
    public function find(int $id): ?EventAddOpt
    {
        return EventAddOpt::find($id);
    }

    public function findWithDetails(int $id): ?EventAddOpt
    {
        return EventAddOpt::with('event_add')->find($id);
    }

    public function create(array $data): EventAddOpt
    {
        return EventAddOpt::create($data);
    }

    public function update(int $id, array $data): EventAddOpt
    {
        $opt = EventAddOpt::findOrFail($id);
        $opt->update($data);
        return $opt;
    }

    public function delete(int $id): bool
    {
        $opt = EventAddOpt::findOrFail($id);
        return $opt->delete();
    }
}
