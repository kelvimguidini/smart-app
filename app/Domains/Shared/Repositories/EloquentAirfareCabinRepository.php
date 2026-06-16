<?php

namespace App\Domains\Shared\Repositories;

use App\Models\AirfareCabin;

class EloquentAirfareCabinRepository extends EloquentBaseRepository implements AirfareCabinRepositoryInterface
{
    public function __construct(AirfareCabin $model)
    {
        parent::__construct($model);
    }
}
