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
    public function allRequestersWithInactive(): Collection;
    public function saveRequester(array $data, ?int $id = null): \App\Models\CustomerRequester;
    public function deleteRequester(int $id): bool;
    public function activateRequester(int $id): bool;
    public function deactivateRequester(int $id): bool;

    public function allSectorsWithInactive(): Collection;
    public function saveSector(array $data, ?int $id = null): \App\Models\CustomerSector;
    public function deleteSector(int $id): bool;
    public function activateSector(int $id): bool;
    public function deactivateSector(int $id): bool;

    public function allCostCentersWithInactive(): Collection;
    public function saveCostCenter(array $data, ?int $id = null): \App\Models\CustomerCostCenter;
    public function deleteCostCenter(int $id): bool;
    public function activateCostCenter(int $id): bool;
    public function deactivateCostCenter(int $id): bool;
}
