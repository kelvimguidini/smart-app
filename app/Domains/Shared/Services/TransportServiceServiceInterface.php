<?php

namespace App\Domains\Shared\Services;

use App\Models\TransportService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransportServiceServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): TransportService;
    public function update(int $id, array $data): TransportService;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
