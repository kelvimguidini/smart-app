<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\AirfareAirlineServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AirfareAirlineApiController extends Controller
{
    protected $airlineService;

    public function __construct(AirfareAirlineServiceInterface $airlineService)
    {
        $this->airlineService = $airlineService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('airfare_airline_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $airlines = $this->airlineService->list($filters, $perPage);

        return response()->json($airlines);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('airfare_airline_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $airline = $this->airlineService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $airline]);
            } else {
                $airline = $this->airlineService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $airline]);
            }
        } catch (\App\Exceptions\UniqueNameException $e) {
            return response()->json(['message' => 'Já existe uma companhia aérea cadastrada com este nome!'], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('airfare_airline_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->airlineService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('airfare_airline_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->airlineService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('airfare_airline_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->airlineService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
