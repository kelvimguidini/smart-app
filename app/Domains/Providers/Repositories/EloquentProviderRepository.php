<?php

namespace App\Domains\Providers\Repositories;

use App\Models\Provider;
use App\Models\ProviderServices;
use App\Models\ProviderTransport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentProviderRepository implements ProviderRepositoryInterface
{
    public function find(int $id): ?Provider
    {
        return Provider::withoutGlobalScope('active')->find($id);
    }

    public function allWithCity(): Collection
    {
        return Provider::with("city")->get();
    }

    public function allWithCityAdmin(): Collection
    {
        return Provider::with('city')->withoutGlobalScope('active')->get();
    }

    public function allServicesWithCity(): Collection
    {
        return ProviderServices::with("city")->get();
    }

    public function allTransportWithCity(): Collection
    {
        return ProviderTransport::with("city")->get();
    }

    /**
     * @inheritDoc
     */
    public function createWithService(array $data): Provider
    {
        return DB::transaction(function () use ($data) {
            $provider = Provider::create($data);

            ProviderServices::create([
                'name' => $data['name'],
                'city_id' => $data['city_id'] ?? null,
                'contact' => $data['contact'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'national' => $data['national'] ?? null,
                'iss_percent' => $data['iss_percent'] ?? 0,
                'service_percent' => $data['service_percent'] ?? 0,
                'iva_percent' => $data['iva_percent'] ?? 0,
                'payment_method' => $data['payment_method'] ?? null
            ]);

            return $provider;
        });
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Provider
    {
        $provider = Provider::withoutGlobalScope('active')->findOrFail($id);
        $provider->update($data);
        return $provider;
    }

    public function activate(int $id): bool
    {
        $provider = Provider::withoutGlobalScope('active')->findOrFail($id);
        return $provider->activate();
    }

    public function deactivate(int $id): bool
    {
        $provider = Provider::withoutGlobalScope('active')->findOrFail($id);
        return $provider->deactivate();
    }
}
