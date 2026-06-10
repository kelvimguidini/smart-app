<?php

namespace App\Domains\Shared\Repositories;

use App\Models\ServiceType;

class EloquentServiceTypeRepository extends EloquentBaseRepository implements ServiceTypeRepositoryInterface
{
    public function __construct(ServiceType $model)
    {
        parent::__construct($model);
    }
}
