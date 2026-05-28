<?php

namespace App\Domains\Shared\Repositories;

use App\Models\ServiceAdd;

class EloquentServiceAddRepository extends EloquentBaseRepository implements ServiceAddRepositoryInterface
{
    public function __construct(ServiceAdd $model)
    {
        parent::__construct($model);
    }
}
