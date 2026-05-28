<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\MeasureRepositoryInterface;
use App\Models\Measure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultMeasureService implements MeasureServiceInterface
{
    protected $measureRepository;

    public function __construct(MeasureRepositoryInterface $measureRepository)
    {
        $this->measureRepository = $measureRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->measureRepository->list($filters, $perPage);
    }

    public function create(array $data): Measure
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->measureRepository->create($data);
    }

    public function update(int $id, array $data): Measure
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->measureRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->measureRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->measureRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->measureRepository->deactivate($id);
    }
}
