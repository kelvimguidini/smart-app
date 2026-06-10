<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\ServiceAddRepositoryInterface;
use App\Models\ServiceAdd;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultServiceAddService implements ServiceAddServiceInterface
{
    protected $serviceAddRepository;

    public function __construct(ServiceAddRepositoryInterface $serviceAddRepository)
    {
        $this->serviceAddRepository = $serviceAddRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->serviceAddRepository->list($filters, $perPage);
    }

    public function create(array $data): ServiceAdd
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->serviceAddRepository->create($data);
    }

    public function update(int $id, array $data): ServiceAdd
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->serviceAddRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->serviceAddRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->serviceAddRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->serviceAddRepository->deactivate($id);
    }
}
