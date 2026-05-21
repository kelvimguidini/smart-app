<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\ServiceTypeRepositoryInterface;
use App\Models\ServiceType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultServiceTypeService implements ServiceTypeServiceInterface
{
    protected $serviceTypeRepository;

    public function __construct(ServiceTypeRepositoryInterface $serviceTypeRepository)
    {
        $this->serviceTypeRepository = $serviceTypeRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->serviceTypeRepository->list($filters, $perPage);
    }

    public function create(array $data): ServiceType
    {
        return $this->serviceTypeRepository->create($data);
    }

    public function update(int $id, array $data): ServiceType
    {
        return $this->serviceTypeRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->serviceTypeRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->serviceTypeRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->serviceTypeRepository->deactivate($id);
    }
}
