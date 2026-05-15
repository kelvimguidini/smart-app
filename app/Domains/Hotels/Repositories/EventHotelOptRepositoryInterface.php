<?php

namespace App\Domains\Hotels\Repositories;

use App\Models\EventHotelOpt;

interface EventHotelOptRepositoryInterface
{
    public function find(int $id): ?EventHotelOpt;
    public function findWithHotel(int $id): ?EventHotelOpt;
}
