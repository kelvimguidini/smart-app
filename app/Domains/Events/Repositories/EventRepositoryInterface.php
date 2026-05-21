<?php

namespace App\Domains\Events\Repositories;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Event;

interface EventRepositoryInterface
{
    /**
     * Lista eventos com filtros e paginação.
     *
     * @param Request $request
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function list(Request $request, int $perPage = 10): LengthAwarePaginator;

    /**
     * Busca um evento por ID.
     *
     * @param int $id
     * @return Event|null
     */
    public function find(int $id): ?Event;

    /**
     * Busca um evento com os locais vinculados.
     *
     * @param int $id
     * @return Event|null
     */
    public function findWithLocals(int $id): ?Event;
}
