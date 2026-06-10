<?php
 
namespace App\Domains\Shared\Services;
 
use App\Models\CarModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 
interface CarModelServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): CarModel;
    public function update(int $id, array $data): CarModel;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
