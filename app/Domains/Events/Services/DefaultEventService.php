<?php

namespace App\Domains\Events\Services;

use App\Domains\Events\Repositories\EventRepositoryInterface;
use App\Domains\Hotels\Repositories\EventHotelRepositoryInterface;
use App\Domains\FoodBeverage\Repositories\EventABRepositoryInterface;
use App\Domains\Halls\Repositories\EventHallRepositoryInterface;
use App\Domains\Additions\Repositories\EventAddRepositoryInterface;
use App\Domains\Transports\Repositories\EventTransportRepositoryInterface;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class DefaultEventService implements EventServiceInterface
{
    protected $eventRepository;
    protected $eventHotelRepository;
    protected $eventABRepository;
    protected $eventHallRepository;
    protected $eventAddRepository;
    protected $eventTransportRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        EventHotelRepositoryInterface $eventHotelRepository,
        EventABRepositoryInterface $eventABRepository,
        EventHallRepositoryInterface $eventHallRepository,
        EventAddRepositoryInterface $eventAddRepository,
        EventTransportRepositoryInterface $eventTransportRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventHotelRepository = $eventHotelRepository;
        $this->eventABRepository = $eventABRepository;
        $this->eventHallRepository = $eventHallRepository;
        $this->eventAddRepository = $eventAddRepository;
        $this->eventTransportRepository = $eventTransportRepository;
    }

    /**
     * @inheritDoc
     */
    public function duplicateEvent(int $id): Event
    {
        // Lógica de duplicação profunda seria implementada aqui
        throw new \Exception("Método em implementação");
    }

    /**
     * @inheritDoc
     */
    public function updateStatus(int $eventId, string $status, ?string $observation = null): bool
    {
        return $this->eventRepository->update($eventId, ['status' => $status], []);
    }

    /**
     * @inheritDoc
     */
    public function store(array $data, ?int $id = null): Event
    {
        if ($id) {
            $relatedTables = $this->getRelatedTablesIds($id);
            return $this->eventRepository->update($id, $data, $relatedTables);
        }

        return $this->eventRepository->create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $relatedTables = $this->getRelatedTablesIds($id);
        return $this->eventRepository->delete($id, $relatedTables);
    }

    /**
     * Coleta IDs de tabelas relacionadas para operações de manutenção.
     */
    protected function getRelatedTablesIds(int $eventId): array
    {
        return [
            'event_hotels' => $this->eventHotelRepository->getByEvent($eventId)->pluck('id')->toArray(),
            'event_abs' => $this->eventABRepository->getIdsByEvent($eventId),
            'event_halls' => $this->eventHallRepository->getIdsByEvent($eventId),
            'event_adds' => $this->eventAddRepository->getIdsByEvent($eventId),
            'event_transports' => $this->eventTransportRepository->getIdsByEvent($eventId),
        ];
    }
}
