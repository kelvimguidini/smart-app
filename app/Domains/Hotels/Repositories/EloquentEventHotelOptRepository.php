<?php

namespace App\Domains\Hotels\Repositories;

use App\Models\EventHotelOpt;

class EloquentEventHotelOptRepository implements EventHotelOptRepositoryInterface
{
    public function find(int $id): ?EventHotelOpt
    {
        return EventHotelOpt::find($id);
    }

    public function findWithHotel(int $id): ?EventHotelOpt
    {
        return EventHotelOpt::with('event_hotel')->find($id);
    }
}
