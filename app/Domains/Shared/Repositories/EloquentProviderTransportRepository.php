<?php

namespace App\Domains\Shared\Repositories;

use App\Models\ProviderTransport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProviderTransportRepository extends EloquentBaseRepository implements ProviderTransportRepositoryInterface
{
    public function __construct(ProviderTransport $model)
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
        
        $allowedColumns = ['id', 'name', 'contact', 'email', 'active'];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($perPage);
    }
}
