<?php
 
namespace App\Domains\Shared\Repositories;
 
use App\Models\CarModel;
 
class EloquentCarModelRepository extends EloquentBaseRepository implements CarModelRepositoryInterface
{
    public function __construct(CarModel $model)
    {
        parent::__construct($model);
    }
}
