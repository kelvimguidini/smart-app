<?php

namespace App\Domains\Shared\Repositories;

use App\Models\ProposalHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EloquentProposalHistoryRepository implements ProposalHistoryRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getHistoryByEvent(int $eventId): Collection
    {
        // 1️⃣ Coleta todas as tabelas que começam com "event"
        $tables = collect(DB::select("SHOW TABLES LIKE 'event%'"))
            ->map(fn($t) => array_values((array)$t)[0]);

        // 2️⃣ Inicializa a estrutura de relacionamentos
        $relations = collect([
            'event' => collect([$eventId]),
        ]);

        // 3️⃣ Mapeia as tabelas com relação direta ou indireta
        foreach ($tables as $table) {
            if ($table === 'event') continue;

            // caso 1: tabelas diretas (ex: event_hotel, event_add, event_local)
            if (Schema::hasColumn($table, 'event_id')) {
                $ids = DB::table($table)->whereIn('event_id', $relations['event'])->pluck('id');
                $relations->put($table, $ids);
            }
            // caso 2: tabelas *_opt (ex: event_hotel_opt, event_add_opt, etc.)
            else {
                $parentTable = Str::beforeLast($table, '_opt');
                if ($relations->has($parentTable) && Schema::hasColumn($parentTable, 'event_id')) {
                    $ids = DB::table($table)
                        ->whereIn($parentTable . '_id', $relations[$parentTable])
                        ->pluck('id');
                    $relations->put($table, $ids);
                }
            }
        }

        // 4️⃣ Monta a query final de históricos
        return ProposalHistory::with('user')
            ->where(function ($query) use ($relations) {
                foreach ($relations as $table => $ids) {
                    if ($ids->isNotEmpty()) {
                        $query->orWhere(function ($sub) use ($table, $ids) {
                            $sub->where('table_name', $table)
                                ->whereIn('record_id', $ids);
                        });
                    }
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function restore(string $table, int $recordId, ?int $historyId = null): bool
    {
        return DB::transaction(function () use ($table, $recordId, $historyId) {
            // Verifica se a tabela existe e tem coluna deleted_at
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'deleted_at')) {
                return false;
            }

            DB::table($table)->where('id', $recordId)->update([
                'deleted_at' => null
            ]);

            if ($historyId) {
                ProposalHistory::where('id', $historyId)->update([
                    'restored' => true,
                    'restored_at' => now(),
                ]);
            }

            return true;
        });
    }
}
