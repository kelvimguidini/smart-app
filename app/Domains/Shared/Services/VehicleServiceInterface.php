<?php
 
namespace App\Domains\Shared\Services;
 
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 
interface VehicleServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Vehicle;
    public function update(int $id, array $data): Vehicle;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
