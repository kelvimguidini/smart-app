<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\ServiceRepositoryInterface;
use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultServiceService implements ServiceServiceInterface
{
    protected $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->serviceRepository->list($filters, $perPage);
    }

    public function create(array $data): Service
    {
        return $this->serviceRepository->create($data);
    }

    public function update(int $id, array $data): Service
    {
        return $this->serviceRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->serviceRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->serviceRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->serviceRepository->deactivate($id);
    }
}
