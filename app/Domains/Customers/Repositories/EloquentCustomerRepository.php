<?php

namespace App\Domains\Customers\Repositories;

use App\Models\Customer;
use App\Models\CRD;
use Illuminate\Database\Eloquent\Collection;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function all(): Collection
    {
        return Customer::all();
    }

    public function allWithInactive(): Collection
    {
        return Customer::withoutGlobalScope('active')->get();
    }

    public function find(int $id): ?Customer
    {
        return Customer::withoutGlobalScope('active')->find($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = Customer::withoutGlobalScope('active')->findOrFail($id);
        $customer->update($data);
        return $customer;
    }

    public function delete(int $id): bool
    {
        $customer = Customer::withoutGlobalScope('active')->findOrFail($id);
        return $customer->delete();
    }

    public function activate(int $id): bool
    {
        $customer = Customer::withoutGlobalScope('active')->findOrFail($id);
        return $customer->activate();
    }

    public function deactivate(int $id): bool
    {
        $customer = Customer::withoutGlobalScope('active')->findOrFail($id);
        return $customer->deactivate();
    }

    public function allCrdsWithCustomer(): Collection
    {
        return CRD::with('customer')->get();
    }

    public function allCrdsWithInactive(): Collection
    {
        return CRD::with('customer')->withoutGlobalScope('active')->get();
    }

    public function saveCrd(array $data, ?int $id = null): \App\Models\CRD
    {
        $item = $id ? CRD::withoutGlobalScope('active')->findOrFail($id) : new CRD();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteCrd(int $id): bool
    {
        return CRD::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateCrd(int $id): bool
    {
        return CRD::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateCrd(int $id): bool
    {
        return CRD::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    public function allRequestersWithInactive(): Collection
    {
        return \App\Models\CustomerRequester::with('customer')->withoutGlobalScope('active')->get();
    }

    public function saveRequester(array $data, ?int $id = null): \App\Models\CustomerRequester
    {
        $item = $id ? \App\Models\CustomerRequester::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\CustomerRequester();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteRequester(int $id): bool
    {
        return \App\Models\CustomerRequester::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateRequester(int $id): bool
    {
        return \App\Models\CustomerRequester::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateRequester(int $id): bool
    {
        return \App\Models\CustomerRequester::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    public function allSectorsWithInactive(): Collection
    {
        return \App\Models\CustomerSector::with('customer')->withoutGlobalScope('active')->get();
    }

    public function saveSector(array $data, ?int $id = null): \App\Models\CustomerSector
    {
        $item = $id ? \App\Models\CustomerSector::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\CustomerSector();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteSector(int $id): bool
    {
        return \App\Models\CustomerSector::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateSector(int $id): bool
    {
        return \App\Models\CustomerSector::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateSector(int $id): bool
    {
        return \App\Models\CustomerSector::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    public function allCostCentersWithInactive(): Collection
    {
        return \App\Models\CustomerCostCenter::with('customer')->withoutGlobalScope('active')->get();
    }

    public function saveCostCenter(array $data, ?int $id = null): \App\Models\CustomerCostCenter
    {
        $item = $id ? \App\Models\CustomerCostCenter::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\CustomerCostCenter();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteCostCenter(int $id): bool
    {
        return \App\Models\CustomerCostCenter::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateCostCenter(int $id): bool
    {
        return \App\Models\CustomerCostCenter::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateCostCenter(int $id): bool
    {
        return \App\Models\CustomerCostCenter::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }
}
