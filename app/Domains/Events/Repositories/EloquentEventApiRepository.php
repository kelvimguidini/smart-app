<?php

namespace App\Domains\Events\Repositories;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentEventApiRepository implements EventApiRepositoryInterface
{
    /**
     * Retrieve events for XML export, filtered by start and end dates.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function getEventsForExport(Carbon $startDate, Carbon $endDate): Collection
    {
        $eventos = Event::with([
            'customer',
            'crd',
            'hotelOperator',
            'landOperator',
            // Hotéis
            'event_hotels.hotel',
            'event_hotels.eventHotelsOpt' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_hotels.eventHotelsOpt.regime',
            'event_hotels.eventHotelsOpt.apto_hotel',
            'event_hotels.eventHotelsOpt.category_hotel',
            'event_hotels.currency',
            'event_hotels.status_his.user',

            // ABs
            'event_abs.ab',
            'event_abs.eventAbOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_abs.eventAbOpts.Local',
            'event_abs.eventAbOpts.service_type',
            'event_abs.currency',
            'event_abs.status_his.user',

            // Halls
            'event_halls.hall',
            'event_halls.eventHallOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_halls.eventHallOpts.purpose',
            'event_halls.currency',
            'event_halls.status_his.user',

            // Adicionais
            'event_adds.add',
            'event_adds.eventAddOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_adds.eventAddOpts.measure',
            'event_adds.eventAddOpts.service',
            'event_adds.currency',
            'event_adds.status_his.user',

            // Transportes
            'event_transports.transport',
            'event_transports.eventTransportOpts' => fn($q) => $q->orderBy('order', 'asc')->orderby('in'),
            'event_transports.eventTransportOpts.brand',
            'event_transports.eventTransportOpts.vehicle',
            'event_transports.eventTransportOpts.model',
            'event_transports.currency',
            'event_transports.status_his.user',
        ])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereHas('event_hotels', function ($q) use ($startDate, $endDate) {
                    $q->whereExists(function ($sub) use ($startDate, $endDate) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_hotel.id')
                            ->where('status_history.table', 'event_hotels')
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$startDate, $endDate]);
                    });
                })->orWhereHas('event_abs', function ($q) use ($startDate, $endDate) {
                    $q->whereExists(function ($sub) use ($startDate, $endDate) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_ab.id')
                            ->where('status_history.table', 'event_abs')
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$startDate, $endDate]);
                    });
                })->orWhereHas('event_halls', function ($q) use ($startDate, $endDate) {
                    $q->whereExists(function ($sub) use ($startDate, $endDate) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_hall.id')
                            ->where('status_history.table', 'event_halls')
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$startDate, $endDate]);
                    });
                })->orWhereHas('event_adds', function ($q) use ($startDate, $endDate) {
                    $q->whereExists(function ($sub) use ($startDate, $endDate) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_add.id')
                            ->whereIn('status_history.table', ['event_adds', 'EventAdds'])
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$startDate, $endDate]);
                    });
                })->orWhereHas('event_transports', function ($q) use ($startDate, $endDate) {
                    $q->whereExists(function ($sub) use ($startDate, $endDate) {
                        $sub->select(DB::raw(1))
                            ->from('status_history')
                            ->whereColumn('status_history.table_id', 'event_transport.id')
                            ->where('status_history.table', 'event_transports')
                            ->where('status_history.status', 'dating_with_customer')
                            ->whereBetween('status_history.created_at', [$startDate, $endDate]);
                    });
                });
            })
            ->get();

        // Filtrar os itens de cada evento conforme a data
        foreach ($eventos as $evento) {
            // Filtra event_hotels
            if (isset($evento->event_hotels)) {
                $evento->event_hotels = $evento->event_hotels->filter(function ($item) use ($startDate, $endDate) {
                    return $item->status_his->contains(function ($status) use ($startDate, $endDate) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $startDate
                            && $status->created_at <= $endDate;
                    });
                })->values();
            }
            // Filtra event_abs
            if (isset($evento->event_abs) && !isset($evento->event_hotels)) {
                $evento->event_abs = collect($evento->event_abs)->filter(function ($item) use ($startDate, $endDate) {
                    return isset($item->status_his) && collect($item->status_his)->contains(function ($status) use ($startDate, $endDate) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $startDate
                            && $status->created_at <= $endDate;
                    });
                })->values();
            }
            // Filtra event_halls
            if (isset($evento->event_halls) && !isset($evento->event_hotels)) {
                $evento->event_halls = $evento->event_halls->filter(function ($item) use ($startDate, $endDate) {
                    return $item->status_his->contains(function ($status) use ($startDate, $endDate) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $startDate
                            && $status->created_at <= $endDate;
                    });
                })->values();
            }
            // Filtra event_adds
            if (isset($evento->event_adds)) {
                $evento->event_adds = $evento->event_adds->filter(function ($item) use ($startDate, $endDate) {
                    return $item->status_his->contains(function ($status) use ($startDate, $endDate) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $startDate
                            && $status->created_at <= $endDate;
                    });
                })->values();
            }
            // Filtra event_transports
            if (isset($evento->event_transports)) {
                $evento->event_transports = $evento->event_transports->filter(function ($item) use ($startDate, $endDate) {
                    return $item->status_his->contains(function ($status) use ($startDate, $endDate) {
                        return $status->status === 'dating_with_customer'
                            && $status->created_at >= $startDate
                            && $status->created_at <= $endDate;
                    });
                })->values();
            }
        }

        return $eventos;
    }
}
