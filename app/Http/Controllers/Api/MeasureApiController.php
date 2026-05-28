<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\MeasureServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MeasureApiController extends Controller
{
    protected $measureService;

    public function __construct(MeasureServiceInterface $measureService)
    {
        $this->measureService = $measureService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('measure_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $measures = $this->measureService->list($filters, $perPage);

        return response()->json($measures);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('measure_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $measure = $this->measureService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $measure]);
            } else {
                $measure = $this->measureService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $measure]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('measure_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->measureService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('measure_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->measureService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('measure_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->measureService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
