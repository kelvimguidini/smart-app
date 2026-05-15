<?php

namespace App\Domains\Hotels\Repositories;

use App\Models\Apto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AptoRepositoryInterface
{
    /**
     * Lista os apartamentos com filtros, ordenação e paginação.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Busca um apartamento pelo ID.
     *
     * @param int $id
     * @param bool $includeInactive
     * @return Apto|null
     */
    public function find(int $id, bool $includeInactive = false): ?Apto;

    /**
     * Cria um novo apartamento.
     *
     * @param array $data
     * @return Apto
     */
    public function create(array $data): Apto;

    /**
     * Atualiza um apartamento existente.
     *
     * @param int $id
     * @param array $data
     * @return Apto
     */
    public function update(int $id, array $data): Apto;

    /**
     * Remove um apartamento.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Ativa um apartamento.
     *
     * @param int $id
     * @return bool
     */
    public function activate(int $id): bool;

    /**
     * Desativa um apartamento.
     *
     * @param int $id
     * @return bool
     */
    public function deactivate(int $id): bool;
}
