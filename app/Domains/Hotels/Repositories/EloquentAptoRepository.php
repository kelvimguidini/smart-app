<?php

namespace App\Domains\Hotels\Repositories;

use App\Models\Apto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAptoRepository implements AptoRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Apto::withoutGlobalScope('active');

        // Search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // Sorting
        $sortColumn = $filters['sort_column'] ?? 'id';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        $allowedColumns = ['id', 'name', 'active'];
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
    public function find(int $id, bool $includeInactive = false): ?Apto
    {
        $query = Apto::query();
        if ($includeInactive) {
            $query->withoutGlobalScope('active');
        }
        return $query->find($id);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Apto
    {
        return Apto::create([
            'name' => $data['name']
        ]);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Apto
    {
        $apto = Apto::withoutGlobalScope('active')->findOrFail($id);
        $apto->name = $data['name'];
        $apto->save();
        return $apto;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $apto = Apto::withoutGlobalScope('active')->findOrFail($id);
        return $apto->delete();
    }

    /**
     * @inheritDoc
     */
    public function activate(int $id): bool
    {
        $apto = Apto::withoutGlobalScope('active')->findOrFail($id);
        return $apto->activate();
    }

    /**
     * @inheritDoc
     */
    public function deactivate(int $id): bool
    {
        $apto = Apto::withoutGlobalScope('active')->findOrFail($id);
        return $apto->deactivate();
    }
}
