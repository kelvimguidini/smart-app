<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\BrokerTransportRepositoryInterface;
use App\Models\BrokerTransport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultBrokerTransportService implements BrokerTransportServiceInterface
{
    protected $brokerTransportRepository;

    public function __construct(BrokerTransportRepositoryInterface $brokerTransportRepository)
    {
        $this->brokerTransportRepository = $brokerTransportRepository;
    }

    /**
     * @inheritDoc
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->brokerTransportRepository->list($filters, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): BrokerTransport
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        if (isset($data['contact'])) {
            $data['contact'] = mb_strtoupper($data['contact']);
        }
        return $this->brokerTransportRepository->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): BrokerTransport
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        if (isset($data['contact'])) {
            $data['contact'] = mb_strtoupper($data['contact']);
        }
        return $this->brokerTransportRepository->update($id, $data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        return $this->brokerTransportRepository->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function activate(int $id): bool
    {
        return $this->brokerTransportRepository->activate($id);
    }

    /**
     * @inheritDoc
     */
    public function deactivate(int $id): bool
    {
        return $this->brokerTransportRepository->deactivate($id);
    }
}
