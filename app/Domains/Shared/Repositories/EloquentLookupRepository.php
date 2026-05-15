<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Broker;
use App\Models\Currency;
use App\Models\Regime;
use App\Models\Purpose;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Local;
use App\Models\ServiceHall;
use App\Models\PurposeHall;
use App\Models\ServiceAdd;
use App\Models\Frequency;
use App\Models\Measure;
use App\Models\BrokerTransport;
use App\Models\Vehicle;
use App\Models\CarModel;
use App\Models\TransportService;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Apto;
use Illuminate\Database\Eloquent\Collection;

class EloquentLookupRepository implements LookupRepositoryInterface
{
    public function getAllBrokers(): Collection { return Broker::all(); }
    public function getAllCurrencies(): Collection { return Currency::all(); }
    public function getAllRegimes(): Collection { return Regime::all(); }
    public function getAllPurposes(): Collection { return Purpose::all(); }
    public function getAllServices(): Collection { return Service::all(); }
    public function getAllServiceTypes(): Collection { return ServiceType::all(); }
    public function getAllLocals(): Collection { return Local::all(); }
    public function getAllServiceHalls(): Collection { return ServiceHall::all(); }
    public function getAllPurposeHalls(): Collection { return PurposeHall::all(); }
    public function getAllServiceAdds(): Collection { return ServiceAdd::all(); }
    public function getAllFrequencies(): Collection { return Frequency::all(); }
    public function getAllMeasures(): Collection { return Measure::all(); }
    public function getAllBrokerTransports(): Collection { return BrokerTransport::all(); }
    public function getAllVehicles(): Collection { return Vehicle::all(); }
    public function getAllCarModels(): Collection { return CarModel::all(); }
    public function getAllTransportServices(): Collection { return TransportService::all(); }
    public function getAllBrands(): Collection { return Brand::all(); }
    public function getAllCategories(): Collection { return Category::all(); }
    public function getAllAptos(): Collection
    {
        return \App\Models\AptoHotel::all();
    }

    // Brokers
    public function getBrokersWithInactive(): Collection
    {
        return \App\Models\Broker::withoutGlobalScope('active')->get();
    }

    public function saveBroker(array $data, ?int $id = null): \App\Models\Broker
    {
        $broker = $id ? \App\Models\Broker::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Broker();
        $broker->fill($data);
        $broker->save();
        return $broker;
    }

