<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Domains\Shared\Repositories\ProposalHistoryRepositoryInterface;

class ProposalHistoryController extends Controller
{
    protected $proposalHistoryRepository;

    public function __construct(ProposalHistoryRepositoryInterface $proposalHistoryRepository)
    {
        $this->proposalHistoryRepository = $proposalHistoryRepository;
    }

    public function getHistory(Request $request)
    {
        if (!$request->event_id) {
            return response()->json(['error' => 'ID do evento é obrigatório.'], 400);
        }

        try {
            $history = $this->proposalHistoryRepository->getHistoryByEvent($request->event_id);
            return response()->json($history);
        } catch (\Exception $e) {
            Log::error('Fetch proposal history failed', ['event_id' => $request->event_id, 'err' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao buscar histórico.'], 500);
        }
    }

    public function restore(Request $request)
    {
        $table = $request->input('table');
        $recordId = $request->input('record_id');
        $historyId = $request->input('history_id');

        if (empty($table) || empty($recordId)) {
            return response()->json(['error' => 'Parâmetros inválidos.'], 422);
        }

        // Prevenção básica de segurança (sanitização do nome da tabela)
        if (!preg_match('/^[a-z0-9_]+$/i', $table)) {
            return response()->json(['error' => 'Tabela inválida.'], 422);
        }

        try {
            $success = $this->proposalHistoryRepository->restore($table, $recordId, $historyId);
            
            if (!$success) {
                return response()->json(['error' => 'Não é possível restaurar: tabela não suporta restauração lógica.'], 422);
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Restore from proposal history failed', ['table' => $table, 'id' => $recordId, 'err' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao restaurar registro.'], 500);
        }
    }
}
