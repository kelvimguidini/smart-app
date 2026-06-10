<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Measure;

class EloquentMeasureRepository extends EloquentBaseRepository implements MeasureRepositoryInterface
{
    public function __construct(Measure $model)
    {
        parent::__construct($model);
    }
}