    public function deleteBroker(int $id): bool
    {
        return \App\Models\Broker::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateBroker(int $id): bool
    {
        return \App\Models\Broker::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateBroker(int $id): bool
    {
        return \App\Models\Broker::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    // Cities
    public function getCitiesWithInactive(): Collection
    {
        return \App\Models\City::withoutGlobalScope('active')->get();
    }

    public function searchCities(string $term): Collection
    {
        return \App\Models\City::where('name', 'LIKE', '%' . $term . '%')
            ->orWhere('states', 'LIKE', '%' . $term . '%')
            ->select('id', 'name', 'states', 'country')
            ->get();
    }

    public function saveCity(array $data, ?int $id = null): \App\Models\City
    {
        $city = $id ? \App\Models\City::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\City();
        $city->fill($data);
        $city->save();
        return $city;
    }

    public function deleteCity(int $id): bool
    {
        return \App\Models\City::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateCity(int $id): bool
    {
        return \App\Models\City::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateCity(int $id): bool
    {
        return \App\Models\City::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    // Currencies
    public function getCurrenciesWithInactive(): Collection
    {
        return \App\Models\Currency::withoutGlobalScope('active')->get();
    }

    public function saveCurrency(array $data, ?int $id = null): \App\Models\Currency
    {
        $currency = $id ? \App\Models\Currency::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Currency();
        $currency->fill($data);
        $currency->save();
        return $currency;
    }

    public function deleteCurrency(int $id): bool
    {
        return \App\Models\Currency::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateCurrency(int $id): bool
    {
        return \App\Models\Currency::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateCurrency(int $id): bool
    {
        return \App\Models\Currency::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    // Brands
    public function getBrandsWithInactive(): Collection
    {
        return \App\Models\Brand::withoutGlobalScope('active')->get();
    }

    public function saveBrand(array $data, ?int $id = null): \App\Models\Brand
    {
        $item = $id ? \App\Models\Brand::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Brand();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteBrand(int $id): bool
    {
        return \App\Models\Brand::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateBrand(int $id): bool
    {
        return \App\Models\Brand::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateBrand(int $id): bool
    {
        return \App\Models\Brand::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    // CarModels
    public function getCarModelsWithInactive(): Collection
    {
        return \App\Models\CarModel::withoutGlobalScope('active')->get();
    }

    public function saveCarModel(array $data, ?int $id = null): \App\Models\CarModel
    {
        $item = $id ? \App\Models\CarModel::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\CarModel();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteCarModel(int $id): bool
    {
        return \App\Models\CarModel::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateCarModel(int $id): bool
    {
        return \App\Models\CarModel::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateCarModel(int $id): bool
    {
        return \App\Models\CarModel::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    // Vehicles
    public function getVehiclesWithInactive(): Collection
    {
        return \App\Models\Vehicle::withoutGlobalScope('active')->get();
    }

    public function saveVehicle(array $data, ?int $id = null): \App\Models\Vehicle
    {
        $item = $id ? \App\Models\Vehicle::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Vehicle();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteVehicle(int $id): bool
    {
        return \App\Models\Vehicle::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateVehicle(int $id): bool
    {
        return \App\Models\Vehicle::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateVehicle(int $id): bool
    {
        return \App\Models\Vehicle::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    // TransportServices
    public function getTransportServicesWithInactive(): Collection
    {
        return \App\Models\TransportService::withoutGlobalScope('active')->get();
    }

    public function saveTransportService(array $data, ?int $id = null): \App\Models\TransportService
    {
        $item = $id ? \App\Models\TransportService::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\TransportService();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteTransportService(int $id): bool
    {
        return \App\Models\TransportService::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateTransportService(int $id): bool
    {
        return \App\Models\TransportService::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateTransportService(int $id): bool
    {
        return \App\Models\TransportService::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    // BrokerTransports
    public function getBrokerTransportsWithInactive(): Collection
    {
        return \App\Models\BrokerTransport::with('city')->withoutGlobalScope('active')->get();
    }

    public function saveBrokerTransport(array $data, ?int $id = null): \App\Models\BrokerTransport
    {
        $item = $id ? \App\Models\BrokerTransport::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\BrokerTransport();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function deleteBrokerTransport(int $id): bool
    {
        return \App\Models\BrokerTransport::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activateBrokerTransport(int $id): bool
    {
        return \App\Models\BrokerTransport::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivateBrokerTransport(int $id): bool
    {
        return \App\Models\BrokerTransport::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    // Services
    public function getServicesWithInactive(): Collection { return \App\Models\Service::withoutGlobalScope('active')->get(); }
    public function saveService(array $data, ?int $id = null): \App\Models\Service
    {
        $item = $id ? \App\Models\Service::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Service();
        $item->fill($data); $item->save(); return $item;
    }
    public function deleteService(int $id): bool { return \App\Models\Service::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activateService(int $id): bool { return \App\Models\Service::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivateService(int $id): bool { return \App\Models\Service::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }

    // ServiceTypes
    public function getServiceTypesWithInactive(): Collection { return \App\Models\ServiceType::withoutGlobalScope('active')->get(); }
    public function saveServiceType(array $data, ?int $id = null): \App\Models\ServiceType
    {
        $item = $id ? \App\Models\ServiceType::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\ServiceType();
        $item->fill($data); $item->save(); return $item;
    }
    public function deleteServiceType(int $id): bool { return \App\Models\ServiceType::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activateServiceType(int $id): bool { return \App\Models\ServiceType::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivateServiceType(int $id): bool { return \App\Models\ServiceType::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }

    // ServiceHalls
    public function getServiceHallsWithInactive(): Collection { return \App\Models\ServiceHall::withoutGlobalScope('active')->get(); }
    public function saveServiceHall(array $data, ?int $id = null): \App\Models\ServiceHall
    {
        $item = $id ? \App\Models\ServiceHall::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\ServiceHall();
        $item->fill($data); $item->save(); return $item;
    }
    public function deleteServiceHall(int $id): bool { return \App\Models\ServiceHall::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activateServiceHall(int $id): bool { return \App\Models\ServiceHall::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivateServiceHall(int $id): bool { return \App\Models\ServiceHall::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }

    // ServiceAdds
    public function getServiceAddsWithInactive(): Collection { return \App\Models\ServiceAdd::withoutGlobalScope('active')->get(); }
    public function saveServiceAdd(array $data, ?int $id = null): \App\Models\ServiceAdd
    {
        $item = $id ? \App\Models\ServiceAdd::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\ServiceAdd();
        $item->fill($data); $item->save(); return $item;
    }
    public function deleteServiceAdd(int $id): bool { return \App\Models\ServiceAdd::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activateServiceAdd(int $id): bool { return \App\Models\ServiceAdd::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivateServiceAdd(int $id): bool { return \App\Models\ServiceAdd::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }

    // Measures
    public function getMeasuresWithInactive(): Collection { return \App\Models\Measure::withoutGlobalScope('active')->get(); }
    public function saveMeasure(array $data, ?int $id = null): \App\Models\Measure
    {
        $item = $id ? \App\Models\Measure::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Measure();
        $item->fill($data); $item->save(); return $item;
    }
    public function deleteMeasure(int $id): bool { return \App\Models\Measure::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activateMeasure(int $id): bool { return \App\Models\Measure::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivateMeasure(int $id): bool { return \App\Models\Measure::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }

    // Frequencies
    public function getFrequenciesWithInactive(): Collection { return \App\Models\Frequency::withoutGlobalScope('active')->get(); }
    public function saveFrequency(array $data, ?int $id = null): \App\Models\Frequency
    {
        $item = $id ? \App\Models\Frequency::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Frequency();
        $item->fill($data); $item->save(); return $item;
    }
    public function deleteFrequency(int $id): bool { return \App\Models\Frequency::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activateFrequency(int $id): bool { return \App\Models\Frequency::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivateFrequency(int $id): bool { return \App\Models\Frequency::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }

    // Locals
    public function getLocalsWithInactive(): Collection { return \App\Models\Local::withoutGlobalScope('active')->get(); }
    public function saveLocal(array $data, ?int $id = null): \App\Models\Local
    {
        $item = $id ? \App\Models\Local::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Local();
        $item->fill($data); $item->save(); return $item;
    }
    public function deleteLocal(int $id): bool { return \App\Models\Local::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activateLocal(int $id): bool { return \App\Models\Local::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivateLocal(int $id): bool { return \App\Models\Local::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }

    // Purposes
    public function getPurposesWithInactive(): Collection { return \App\Models\Purpose::withoutGlobalScope('active')->get(); }
    public function savePurpose(array $data, ?int $id = null): \App\Models\Purpose
    {
        $item = $id ? \App\Models\Purpose::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\Purpose();
        $item->fill($data); $item->save(); return $item;
    }
    public function deletePurpose(int $id): bool { return \App\Models\Purpose::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activatePurpose(int $id): bool { return \App\Models\Purpose::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivatePurpose(int $id): bool { return \App\Models\Purpose::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }

    // PurposeHalls
    public function getPurposeHallsWithInactive(): Collection { return \App\Models\PurposeHall::withoutGlobalScope('active')->get(); }
    public function savePurposeHall(array $data, ?int $id = null): \App\Models\PurposeHall
    {
        $item = $id ? \App\Models\PurposeHall::withoutGlobalScope('active')->findOrFail($id) : new \App\Models\PurposeHall();
        $item->fill($data); $item->save(); return $item;
    }
    public function deletePurposeHall(int $id): bool { return \App\Models\PurposeHall::withoutGlobalScope('active')->findOrFail($id)->delete(); }
    public function activatePurposeHall(int $id): bool { return \App\Models\PurposeHall::withoutGlobalScope('active')->findOrFail($id)->activate(); }
    public function deactivatePurposeHall(int $id): bool { return \App\Models\PurposeHall::withoutGlobalScope('active')->findOrFail($id)->deactivate(); }
}
