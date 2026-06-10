<?php

namespace App\Domains\Providers\Services;

use App\Models\ProviderServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProviderServiceServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): ProviderServices;
    public function update(int $id, array $data): ProviderServices;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
