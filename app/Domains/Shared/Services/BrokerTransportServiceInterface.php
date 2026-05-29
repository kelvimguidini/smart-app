<?php

namespace App\Domains\Shared\Services;

use App\Models\BrokerTransport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BrokerTransportServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): BrokerTransport;
    public function update(int $id, array $data): BrokerTransport;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
