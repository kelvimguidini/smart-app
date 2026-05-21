<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Purpose;

class EloquentPurposeRepository extends EloquentBaseRepository implements PurposeRepositoryInterface
{
    public function __construct(Purpose $model)
    {
        parent::__construct($model);
    }
}
