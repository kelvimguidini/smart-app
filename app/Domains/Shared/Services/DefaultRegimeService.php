<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\RegimeRepositoryInterface;
use App\Models\Regime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultRegimeService implements RegimeServiceInterface
{
    protected $regimeRepository;

    public function __construct(RegimeRepositoryInterface $regimeRepository)
    {
        $this->regimeRepository = $regimeRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->regimeRepository->list($filters, $perPage);
    }

    public function create(array $data): Regime
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->regimeRepository->create($data);
    }

    public function update(int $id, array $data): Regime
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->regimeRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->regimeRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->regimeRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->regimeRepository->deactivate($id);
    }
}
