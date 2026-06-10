<?php

namespace App\Domains\Shared\Repositories;

use App\Models\PurposeHall;

class EloquentPurposeHallRepository extends EloquentBaseRepository implements PurposeHallRepositoryInterface
{
    public function __construct(PurposeHall $model)
    {
        parent::__construct($model);
    }
}
