<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Frequency;

class EloquentFrequencyRepository extends EloquentBaseRepository implements FrequencyRepositoryInterface
{
    public function __construct(Frequency $model)
    {
        parent::__construct($model);
    }
}
