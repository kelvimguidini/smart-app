<?php

namespace App\Domains\FoodBeverage\Repositories;

use App\Models\EventABOpt;

class EloquentEventABOptRepository implements EventABOptRepositoryInterface
{
    public function find(int $id): ?EventABOpt
    {
        return EventABOpt::find($id);
    }

    public function findWithDetails(int $id): ?EventABOpt
    {
        return EventABOpt::with('event_ab')->find($id);
    }

    public function create(array $data): EventABOpt
    {
        return EventABOpt::create($data);
    }

    public function update(int $id, array $data): EventABOpt
    {
        $opt = EventABOpt::findOrFail($id);
        $opt->update($data);
        return $opt;
    }

    public function delete(int $id): bool
    {
        $opt = EventABOpt::findOrFail($id);
        return $opt->delete();
    }
}
