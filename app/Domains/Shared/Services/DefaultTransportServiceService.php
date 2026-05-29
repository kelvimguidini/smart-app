<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\TransportServiceRepositoryInterface;
use App\Models\TransportService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultTransportServiceService implements TransportServiceServiceInterface
{
    protected $transportServiceRepository;

    public function __construct(TransportServiceRepositoryInterface $transportServiceRepository)
    {
        $this->transportServiceRepository = $transportServiceRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->transportServiceRepository->list($filters, $perPage);
    }

    public function create(array $data): TransportService
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->transportServiceRepository->create($data);
    }

    public function update(int $id, array $data): TransportService
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->transportServiceRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->transportServiceRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->transportServiceRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->transportServiceRepository->deactivate($id);
    }
}
