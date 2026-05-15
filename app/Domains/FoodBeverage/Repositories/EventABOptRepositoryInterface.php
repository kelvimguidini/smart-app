<?php

namespace App\Domains\FoodBeverage\Repositories;

use App\Models\EventABOpt;

interface EventABOptRepositoryInterface
{
    public function find(int $id): ?EventABOpt;
    public function findWithDetails(int $id): ?EventABOpt;
    public function create(array $data): EventABOpt;
    public function update(int $id, array $data): EventABOpt;
    public function delete(int $id): bool;
}
