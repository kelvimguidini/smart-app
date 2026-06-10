<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\FrequencyRepositoryInterface;
use App\Models\Frequency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultFrequencyService implements FrequencyServiceInterface
{
    protected $frequencyRepository;

    public function __construct(FrequencyRepositoryInterface $frequencyRepository)
    {
        $this->frequencyRepository = $frequencyRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->frequencyRepository->list($filters, $perPage);
    }

    public function create(array $data): Frequency
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->frequencyRepository->create($data);
    }

    public function update(int $id, array $data): Frequency
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->frequencyRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->frequencyRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->frequencyRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->frequencyRepository->deactivate($id);
    }
}
