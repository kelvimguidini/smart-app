<?php

namespace App\Domains\Shared\Repositories;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;

interface CityRepositoryInterface
{
    public function all(): Collection;
}
