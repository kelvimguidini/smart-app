<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\ServiceHallRepositoryInterface;
use App\Models\ServiceHall;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultServiceHallService implements ServiceHallServiceInterface
{
    protected $serviceHallRepository;

    public function __construct(ServiceHallRepositoryInterface $serviceHallRepository)
    {
        $this->serviceHallRepository = $serviceHallRepository;
    }

    public function getPaginatedServiceHalls(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->serviceHallRepository->list($filters, $perPage);
    }

    public function saveServiceHall(array $data): ServiceHall
    {
        if (isset($data['id']) && $data['id'] > 0) {
            return $this->serviceHallRepository->update($data['id'], $data);
        }

        return $this->serviceHallRepository->create($data);
    }

    public function deleteServiceHall(int $id): bool
    {
        return $this->serviceHallRepository->delete($id);
    }

    public function activateServiceHall(int $id): bool
    {
        return $this->serviceHallRepository->activate($id);
    }

    public function deactivateServiceHall(int $id): bool
    {
        return $this->serviceHallRepository->deactivate($id);
    }
}
