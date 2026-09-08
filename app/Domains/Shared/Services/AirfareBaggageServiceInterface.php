<?php

namespace App\Domains\Shared\Services;

use App\Models\AirfareBaggage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AirfareBaggageServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): AirfareBaggage;
    public function update(int $id, array $data): AirfareBaggage;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
