<?php

namespace App\Domains\Shared\Repositories;

use App\Models\AirfareAirline;

class EloquentAirfareAirlineRepository extends EloquentBaseRepository implements AirfareAirlineRepositoryInterface
{
    public function __construct(AirfareAirline $model)
    {
        parent::__construct($model);
    }
}
