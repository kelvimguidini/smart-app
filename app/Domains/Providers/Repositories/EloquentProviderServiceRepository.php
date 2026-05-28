<?php

namespace App\Domains\Providers\Repositories;

use App\Domains\Shared\Repositories\EloquentBaseRepository;
use App\Models\ProviderServices;

class EloquentProviderServiceRepository extends EloquentBaseRepository implements ProviderServiceRepositoryInterface
{
    public function __construct(ProviderServices $model)
    {
        parent::__construct($model);
    }
}
