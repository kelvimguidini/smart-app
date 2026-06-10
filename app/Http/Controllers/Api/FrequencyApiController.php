<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\FrequencyServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FrequencyApiController extends Controller
{
    protected $frequencyService;

    public function __construct(FrequencyServiceInterface $frequencyService)
    {
        $this->frequencyService = $frequencyService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('frequency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $frequencies = $this->frequencyService->list($filters, $perPage);

        return response()->json($frequencies);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('frequency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $frequency = $this->frequencyService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $frequency]);
            } else {
                $frequency = $this->frequencyService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $frequency]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('frequency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->frequencyService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('frequency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->frequencyService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('frequency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->frequencyService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
