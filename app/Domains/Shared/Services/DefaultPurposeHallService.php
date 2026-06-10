<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\PurposeHallRepositoryInterface;
use App\Models\PurposeHall;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultPurposeHallService implements PurposeHallServiceInterface
{
    protected $purposeHallRepository;

    public function __construct(PurposeHallRepositoryInterface $purposeHallRepository)
    {
        $this->purposeHallRepository = $purposeHallRepository;
    }

    public function getPaginatedPurposeHalls(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->purposeHallRepository->list($filters, $perPage);
    }

    public function savePurposeHall(array $data): PurposeHall
    {
        if (isset($data['id']) && $data['id'] > 0) {
            return $this->purposeHallRepository->update($data['id'], $data);
        }

        return $this->purposeHallRepository->create($data);
    }

    public function deletePurposeHall(int $id): bool
    {
        return $this->purposeHallRepository->delete($id);
    }

    public function activatePurposeHall(int $id): bool
    {
        return $this->purposeHallRepository->activate($id);
    }

    public function deactivatePurposeHall(int $id): bool
    {
        return $this->purposeHallRepository->deactivate($id);
    }
}
