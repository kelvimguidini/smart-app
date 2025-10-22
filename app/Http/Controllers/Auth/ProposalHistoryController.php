<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ProposalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProposalHistoryController extends Controller
{
    public function getHistory(Request $request)
    {
        $eventId = $request->event_id;

        // 1️⃣ Coleta todas as tabelas que começam com "event"
        $tables = collect(DB::select("SHOW TABLES LIKE 'event%'"))
            ->map(fn($t) => array_values((array)$t)[0]);

        // 2️⃣ Inicializa a estrutura de relacionamentos
        $relations = collect([
            'event' => collect([$eventId]),
        ]);

        // 3️⃣ Mapeia as tabelas com relação direta ou indireta
        foreach ($tables as $table) {
            // pula a principal
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
        $history = ProposalHistory::with('user')
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

        return response()->json($history);
    }

    public function restore(Request $request)
    {
        $table = $request->input('table');
        $recordId = $request->input('record_id');
        $historyId = $request->input('history_id');

        if (empty($table) || empty($recordId)) {
            return response()->json(['error' => 'Parâmetros inválidos.'], 422);
        }

        // prevenção básica: evitar nomes de tabela perigosos
        if (!preg_match('/^[a-z0-9_]+$/i', $table)) {
            return response()->json(['error' => 'Tabela inválida.'], 422);
        }

        // Verifica se a tabela existe e tem coluna deleted_at
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'deleted_at')) {
            return response()->json(['error' => 'Não é possível restaurar: tabela não suporta restauração lógica.'], 422);
        }

        try {
            DB::table($table)->where('id', $recordId)->update([
                'deleted_at' => null
            ]);

            if (!empty($historyId)) {
                ProposalHistory::where('id', $historyId)->update([
                    'restored' => true,
                    'restored_at' => now(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Restore from proposal history failed', ['table' => $table, 'id' => $recordId, 'err' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao restaurar registro.'], 500);
        }
    }
}
