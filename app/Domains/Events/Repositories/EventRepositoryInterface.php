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

    /**
     * Cria um novo evento.
     *
     * @param array $data
     * @return Event
     */
    public function create(array $data): Event;

    /**
     * Atualiza um evento existente.
     *
     * @param int $id
     * @param array $data
     * @param array $relatedTables
     * @return Event
     */
    public function update(int $id, array $data, array $relatedTables): Event;

    /**
     * Exclui um evento e seus vinculos associados.
     *
     * @param int $id
     * @param array $relatedTables
     * @return bool
     */
    public function delete(int $id, array $relatedTables): bool;

    /**
     * Obtém os dados da proposta de um evento.
     *
     * @param int $eventId
     * @param int $providerId
     * @param string $table
     * @return array
     */
    public function getProposalData(int $eventId, int $providerId, string $table): array;
}
