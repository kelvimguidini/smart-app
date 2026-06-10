<?php

namespace App\Domains\Shared\Services;

interface CityServiceInterface
{
    public function search(string $term = '');
}
