<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Local;

class EloquentLocalRepository extends EloquentBaseRepository implements LocalRepositoryInterface
{
    public function __construct(Local $model)
    {
        parent::__construct($model);
    }
}
