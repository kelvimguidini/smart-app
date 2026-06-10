<?php

namespace App\Domains\Shared\Repositories;

use App\Models\ServiceHall;

class EloquentServiceHallRepository extends EloquentBaseRepository implements ServiceHallRepositoryInterface
{
    public function __construct(ServiceHall $model)
    {
        parent::__construct($model);
    }
}
