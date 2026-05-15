<?php

namespace App\Domains\Events\Repositories;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EloquentEventRepository implements EventRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function list(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $page = $request->page ? $request->page : 1;
        $userId = Auth::user()->id;

        $query = Event::with([
            'crd', "eventLocals", 'customer',
            'event_hotels.hotel', 'event_hotels.status_his', 'event_hotels.currency',
            'event_abs.ab', 'event_abs.status_his', 'event_abs.currency',
            'event_halls.hall', 'event_halls.status_his', 'event_halls.currency',
            'event_adds.add', 'event_adds.status_his', 'event_adds.currency',
            'event_transports.transport.city', 'event_transports.status_his', 'event_transports.currency'
        ]);

        if (Gate::allows('event_admin')) {
            $query->with(['hotelOperator', 'airOperator', 'landOperator']);
        } else {
            $query->with(['hotelOperator' => fn($q) => $q->where('id', $userId),
                         'airOperator' => fn($q) => $q->where('id', $userId),
                         'landOperator' => fn($q) => $q->where('id', $userId)]);
        }

        // Filtros simplificados para o repositório
        if ($request->startDate && $request->endDate) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->startDate)->whereDate('date', '<=', $request->endDate)
                  ->orWhereDate('date_final', '>=', $request->startDate)->whereDate('date_final', '<=', $request->endDate);
            });
        }
        if ($request->city) {
            $query->whereHas('eventLocals', fn($q) => $q->where('cidade', $request->city));
        }
        if ($request->eventCode) $query->where('code', $request->eventCode);
        if ($request->client) $query->where('customer_id', $request->client);

        return $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @inheritDoc
     */
    public function find(int $id): ?Event
    {
        return Event::find($id);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Event
    {
        return Event::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data, array $relatedTables): Event
    {
        $event = Event::findOrFail($id);
        $event->update($data);
        return $event;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id, array $relatedTables): bool
    {
        return DB::transaction(function () use ($id, $relatedTables) {
            $event = Event::findOrFail($id);
            foreach ($relatedTables as $table => $ids) {
                DB::table($table)->whereIn('id', $ids)->delete();
            }
            return $event->delete();
        });
    }

    /**
     * @inheritDoc
     */
    public function findWithLocals(int $id): ?Event
    {
        return Event::with("eventLocals")->find($id);
    }

    /**
     * @inheritDoc
     */
    public function getProposalData(int $eventId, int $providerId, string $table): array
    {
        $withRelations = ['customer'];

        if (in_array($table, ['event_hotels', 'event_abs', 'event_halls'])) {
            $withRelations = array_merge($withRelations, [
                'event_hotels' => fn($q) => $q->where('hotel_id', $providerId),
                'event_hotels.hotel',
                'event_hotels.eventHotelsOpt' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_hotels.eventHotelsOpt.regime',
                'event_hotels.eventHotelsOpt.apto_hotel',
                'event_hotels.eventHotelsOpt.category_hotel',
                'event_hotels.currency',
                
                'event_abs' => fn($q) => $q->where('ab_id', $providerId),
                'event_abs.ab',
                'event_abs.eventAbOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_abs.eventAbOpts.local',
                'event_abs.eventAbOpts.service_type',
                'event_abs.currency',
                
                'event_halls' => fn($q) => $q->where('hall_id', $providerId),
                'event_halls.hall',
                'event_halls.eventHallOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_halls.eventHallOpts.purpose',
                'event_halls.currency',
            ]);
        }

        if ($table == 'event_adds') {
            $withRelations = array_merge($withRelations, [
                'event_adds' => fn($q) => $q->where('add_id', $providerId),
                'event_adds.add',
                'event_adds.eventAddOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_adds.eventAddOpts.measure',
                'event_adds.eventAddOpts.service',
                'event_adds.currency',
            ]);
        }

        if ($table == 'event_transports') {
            $withRelations = array_merge($withRelations, [
                'event_transports' => fn($q) => $q->where('transport_id', $providerId),
                'event_transports.transport',
                'event_transports.eventTransportOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_transports.eventTransportOpts.brand',
                'event_transports.eventTransportOpts.vehicle',
                'event_transports.eventTransportOpts.model',
                'event_transports.currency',
            ]);
        }

        $eventDataBase = Event::with($withRelations)->find($eventId);

        $providers = collect();
        if ($table == 'event_hotels' || $table == 'event_abs' || $table == 'event_halls') {
            $providers = $providers->concat($eventDataBase->event_hotels->pluck('hotel'));
            $providers = $providers->concat($eventDataBase->event_abs->pluck('ab'));
            $providers = $providers->concat($eventDataBase->event_halls->pluck('hall'));
        } elseif ($table == 'event_transports') {
            $providers = $providers->concat($eventDataBase->event_transports->pluck('transport'));
        } elseif ($table == 'event_adds') {
            $providers = $providers->concat($eventDataBase->event_adds->pluck('add'));
        }

        $providerDataBase = $providers->filter()->unique()->values()->first();

        return [
            "providerDataBase" => $providerDataBase,
            "eventDataBase" => $eventDataBase,
            "table" => $table
        ];
    }
}
