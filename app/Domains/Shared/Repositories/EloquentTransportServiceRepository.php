<?php

namespace App\Domains\Shared\Repositories;

use App\Models\TransportService;

class EloquentTransportServiceRepository extends EloquentBaseRepository implements TransportServiceRepositoryInterface
{
    public function __construct(TransportService $model)
    {
        parent::__construct($model);
    }
}
