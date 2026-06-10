<?php
 
namespace App\Domains\Shared\Repositories;
 
use App\Models\Vehicle;
 
class EloquentVehicleRepository extends EloquentBaseRepository implements VehicleRepositoryInterface
{
    public function __construct(Vehicle $model)
    {
        parent::__construct($model);
    }
}
