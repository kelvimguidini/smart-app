<?php

namespace App\Domains\Hotels\Repositories;

use App\Models\EventHotel;
use Illuminate\Database\Eloquent\Collection;

interface EventHotelRepositoryInterface
{
    /**
     * Busca um EventHotel com todos os seus detalhes e opções.
     *
     * @param int $id
     * @return EventHotel|null
     */
    public function findWithDetails(int $id): ?EventHotel;

    public function create(array $data): EventHotel;
    public function update(int $id, array $data): EventHotel;
    public function updateByEventAndProvider(int $eventId, int $providerId, array $data): bool;

    /**
     * Lista todos os EventHotels vinculados a um evento.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getByEvent(int $eventId): Collection;
}
