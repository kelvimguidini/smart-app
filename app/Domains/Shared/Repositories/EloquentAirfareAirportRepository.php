<?php

namespace App\Domains\Shared\Repositories;

use App\Models\AirfareAirport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAirfareAirportRepository extends EloquentBaseRepository implements AirfareAirportRepositoryInterface
{
    public function __construct(AirfareAirport $model)
    {
        parent::__construct($model);
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->withoutGlobalScope('active');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('iata_code', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%')
                  ->orWhere('state', 'like', '%' . $search . '%')
                  ->orWhere('country', 'like', '%' . $search . '%');
            });
        }

        $sortColumn = $filters['sort_column'] ?? 'iata_code';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        $allowedColumns = ['id', 'iata_code', 'name', 'city', 'state', 'country', 'active'];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('iata_code', 'asc');
        }

        return $query->paginate($perPage);
    }
}
