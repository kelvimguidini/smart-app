<?php
 
namespace App\Domains\Shared\Services;
 
use App\Domains\Shared\Repositories\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 
class DefaultBrandService implements BrandServiceInterface
{
    protected $brandRepository;
 
    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }
 
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->brandRepository->list($filters, $perPage);
    }
 
    public function create(array $data): Brand
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->brandRepository->create($data);
    }
 
    public function update(int $id, array $data): Brand
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->brandRepository->update($id, $data);
    }
 
    public function delete(int $id): bool
    {
        return $this->brandRepository->delete($id);
    }
 
    public function activate(int $id): bool
    {
        return $this->brandRepository->activate($id);
    }
 
    public function deactivate(int $id): bool
    {
        return $this->brandRepository->deactivate($id);
    }
}
