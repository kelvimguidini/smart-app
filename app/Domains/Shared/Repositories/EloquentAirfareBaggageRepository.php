<?php

namespace App\Domains\Shared\Repositories;

use App\Models\AirfareBaggage;

class EloquentAirfareBaggageRepository extends EloquentBaseRepository implements AirfareBaggageRepositoryInterface
{
    public function __construct(AirfareBaggage $model)
    {
        parent::__construct($model);
    }
}
