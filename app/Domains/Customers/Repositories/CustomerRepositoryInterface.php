<?php

namespace App\Domains\Customers\Repositories;

use App\Models\Customer;
use App\Models\CRD;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    public function all(): Collection;
    public function allWithInactive(): Collection;
    public function find(int $id): ?Customer;
    public function create(array $data): Customer;
    public function update(int $id, array $data): Customer;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
    public function allCrdsWithCustomer(): Collection;
    public function allCrdsWithInactive(): Collection;
    public function saveCrd(array $data, ?int $id = null): \App\Models\CRD;
    public function deleteCrd(int $id): bool;
    public function activateCrd(int $id): bool;
    public function deactivateCrd(int $id): bool;
}
