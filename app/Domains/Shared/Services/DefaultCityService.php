<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\CityRepositoryInterface;

class DefaultCityService implements CityServiceInterface
{
    protected $cityRepository;

    public function __construct(CityRepositoryInterface $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    public function search(string $term = '')
    {
        return $this->cityRepository->search($term);
    }
}
