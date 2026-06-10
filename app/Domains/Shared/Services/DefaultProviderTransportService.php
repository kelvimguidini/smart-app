<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\ProviderTransportRepositoryInterface;

class DefaultProviderTransportService implements ProviderTransportServiceInterface
{
    public function __construct(
        protected ProviderTransportRepositoryInterface $repository
    ) {}

    public function list(array $filters = []): array
    {
        $perPage = $filters['per_page'] ?? 10;
        $paginator = $this->repository->list($filters, $perPage);

        return [
            'data'         => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];
    }

    public function store(array $data): object
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        if (isset($data['contact'])) {
            $data['contact'] = mb_strtoupper($data['contact']);
        }
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): object
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        if (isset($data['contact'])) {
            $data['contact'] = mb_strtoupper($data['contact']);
        }
        return $this->repository->update($id, $data);
    }

    public function activate(int $id): void
    {
        $this->repository->activate($id);
    }

    public function deactivate(int $id): void
    {
        $this->repository->deactivate($id);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
