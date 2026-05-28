<?php

namespace App\Domains\Providers\Services;

use App\Domains\Providers\Repositories\ProviderServiceRepositoryInterface;
use App\Models\ProviderServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultProviderServiceService implements ProviderServiceServiceInterface
{
    protected $providerServiceRepository;

    public function __construct(ProviderServiceRepositoryInterface $providerServiceRepository)
    {
        $this->providerServiceRepository = $providerServiceRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->providerServiceRepository->list($filters, $perPage);
    }

    public function create(array $data): ProviderServices
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->providerServiceRepository->create($data);
    }

    public function update(int $id, array $data): ProviderServices
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->providerServiceRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->providerServiceRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->providerServiceRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->providerServiceRepository->deactivate($id);
    }
}
