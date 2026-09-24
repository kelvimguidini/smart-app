<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\AirfareAirportRepositoryInterface;
use App\Models\AirfareAirport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultAirfareAirportService implements AirfareAirportServiceInterface
{
    protected $airportRepository;

    public function __construct(AirfareAirportRepositoryInterface $airportRepository)
    {
        $this->airportRepository = $airportRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->airportRepository->list($filters, $perPage);
    }

    public function create(array $data): AirfareAirport
    {
        if (isset($data['iata_code'])) {
            $data['iata_code'] = mb_strtoupper(trim($data['iata_code']));
        }
        return $this->airportRepository->create($data);
    }

    public function update(int $id, array $data): AirfareAirport
    {
        if (isset($data['iata_code'])) {
            $data['iata_code'] = mb_strtoupper(trim($data['iata_code']));
        }
        return $this->airportRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->airportRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->airportRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->airportRepository->deactivate($id);
    }
}
