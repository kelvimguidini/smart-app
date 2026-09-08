<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\AirfareAirlineRepositoryInterface;
use App\Models\AirfareAirline;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultAirfareAirlineService implements AirfareAirlineServiceInterface
{
    protected $airlineRepository;

    public function __construct(AirfareAirlineRepositoryInterface $airlineRepository)
    {
        $this->airlineRepository = $airlineRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->airlineRepository->list($filters, $perPage);
    }

    public function create(array $data): AirfareAirline
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->airlineRepository->create($data);
    }

    public function update(int $id, array $data): AirfareAirline
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->airlineRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->airlineRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->airlineRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->airlineRepository->deactivate($id);
    }
}
