<?php

namespace App\Domains\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentBaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * @inheritDoc
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->withoutGlobalScope('active');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

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
    public function find(int $id, bool $includeInactive = false): ?Model
    {
        $query = $this->model->newQuery();
        if ($includeInactive) {
            $query->withoutGlobalScope('active');
        }
        return $query->find($id);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Model
    {
        $record = $this->model->withoutGlobalScope('active')->findOrFail($id);
        $record->fill($data);
        $record->save();
        return $record;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $record = $this->model->withoutGlobalScope('active')->findOrFail($id);
        return $record->delete();
    }

    /**
     * @inheritDoc
     */
    public function activate(int $id): bool
    {
        $record = $this->model->withoutGlobalScope('active')->findOrFail($id);
        return $record->activate();
    }

    /**
     * @inheritDoc
     */
    public function deactivate(int $id): bool
    {
        $record = $this->model->withoutGlobalScope('active')->findOrFail($id);
        return $record->deactivate();
    }
}
