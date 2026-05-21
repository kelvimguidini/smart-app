<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\PurposeRepositoryInterface;
use App\Models\Purpose;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultPurposeService implements PurposeServiceInterface
{
    protected $purposeRepository;

    public function __construct(PurposeRepositoryInterface $purposeRepository)
    {
        $this->purposeRepository = $purposeRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->purposeRepository->list($filters, $perPage);
    }

    public function create(array $data): Purpose
    {
        return $this->purposeRepository->create($data);
    }

    public function update(int $id, array $data): Purpose
    {
        return $this->purposeRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->purposeRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->purposeRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->purposeRepository->deactivate($id);
    }
}
