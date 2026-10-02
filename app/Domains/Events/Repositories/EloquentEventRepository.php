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
            'event_hotels.hotel.city', 'event_hotels.status_his', 'event_hotels.currency', 'event_hotels.providerBudget',
            'event_abs.ab.city', 'event_abs.status_his', 'event_abs.currency', 'event_abs.providerBudget',
            'event_halls.hall.city', 'event_halls.status_his', 'event_halls.currency', 'event_halls.providerBudget',
            'event_adds.add.city', 'event_adds.status_his', 'event_adds.currency', 'event_adds.providerBudget',
            'event_transports.transport.city', 'event_transports.status_his', 'event_transports.currency', 'event_transports.providerBudget',
            'event_airfares.airline', 'event_airfares.provider', 'event_airfares.status_his', 'event_airfares.currency'
        ]);

        if (Gate::allows('event_admin')) {
            $query->with(['hotelOperator', 'airOperator', 'landOperator']);
        } else {
            $query->with(['hotelOperator' => fn($q) => $q->where('id', $userId),
                         'airOperator' => fn($q) => $q->where('id', $userId),
                         'landOperator' => fn($q) => $q->where('id', $userId)]);
        }

        // Filtros simplificados para o repositório
        $proposalId = $request->input('id') ?: $request->input('proposalId') ?: $request->input('proposal_id');
        if (!empty($proposalId)) {
            $query->where('event.id', $proposalId);
        }
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

        $orderBy = $request->input('order_by', 'created_at');
        $orderDir = $request->input('order_dir', 'desc');

        $allowedOrderBy = ['id', 'name', 'code', 'date', 'date_final', 'created_at', 'customer_name'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'created_at';
        }
        if (!in_array(strtolower($orderDir), ['asc', 'desc'])) {
            $orderDir = 'desc';
        }

        if ($orderBy === 'customer_name') {
            $query->leftJoin('customer', 'event.customer_id', '=', 'customer.id')
                  ->select('event.*')
                  ->orderBy('customer.name', $orderDir);
        } else {
            $query->orderBy('event.' . $orderBy, $orderDir);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
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
    public function getProposalData(int $eventId, int $providerId, string $table, ?string $targetStatus = null): array
    {
        $withRelations = ['customer'];

        if (in_array($table, ['event_hotels', 'event_abs', 'event_halls'])) {
            $withRelations = array_merge($withRelations, [
                'event_hotels' => fn($q) => $q->where('hotel_id', $providerId),
                'event_hotels.hotel',
                'event_hotels.status_his',
                'event_hotels.eventHotelsOpt' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_hotels.eventHotelsOpt.regime',
                'event_hotels.eventHotelsOpt.apto_hotel',
                'event_hotels.eventHotelsOpt.category_hotel',
                'event_hotels.currency',
                
                'event_abs' => fn($q) => $q->where('ab_id', $providerId),
                'event_abs.ab',
                'event_abs.status_his',
                'event_abs.eventAbOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_abs.eventAbOpts.local',
                'event_abs.eventAbOpts.service_type',
                'event_abs.currency',
                
                'event_halls' => fn($q) => $q->where('hall_id', $providerId),
                'event_halls.hall',
                'event_halls.status_his',
                'event_halls.eventHallOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_halls.eventHallOpts.purpose',
                'event_halls.eventHallOpts.service',
                'event_halls.eventHallOpts.broker',
                'event_halls.currency',
            ]);
        }

        if ($table == 'event_adds') {
            $withRelations = array_merge($withRelations, [
                'event_adds' => fn($q) => $q->where('add_id', $providerId),
                'event_adds.add',
                'event_adds.status_his',
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
                'event_transports.status_his',
                'event_transports.eventTransportOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
                'event_transports.eventTransportOpts.brand',
                'event_transports.eventTransportOpts.vehicle',
                'event_transports.eventTransportOpts.model',
                'event_transports.currency',
            ]);
        }

        if ($table == 'event_airfares' || $table == 'event_airfare') {
            $withRelations = array_merge($withRelations, [
                'event_airfares' => fn($q) => $providerId > 0 ? $q->where(function($sub) use ($providerId) {
                    $sub->where('airline_id', $providerId)->orWhere('id', $providerId);
                }) : $q,
                'event_airfares.provider',
                'event_airfares.airline',
                'event_airfares.status_his',
                'event_airfares.eventAirfareOpts' => fn($q) => $q->orderBy('id', 'asc'),
                'event_airfares.eventAirfareOpts.outbound_airline',
                'event_airfares.currency',
            ]);
        }

        $eventDataBase = Event::with($withRelations)->find($eventId);

        if (!$targetStatus && $eventDataBase) {
            $mainItem = null;
            if ($table === 'event_hotels') {
                $mainItem = $eventDataBase->event_hotels->firstWhere('hotel_id', $providerId);
            } elseif ($table === 'event_abs') {
                $mainItem = $eventDataBase->event_abs->firstWhere('ab_id', $providerId);
            } elseif ($table === 'event_halls') {
                $mainItem = $eventDataBase->event_halls->firstWhere('hall_id', $providerId);
            } elseif ($table === 'event_adds') {
                $mainItem = $eventDataBase->event_adds->firstWhere('add_id', $providerId);
            } elseif ($table === 'event_transports') {
                $mainItem = $eventDataBase->event_transports->firstWhere('transport_id', $providerId);
            } elseif ($table === 'event_airfares' || $table === 'event_airfare') {
                $mainItem = $eventDataBase->event_airfares->firstWhere('id', $providerId) 
                    ?? $eventDataBase->event_airfares->firstWhere('airline_id', $providerId);
            }
            $targetStatus = $mainItem?->status_his?->first()?->status;
        }

        if ($targetStatus === 'dating_with_customer' && $eventDataBase) {
            if ($eventDataBase->relationLoaded('event_hotels')) {
                $eventDataBase->setRelation('event_hotels', $eventDataBase->event_hotels->filter(function ($item) {
                    return $item->status_his?->first()?->status === 'dating_with_customer';
                })->values());
            }
            if ($eventDataBase->relationLoaded('event_abs')) {
                $eventDataBase->setRelation('event_abs', $eventDataBase->event_abs->filter(function ($item) {
                    return $item->status_his?->first()?->status === 'dating_with_customer';
                })->values());
            }
            if ($eventDataBase->relationLoaded('event_halls')) {
                $eventDataBase->setRelation('event_halls', $eventDataBase->event_halls->filter(function ($item) {
                    return $item->status_his?->first()?->status === 'dating_with_customer';
                })->values());
            }
            if ($eventDataBase->relationLoaded('event_adds')) {
                $eventDataBase->setRelation('event_adds', $eventDataBase->event_adds->filter(function ($item) {
                    return $item->status_his?->first()?->status === 'dating_with_customer';
                })->values());
            }
            if ($eventDataBase->relationLoaded('event_transports')) {
                $eventDataBase->setRelation('event_transports', $eventDataBase->event_transports->filter(function ($item) {
                    return $item->status_his?->first()?->status === 'dating_with_customer';
                })->values());
            }
            if ($eventDataBase->relationLoaded('event_airfares')) {
                $eventDataBase->setRelation('event_airfares', $eventDataBase->event_airfares->filter(function ($item) {
                    return $item->status_his?->first()?->status === 'dating_with_customer';
                })->values());
            }
        }

        $providers = collect();
        if ($table == 'event_hotels' || $table == 'event_abs' || $table == 'event_halls') {
            $providers = $providers->concat($eventDataBase->event_hotels->pluck('hotel'));
            $providers = $providers->concat($eventDataBase->event_abs->pluck('ab'));
            $providers = $providers->concat($eventDataBase->event_halls->pluck('hall'));
        } elseif ($table == 'event_transports') {
            $providers = $providers->concat($eventDataBase->event_transports->pluck('transport'));
        } elseif ($table == 'event_adds') {
            $providers = $providers->concat($eventDataBase->event_adds->pluck('add'));
        } elseif ($table == 'event_airfares' || $table == 'event_airfare') {
            $providers = $providers->concat($eventDataBase->event_airfares->pluck('provider'));
            $providers = $providers->concat($eventDataBase->event_airfares->pluck('airline'));
        }

        $providerDataBase = $providers->filter()->unique()->values()->first();

        return [
            "providerDataBase" => $providerDataBase,
            "eventDataBase" => $eventDataBase,
            "table" => $table,
            "targetStatus" => $targetStatus
        ];
    }
}
