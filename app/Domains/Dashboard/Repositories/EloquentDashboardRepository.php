<?php

namespace App\Domains\Dashboard\Repositories;

use App\Models\Event;
use App\Models\StatusHistory;
use App\Models\ProviderBudget;
use App\Models\Role;
use App\Http\Middleware\Constants;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPendingValidateCount(?int $userId, array $roles): int
    {
        $query = Event::where(function ($query) {
            $query->whereHas('event_hotels.providerBudget', fn($q) => $q->where('evaluated', false))
                ->orWhereHas('event_halls.providerBudget', fn($q) => $q->where('evaluated', false))
                ->orWhereHas('event_transports.providerBudget', fn($q) => $q->where('evaluated', false))
                ->orWhereHas('event_adds.providerBudget', fn($q) => $q->where('evaluated', false))
                ->orWhereHas('event_abs.providerBudget', fn($q) => $q->where('evaluated', false));
        });

        if (!in_array('event_admin', $roles)) {
            $query->where(function ($q) use ($userId, $roles) {
                if (in_array('hotel_operator', $roles)) $q->orWhere('hotel_operator', $userId);
                if (in_array('land_operator', $roles)) $q->orWhere('land_operator', $userId);
                if (in_array('air_operator', $roles)) $q->orWhere('air_operator', $userId);
                
                if (!array_intersect(['hotel_operator', 'land_operator', 'air_operator'], $roles)) {
                    $q->where('id', 0);
                }
            });
        }

        return $query->count();
    }

    /**
     * @inheritDoc
     */
    public function getEventStatusCounts(): array
    {
        $latestStatus = StatusHistory::select('status', 'table', 'table_id')
            ->from('status_history as s1')
            ->where('created_at', '=', function ($query) {
                $query->select(DB::raw('MAX(created_at)'))
                    ->from('status_history as s2')
                    ->whereColumn('s1.table', '=', 's2.table')
                    ->whereColumn('s1.table_id', '=', 's2.table_id');
            })
            ->get();

        $counts = [];
        foreach ($latestStatus as $status) {
            $label = Constants::STATUS[$status->status]['label'] ?? $status->status;
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @inheritDoc
     */
    public function getWaitApprovalCounts(): array
    {
        $subQuery = function ($status) {
            return StatusHistory::where('created_at', '=', function ($query) {
                $query->select(DB::raw('MAX(created_at)'))
                    ->from('status_history as s2')
                    ->whereColumn('status_history.table', '=', 's2.table')
                    ->whereColumn('status_history.table_id', '=', 's2.table_id');
            })->where('status', $status);
        };

        return [
            'hotels' => $subQuery('dating_with_customer')->count(),
            'transports' => $subQuery('Cancelled')->count(), // Mantendo lógica original (Cancelled para transporte?)
        ];
    }

    /**
     * @inheritDoc
     */
    public function getBudgetApprovalRate(): float
    {
        return (float) ProviderBudget::selectRaw('ROUND((SUM(approved) / COUNT(*)) * 100, 1) AS rate')->value('rate') ?? 0.0;
    }

    /**
     * @inheritDoc
     */
    public function getMonthlyEvolutionData(?int $userId, array $roles): array
    {
        $query = Event::query();
        if (!in_array('event_admin', $roles)) {
            $query->where(function ($q) use ($userId, $roles) {
                if (in_array('hotel_operator', $roles)) $q->orWhere('hotel_operator', $userId);
                if (in_array('land_operator', $roles)) $q->orWhere('land_operator', $userId);
                if (in_array('air_operator', $roles)) $q->orWhere('air_operator', $userId);
            });
        }

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->addMonths(5)->endOfMonth();

        $eventCounts = (clone $query)->selectRaw('formatar_data_ptbr(date) AS month, COUNT(*) AS event_count')
            ->where(fn($q) => $q->whereBetween('date', [$startDate, $endDate])->orWhereBetween('date_final', [$startDate, $endDate]))
            ->groupBy('month')->get()->keyBy('month');

        $registerCounts = (clone $query)->selectRaw('formatar_data_ptbr(created_at) AS month, COUNT(*) AS register_count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')->get()->keyBy('month');

        $results = [];
        $monthsPt = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $tempDate = clone $startDate;

        for ($i = 0; $i < 12; $i++) {
            $monthName = $monthsPt[$tempDate->month - 1] . ' ' . $tempDate->year;
            $results[] = [
                'month' => $monthName,
                'event_count' => $eventCounts->get($monthName)->event_count ?? 0,
                'register_count' => $registerCounts->get($monthName)->register_count ?? 0
            ];
            $tempDate->addMonth();
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getUserGroupDistribution(): array
    {
        return Role::selectRaw('roles.name AS Name, COUNT(user_role.user_id) AS CountUsers, (COUNT(user_role.user_id) / total_users.total) * 100 AS Percentage')
            ->leftJoin('user_role', 'roles.id', '=', 'user_role.role_id')
            ->crossJoin(DB::raw('(SELECT COUNT(id) AS total FROM users) AS total_users'))
            ->groupBy('roles.id', 'roles.name', 'total_users.total')
            ->get()->toArray();
    }
}
