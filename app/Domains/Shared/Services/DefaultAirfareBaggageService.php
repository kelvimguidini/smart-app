<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\AirfareBaggageRepositoryInterface;
use App\Models\AirfareBaggage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultAirfareBaggageService implements AirfareBaggageServiceInterface
{
    protected $baggageRepository;

    public function __construct(AirfareBaggageRepositoryInterface $baggageRepository)
    {
        $this->baggageRepository = $baggageRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->baggageRepository->list($filters, $perPage);
    }

    public function create(array $data): AirfareBaggage
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->baggageRepository->create($data);
    }

    public function update(int $id, array $data): AirfareBaggage
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->baggageRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->baggageRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->baggageRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->baggageRepository->deactivate($id);
    }
}
