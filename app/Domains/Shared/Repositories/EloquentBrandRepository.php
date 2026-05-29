<?php
 
namespace App\Domains\Shared\Repositories;
 
use App\Models\Brand;
 
class EloquentBrandRepository extends EloquentBaseRepository implements BrandRepositoryInterface
{
    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }
}
