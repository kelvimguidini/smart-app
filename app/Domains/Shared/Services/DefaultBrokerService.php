<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\BrokerRepositoryInterface;
use App\Models\Broker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultBrokerService implements BrokerServiceInterface
{
    protected $brokerRepository;

    public function __construct(BrokerRepositoryInterface $brokerRepository)
    {
        $this->brokerRepository = $brokerRepository;
    }

    /**
     * @inheritDoc
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->brokerRepository->list($filters, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Broker
    {
        return $this->brokerRepository->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Broker
    {
        return $this->brokerRepository->update($id, $data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        return $this->brokerRepository->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function activate(int $id): bool
    {
        return $this->brokerRepository->activate($id);
    }

    /**
     * @inheritDoc
     */
    public function deactivate(int $id): bool
    {
        return $this->brokerRepository->deactivate($id);
    }
}
