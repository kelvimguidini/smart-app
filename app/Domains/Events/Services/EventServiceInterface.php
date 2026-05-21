<?php

namespace App\Domains\Events\Services;

use App\Models\Event;

interface EventServiceInterface
{
    /**
     * Duplica um evento com todos os seus fornecedores e opções.
     *
     * @param int $id
     * @return Event
     */
    public function duplicateEvent(int $id): Event;

    /**
     * Atualiza o status de um evento e registra no histórico.
     *
     * @param int $eventId
     * @param string $status
     * @param string|null $observation
     * @return bool
     */
    public function updateStatus(int $eventId, string $status, ?string $observation = null): bool;

    /**
     * Salva ou atualiza um evento.
     *
     * @param array $data
     * @param int|null $id
     * @return Event
     */
    public function store(array $data, ?int $id = null): Event;

    /**
     * Remove um evento e seus vínculos.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
