<?php

namespace App\Domains\Additions\Repositories;

use App\Models\EventAddOpt;

interface EventAddOptRepositoryInterface
{
    public function find(int $id): ?EventAddOpt;
    public function findWithDetails(int $id): ?EventAddOpt;
    public function create(array $data): EventAddOpt;
    public function update(int $id, array $data): EventAddOpt;
    public function delete(int $id): bool;
}
