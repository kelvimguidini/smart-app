<?php

namespace App\Domains\Shared\Repositories;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentRoleRepository extends EloquentBaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        // Precisamos ignorar o Global Scope 'active' para listar até os inativos
        // e já trazer as 'permissions' carregadas ansiosamente para uso do Frontend.
        $query = $this->model->withoutGlobalScope('active')->with('permissions');

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
}
