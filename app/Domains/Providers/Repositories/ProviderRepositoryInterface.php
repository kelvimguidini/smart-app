<?php

namespace App\Domains\Providers\Repositories;

use App\Models\Provider;
use App\Models\ProviderServices;
use App\Models\ProviderTransport;
use Illuminate\Database\Eloquent\Collection;

interface ProviderRepositoryInterface
{
    public function find(int $id): ?Provider;
    public function allWithCity(): Collection;
    public function allWithCityAdmin(): Collection;
    public function allServicesWithCity(): Collection;
    public function allTransportWithCity(): Collection;
    
    /**
     * Cria um fornecedor e seu serviço correspondente.
     *
     * @param array $data
     * @return Provider
     */
    public function createWithService(array $data): Provider;

    /**
     * Atualiza um fornecedor.
     *
     * @param int $id
     * @param array $data
     * @return Provider
     */
    public function update(int $id, array $data): Provider;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
