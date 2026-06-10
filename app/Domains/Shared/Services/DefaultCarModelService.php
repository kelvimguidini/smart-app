<?php
 
namespace App\Domains\Shared\Services;
 
use App\Domains\Shared\Repositories\CarModelRepositoryInterface;
use App\Models\CarModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 
class DefaultCarModelService implements CarModelServiceInterface
{
    protected $carModelRepository;
 
    public function __construct(CarModelRepositoryInterface $carModelRepository)
    {
        $this->carModelRepository = $carModelRepository;
    }
 
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->carModelRepository->list($filters, $perPage);
    }
 
    public function create(array $data): CarModel
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->carModelRepository->create($data);
    }
 
    public function update(int $id, array $data): CarModel
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->carModelRepository->update($id, $data);
    }
 
    public function delete(int $id): bool
    {
        return $this->carModelRepository->delete($id);
    }
 
    public function activate(int $id): bool
    {
        return $this->carModelRepository->activate($id);
    }
 
    public function deactivate(int $id): bool
    {
        return $this->carModelRepository->deactivate($id);
    }
}
