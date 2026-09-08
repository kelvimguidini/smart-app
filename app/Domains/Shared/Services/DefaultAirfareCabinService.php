<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\AirfareCabinRepositoryInterface;
use App\Models\AirfareCabin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultAirfareCabinService implements AirfareCabinServiceInterface
{
    protected $cabinRepository;

    public function __construct(AirfareCabinRepositoryInterface $cabinRepository)
    {
        $this->cabinRepository = $cabinRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->cabinRepository->list($filters, $perPage);
    }

    public function create(array $data): AirfareCabin
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->cabinRepository->create($data);
    }

    public function update(int $id, array $data): AirfareCabin
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->cabinRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->cabinRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->cabinRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->cabinRepository->deactivate($id);
    }
}
