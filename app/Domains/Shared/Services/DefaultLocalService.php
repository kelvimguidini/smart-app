<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\LocalRepositoryInterface;
use App\Models\Local;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultLocalService implements LocalServiceInterface
{
    protected $localRepository;

    public function __construct(LocalRepositoryInterface $localRepository)
    {
        $this->localRepository = $localRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->localRepository->list($filters, $perPage);
    }

    public function create(array $data): Local
    {
        return $this->localRepository->create($data);
    }

    public function update(int $id, array $data): Local
    {
        return $this->localRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->localRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->localRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->localRepository->deactivate($id);
    }
}
