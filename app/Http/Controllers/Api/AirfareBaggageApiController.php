<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\AirfareBaggageServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AirfareBaggageApiController extends Controller
{
    protected $baggageService;

    public function __construct(AirfareBaggageServiceInterface $baggageService)
    {
        $this->baggageService = $baggageService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('airfare_baggage_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $baggages = $this->baggageService->list($filters, $perPage);

        return response()->json($baggages);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('airfare_baggage_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $baggage = $this->baggageService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $baggage]);
            } else {
                $baggage = $this->baggageService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $baggage]);
            }
        } catch (\App\Exceptions\UniqueNameException $e) {
            return response()->json(['message' => 'Já existe um registro de bagagem cadastrado com este nome!'], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('airfare_baggage_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->baggageService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('airfare_baggage_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->baggageService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('airfare_baggage_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->baggageService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
