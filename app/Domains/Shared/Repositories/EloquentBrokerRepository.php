<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Broker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentBrokerRepository extends EloquentBaseRepository implements BrokerRepositoryInterface
{
    public function __construct(Broker $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with('city')->withoutGlobalScope('active');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('contact', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        $sortColumn = $filters['sort_column'] ?? 'id';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        $allowedColumns = ['id', 'name', 'contact', 'email', 'phone', 'active'];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function find(int $id, bool $includeInactive = false): ?Broker
    {
        $query = Broker::query();
        if ($includeInactive) {
            $query->withoutGlobalScope('active');
        }
        return $query->find($id);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Broker
    {
        return Broker::create([
            'name' => $data['name'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'contact' => $data['contact'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'national' => $data['national'] ?? false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Broker
    {
        $broker = Broker::withoutGlobalScope('active')->findOrFail($id);
        $broker->fill($data);
        $broker->save();
        return $broker;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $broker = Broker::withoutGlobalScope('active')->findOrFail($id);
        return $broker->delete();
    }

    /**
     * @inheritDoc
     */
    public function activate(int $id): bool
    {
        $broker = Broker::withoutGlobalScope('active')->findOrFail($id);
        return $broker->activate();
    }

    /**
     * @inheritDoc
     */
    public function deactivate(int $id): bool
    {
        $broker = Broker::withoutGlobalScope('active')->findOrFail($id);
        return $broker->deactivate();
    }
}
