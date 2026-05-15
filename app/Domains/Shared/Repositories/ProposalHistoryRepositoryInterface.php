<?php

namespace App\Domains\Shared\Repositories;

use App\Models\ProposalHistory;
use Illuminate\Database\Eloquent\Collection;

interface ProposalHistoryRepositoryInterface
{
    /**
     * Retorna o histórico de propostas consolidado para um evento.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getHistoryByEvent(int $eventId): Collection;

    /**
     * Restaura um registro do histórico.
     *
     * @param string $table
     * @param int $recordId
     * @param int|null $historyId
     * @return bool
     */
    public function restore(string $table, int $recordId, ?int $historyId = null): bool;
}
