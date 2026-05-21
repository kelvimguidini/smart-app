<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Regime;

class EloquentRegimeRepository extends EloquentBaseRepository implements RegimeRepositoryInterface
{
    public function __construct(Regime $model)
    {
        parent::__construct($model);
    }
}
