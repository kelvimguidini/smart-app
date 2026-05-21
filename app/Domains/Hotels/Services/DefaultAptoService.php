<?php

namespace App\Domains\Hotels\Services;

use App\Domains\Hotels\Repositories\AptoRepositoryInterface;
use App\Models\Apto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultAptoService implements AptoServiceInterface
{
    protected $aptoRepository;

    public function __construct(AptoRepositoryInterface $aptoRepository)
    {
        $this->aptoRepository = $aptoRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->aptoRepository->list($filters, $perPage);
    }

    public function create(array $data): Apto
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->aptoRepository->create($data);
    }

    public function update(int $id, array $data): Apto
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->aptoRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->aptoRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->aptoRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->aptoRepository->deactivate($id);
    }
}
