<?php

namespace App\Domains\Shared\Services;

use App\Models\Frequency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FrequencyServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Frequency;
    public function update(int $id, array $data): Frequency;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
