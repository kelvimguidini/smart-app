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

    public function search(string $term = '')
    {
        $query = City::withoutGlobalScope('active');
        
        if (!empty($term)) {
            $query->where('name', 'like', '%' . $term . '%')
                  ->orWhere('states', 'like', '%' . $term . '%');
        }

        return $query->select('id', 'name', 'states', 'country')
                     ->orderBy('name', 'asc')
                     ->limit(20)
                     ->get();
    }
}
