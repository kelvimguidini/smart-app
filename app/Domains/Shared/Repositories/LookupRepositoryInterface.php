<?php

namespace App\Domains\Shared\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface LookupRepositoryInterface
{
    public function getAllBrokers(): Collection;
    public function getAllCurrencies(): Collection;
    public function getAllRegimes(): Collection;
    public function getAllPurposes(): Collection;
    public function getAllServices(): Collection;
    public function getAllServiceTypes(): Collection;
    public function getAllLocals(): Collection;
    public function getAllServiceHalls(): Collection;
    public function getAllPurposeHalls(): Collection;
    public function getAllServiceAdds(): Collection;
    public function getAllFrequencies(): Collection;
    public function getAllMeasures(): Collection;
    public function getAllBrokerTransports(): Collection;
    public function getAllVehicles(): Collection;
    public function getAllCarModels(): Collection;
    public function getAllTransportServices(): Collection;
    public function getAllBrands(): Collection;
    public function getAllCategories(): Collection;
    public function getAllAptos(): Collection;

    // Brokers
    public function getBrokersWithInactive(): Collection;
    public function saveBroker(array $data, ?int $id = null): \App\Models\Broker;
    public function deleteBroker(int $id): bool;
    public function activateBroker(int $id): bool;
    public function deactivateBroker(int $id): bool;

    // Cities
    public function getCitiesWithInactive(): Collection;
    public function searchCities(string $term): Collection;
    public function saveCity(array $data, ?int $id = null): \App\Models\City;
    public function deleteCity(int $id): bool;
    public function activateCity(int $id): bool;
    public function deactivateCity(int $id): bool;

    // Currencies
    public function getCurrenciesWithInactive(): Collection;
    public function saveCurrency(array $data, ?int $id = null): \App\Models\Currency;
    public function deleteCurrency(int $id): bool;
    public function activateCurrency(int $id): bool;
    public function deactivateCurrency(int $id): bool;

    // Brands
    public function getBrandsWithInactive(): Collection;
    public function saveBrand(array $data, ?int $id = null): \App\Models\Brand;
    public function deleteBrand(int $id): bool;
    public function activateBrand(int $id): bool;
    public function deactivateBrand(int $id): bool;

    // CarModels
    public function getCarModelsWithInactive(): Collection;
    public function saveCarModel(array $data, ?int $id = null): \App\Models\CarModel;
    public function deleteCarModel(int $id): bool;
    public function activateCarModel(int $id): bool;
    public function deactivateCarModel(int $id): bool;

    // Vehicles
    public function getVehiclesWithInactive(): Collection;
    public function saveVehicle(array $data, ?int $id = null): \App\Models\Vehicle;
    public function deleteVehicle(int $id): bool;
    public function activateVehicle(int $id): bool;
    public function deactivateVehicle(int $id): bool;

    // TransportServices
    public function getTransportServicesWithInactive(): Collection;
    public function saveTransportService(array $data, ?int $id = null): \App\Models\TransportService;
    public function deleteTransportService(int $id): bool;
    public function activateTransportService(int $id): bool;
    public function deactivateTransportService(int $id): bool;

    // BrokerTransports
    public function getBrokerTransportsWithInactive(): Collection;
    public function saveBrokerTransport(array $data, ?int $id = null): \App\Models\BrokerTransport;
    public function deleteBrokerTransport(int $id): bool;
    public function activateBrokerTransport(int $id): bool;
    public function deactivateBrokerTransport(int $id): bool;

    // Services
    public function getServicesWithInactive(): Collection;
    public function saveService(array $data, ?int $id = null): \App\Models\Service;
    public function deleteService(int $id): bool;
    public function activateService(int $id): bool;
    public function deactivateService(int $id): bool;

    // ServiceTypes
    public function getServiceTypesWithInactive(): Collection;
    public function saveServiceType(array $data, ?int $id = null): \App\Models\ServiceType;
    public function deleteServiceType(int $id): bool;
    public function activateServiceType(int $id): bool;
    public function deactivateServiceType(int $id): bool;

    // ServiceHalls
    public function getServiceHallsWithInactive(): Collection;
    public function saveServiceHall(array $data, ?int $id = null): \App\Models\ServiceHall;
    public function deleteServiceHall(int $id): bool;
    public function activateServiceHall(int $id): bool;
    public function deactivateServiceHall(int $id): bool;

    // ServiceAdds
    public function getServiceAddsWithInactive(): Collection;
    public function saveServiceAdd(array $data, ?int $id = null): \App\Models\ServiceAdd;
    public function deleteServiceAdd(int $id): bool;
    public function activateServiceAdd(int $id): bool;
    public function deactivateServiceAdd(int $id): bool;

    // Measures
    public function getMeasuresWithInactive(): Collection;
    public function saveMeasure(array $data, ?int $id = null): \App\Models\Measure;
    public function deleteMeasure(int $id): bool;
    public function activateMeasure(int $id): bool;
    public function deactivateMeasure(int $id): bool;

    // Frequencies
    public function getFrequenciesWithInactive(): Collection;
    public function saveFrequency(array $data, ?int $id = null): \App\Models\Frequency;
    public function deleteFrequency(int $id): bool;
    public function activateFrequency(int $id): bool;
    public function deactivateFrequency(int $id): bool;

    // Locals
    public function getLocalsWithInactive(): Collection;
    public function saveLocal(array $data, ?int $id = null): \App\Models\Local;
    public function deleteLocal(int $id): bool;
    public function activateLocal(int $id): bool;
    public function deactivateLocal(int $id): bool;

    // Purposes
    public function getPurposesWithInactive(): Collection;
    public function savePurpose(array $data, ?int $id = null): \App\Models\Purpose;
    public function deletePurpose(int $id): bool;
    public function activatePurpose(int $id): bool;
    public function deactivatePurpose(int $id): bool;

    // PurposeHalls
    public function getPurposeHallsWithInactive(): Collection;
    public function savePurposeHall(array $data, ?int $id = null): \App\Models\PurposeHall;
    public function deletePurposeHall(int $id): bool;
    public function activatePurposeHall(int $id): bool;
    public function deactivatePurposeHall(int $id): bool;

    // ProviderServices (Base)
    public function getProviderServicesWithInactive(): Collection;
    public function saveProviderService(array $data, ?int $id = null): \App\Models\ProviderServices;
    public function deleteProviderService(int $id): bool;
    public function activateProviderService(int $id): bool;
    public function deactivateProviderService(int $id): bool;

    // ProviderTransport (Base)
    public function getProviderTransportsWithInactive(): Collection;
    public function saveProviderTransport(array $data, ?int $id = null): \App\Models\ProviderTransport;
    public function deleteProviderTransport(int $id): bool;
    public function activateProviderTransport(int $id): bool;
    public function deactivateProviderTransport(int $id): bool;
}
