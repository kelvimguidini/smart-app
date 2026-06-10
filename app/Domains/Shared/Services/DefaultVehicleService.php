<?php
 
namespace App\Domains\Shared\Services;
 
use App\Domains\Shared\Repositories\VehicleRepositoryInterface;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 
class DefaultVehicleService implements VehicleServiceInterface
{
    protected $vehicleRepository;
 
    public function __construct(VehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }
 
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->vehicleRepository->list($filters, $perPage);
    }
 
    public function create(array $data): Vehicle
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->vehicleRepository->create($data);
    }
 
    public function update(int $id, array $data): Vehicle
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->vehicleRepository->update($id, $data);
    }
 
    public function delete(int $id): bool
    {
        return $this->vehicleRepository->delete($id);
    }
 
    public function activate(int $id): bool
    {
        return $this->vehicleRepository->activate($id);
    }
 
    public function deactivate(int $id): bool
    {
        return $this->vehicleRepository->deactivate($id);
    }
}
