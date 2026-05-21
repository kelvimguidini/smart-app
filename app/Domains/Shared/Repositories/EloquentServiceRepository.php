<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Service;

class EloquentServiceRepository extends EloquentBaseRepository implements ServiceRepositoryInterface
{
    public function __construct(Service $model)
    {
        parent::__construct($model);
    }
}
