<?php
 
namespace App\Domains\Shared\Services;
 
use App\Models\Brand;
 use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 
interface BrandServiceInterface
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Brand;
    public function update(int $id, array $data): Brand;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
}
