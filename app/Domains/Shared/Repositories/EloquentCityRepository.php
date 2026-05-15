<?php

namespace App\Domains\Shared\Repositories;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;

class EloquentCityRepository implements CityRepositoryInterface
{
    public function all(): Collection
    {
        return City::all();
    }
}
