<?php

namespace App\Domains\Hotels\Repositories;

use App\Models\Apto;
use App\Domains\Shared\Repositories\EloquentBaseRepository;

class EloquentAptoRepository extends EloquentBaseRepository implements AptoRepositoryInterface
{
    public function __construct(Apto $model)
    {
        parent::__construct($model);
    }
}
